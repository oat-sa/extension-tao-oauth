<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoOauth\model\storage;

use League\OAuth2\Client\Token\AccessToken;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\taoOauth\model\Oauth2Service;

class ConsumerStorage extends ConfigurableService
{
    use OntologyAwareTrait;

    const SERVICE_ID = 'taoOauth/consumerStorage';

    const DEFAULT_PERSISTENCE = 'default';

    const DEFAULT_CACHE = 'cache';

    const OPTION_PERSISTENCE = 'persistence';

    const OPTION_CACHE = 'cache';


    const CONSUMER_CLASS = 'http://www.taotesting.com/ontologies/taooauth.rdf#Oauth-consumer';

    const CONSUMER_CLIENT_ID = 'http://www.taotesting.com/ontologies/taooauth.rdf#ClientId';

    const CONSUMER_CLIENT_SECRET = 'http://www.taotesting.com/ontologies/taooauth.rdf#ClientSecret';

    const CONSUMER_TOKEN = 'http://www.taotesting.com/ontologies/taooauth.rdf#Token';

    const CONSUMER_TOKEN_HASH = 'http://www.taotesting.com/ontologies/taooauth.rdf#TokenHash';


    const CONSUMER_TOKEN_URL = 'http://www.taotesting.com/ontologies/taooauth.rdf#TokenUrl';

    const CONSUMER_TOKEN_TYPE = 'http://www.taotesting.com/ontologies/taooauth.rdf#TokenType';

    const CONSUMER_TOKEN_GRANT_TYPE = 'http://www.taotesting.com/ontologies/taooauth.rdf#GrantType';

    /**
     * Register a token to a consumer resource
     *
     * @param \core_kernel_classes_Resource $consumer
     * @param AccessToken $token
     * @throws \common_Exception
     */
    public function setConsumerToken(\core_kernel_classes_Resource $consumer, AccessToken $token)
    {
        $consumer->removePropertyValues($this->getProperty(Oauth2Service::PROPERTY_OAUTH_TOKEN));
        $consumer->removePropertyValues($this->getProperty(Oauth2Service::PROPERTY_OAUTH_TOKEN_HASH));

        $consumer->setPropertiesValues(array(
            Oauth2Service::PROPERTY_OAUTH_TOKEN => json_encode($token),
            Oauth2Service::PROPERTY_OAUTH_TOKEN_HASH => $token->getToken()
        ));

        $this->getCache()->set($token->getToken(), json_encode($token));
    }

    /**
     * Retrieve a token from persistence.
     *
     * Fetch token from cache if exists, otherwise set it
     *
     * @param $tokenHash
     * @return AccessToken
     * @throws \common_Exception
     * @throws \common_exception_NotFound
     * @throws \core_kernel_persistence_Exception
     */
    public function getToken($tokenHash)
    {
        if ($this->getCache()->exists($tokenHash)) {
            $token = new AccessToken(json_decode($this->getCache()->get($tokenHash), true));
        } else {
            $encodedToken = $this->getConsumerByTokenHash($tokenHash)->getOnePropertyValue($this->getProperty(Oauth2Service::PROPERTY_OAUTH_TOKEN));
            $token = new AccessToken(json_decode($encodedToken, true));
            $this->getCache()->set($token->getToken(), $encodedToken);
        }
        return $token;
    }

    /**
     * Get an oauth consumer by client id and secret
     *
     * @param $clientId
     * @param $clientSecret
     * @return mixed
     * @throws \common_exception_NotFound
     */
    public function getConsumer($clientId, $clientSecret)
    {
        $consumers = $this->getRootClass()->searchInstances(
            array(
                Oauth2Service::PROPERTY_OAUTH_KEY => $clientId,
                Oauth2Service::PROPERTY_OAUTH_SECRET => $clientSecret,
            ),
            array('like' => false, 'recursive' => true)
        );

        if (count($consumers) == 1) {
            return reset($consumers);
        } else {
            throw new \common_exception_NotFound('Consumer does not exist..');
        }
    }

    /**
     * Get an oauth consumer by token hash
     *
     * @param $hash
     * @return \core_kernel_classes_Resource
     * @throws \common_exception_NotFound
     */
    public function getConsumerByTokenHash($hash)
    {
        $consumers = $this->getRootClass()->searchInstances(
            array(Oauth2Service::PROPERTY_OAUTH_TOKEN_HASH => $hash),
            array('like' => false, 'recursive' => true)
        );

        if (count($consumers) == 1) {
            return reset($consumers);
        } else {
            throw new \common_exception_NotFound('Consumer does not exist.');
        }
    }

    /**
     * Get consumer root class
     *
     * @return \core_kernel_classes_Class
     */
    protected function getRootClass()
    {
        return $this->getClass(Oauth2Service::CLASS_URI_OAUTH_CONSUMER);
    }

    /**
     * Get the persistence to persist token in the database
     *
     * @return \common_persistence_Persistence
     */
    protected function getPersistence()
    {
        if ($this->hasOption(self::OPTION_PERSISTENCE)) {
            $persistenceName = $this->getOption(self::OPTION_PERSISTENCE);
        } else {
            $persistenceName = self::DEFAULT_PERSISTENCE;
        }

        return $this->getPersistenceManager()->getPersistenceById($persistenceName);
    }

    /**
     * Get the persistence to persist token in cache
     *
     * @return \common_persistence_KeyValuePersistence
     */
    protected function getCache()
    {
        if ($this->hasOption(self::OPTION_CACHE)) {
            $persistenceName = $this->getOption(self::OPTION_CACHE);
        } else {
            $persistenceName = self::DEFAULT_CACHE;
        }

        $persistence = $this->getPersistenceManager()->getPersistenceById($persistenceName);
        if (!$persistence instanceof \common_persistence_KeyValuePersistence) {
            throw new \LogicException('Cache persistence has to be a Key Value persistence');
        }
        return $persistence;
    }

    /**
     * Get the persistence manager
     *
     * @return \common_persistence_Manager
     */
    protected function getPersistenceManager()
    {
        return $this->getServiceLocator()->get(\common_persistence_Manager::SERVICE_ID);
    }
}