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
 * Copyright (c) 2018-2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\taoOauth\model\storage;

use common_Exception;
use common_exception_NotFound;
use core_kernel_classes_Resource;
use core_kernel_persistence_Exception;
use League\OAuth2\Client\Token\AccessToken;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\oauth\DataStore;
use oat\taoOauth\model\OAuthClient;

class ConsumerStorage extends ConfigurableService
{
    use OntologyAwareTrait;

    public const SERVICE_ID = 'taoOauth/consumerStorage';

    public const DEFAULT_PERSISTENCE = 'default';
    public const DEFAULT_CACHE = 'cache';
    public const OPTION_PERSISTENCE = 'persistence';
    public const OPTION_CACHE = 'cache';

    public const CONSUMER_CLASS = DataStore::CLASS_URI_OAUTH_CONSUMER;
    public const CONSUMER_CLIENT_KEY = DataStore::PROPERTY_OAUTH_KEY;
    public const CONSUMER_CLIENT_SECRET = DataStore::PROPERTY_OAUTH_SECRET;
    public const CONSUMER_CALLBACK_URL = DataStore::PROPERTY_OAUTH_CALLBACK;

    public const CONSUMER_TOKEN = 'http://www.taotesting.com/ontologies/taooauth.rdf#Token';
    public const CONSUMER_TOKEN_HASH = 'http://www.taotesting.com/ontologies/taooauth.rdf#TokenHash';
    public const CONSUMER_TOKEN_URL = 'http://www.taotesting.com/ontologies/taooauth.rdf#TokenUrl';
    public const CONSUMER_TOKEN_TYPE = 'http://www.taotesting.com/ontologies/taooauth.rdf#TokenType';
    public const CONSUMER_TOKEN_GRANT_TYPE = 'http://www.taotesting.com/ontologies/taooauth.rdf#GrantType';

    /**
     * Register a token to a consumer resource
     *
     * @param core_kernel_classes_Resource $consumer
     * @param AccessToken $token
     * @throws common_Exception
     */
    public function setConsumerToken(core_kernel_classes_Resource $consumer, AccessToken $token)
    {
        $consumer->removePropertyValues($this->getProperty(self::CONSUMER_TOKEN));
        $consumer->removePropertyValues($this->getProperty(self::CONSUMER_TOKEN_HASH));

        $consumer->setPropertiesValues(array(
            self::CONSUMER_TOKEN => json_encode($token),
            self::CONSUMER_TOKEN_HASH => $token->getToken()
        ));

        $this->getCache()->set($token->getToken(), json_encode($token));
    }

    /**
     * Retrieve a token from persistence.
     *
     * Fetch token from cache if exists, otherwise set it
     *
     * @param string $tokenHash
     * @return AccessToken
     * @throws common_Exception
     * @throws common_exception_NotFound
     * @throws core_kernel_persistence_Exception
     */
    public function getToken($tokenHash)
    {
        $cache = $this->getCache();

        if ($cache->exists($tokenHash)) {
            $decodedToken = json_decode($this->getCache()->get($tokenHash), true);

            if (is_array($decodedToken)) {
                return new AccessToken($decodedToken);
            }

            $this->logWarning(
                sprintf(
                    'The token %s contains an invalid JSON: %s',
                    substr($tokenHash, 0, 10) . '...',
                    json_last_error_msg()
                )
            );
        }

        $encodedToken = $this->getConsumerByTokenHash($tokenHash)
            ->getOnePropertyValue($this->getProperty(self::CONSUMER_TOKEN));

        $decodedToken = json_decode($encodedToken, true);

        if (!is_array($decodedToken)) {
            $errorMessage = sprintf(
                'The token %s contains an invalid JSON: %s',
                substr($tokenHash, 0, 10) . '...',
                json_last_error_msg()
            );

            $this->logError($errorMessage);

            throw new common_exception_NotFound($errorMessage);
        }

        $token = new AccessToken($decodedToken);
        $cache->set($token->getToken(), json_encode($token));

        return $token;
    }

    /**
     * Get an oauth consumer by client id and secret
     *
     * @param $clientKey
     * @param $clientSecret
     * @return mixed
     * @throws common_exception_NotFound
     */
    public function getConsumer($clientKey, $clientSecret)
    {
        $consumers = $this->getRootClass()->searchInstances(
            array(
                self::CONSUMER_CLIENT_KEY => $clientKey,
                self::CONSUMER_CLIENT_SECRET => $clientSecret,
            ),
            array('like' => false, 'recursive' => true)
        );

        if (count($consumers) == 1) {
            return reset($consumers);
        } else {
            throw new common_exception_NotFound('invalid_client');
        }
    }

    /**
     * Get an oauth consumer by token hash
     *
     * @param $hash
     * @return core_kernel_classes_Resource
     * @throws common_exception_NotFound
     */
    public function getConsumerByTokenHash($hash)
    {
        $consumers = $this->getRootClass()->searchInstances(
            array(self::CONSUMER_TOKEN_HASH => $hash),
            array('like' => false, 'recursive' => true)
        );

        if (count($consumers) == 1) {
            return reset($consumers);
        } else {
            throw new common_exception_NotFound('Consumer does not exist.');
        }
    }

    /**
     * Create a consumer from key, secret and token url
     *
     * @param $key
     * @param $secret
     * @param $tokenUrl
     * @return core_kernel_classes_Resource
     */
    public function createConsumer($key, $secret, $tokenUrl)
    {
        return $this->getClass(self::CONSUMER_CLASS)->createInstanceWithProperties(array(
            self::CONSUMER_CLIENT_KEY => $key,
            self::CONSUMER_CLIENT_SECRET => $secret,
            self::CONSUMER_CALLBACK_URL => false,
            self::CONSUMER_TOKEN => '',
            self::CONSUMER_TOKEN_HASH => '',
            self::CONSUMER_TOKEN_URL => $tokenUrl,
            self::CONSUMER_TOKEN_TYPE => OAuthClient::DEFAULT_TOKEN_TYPE,
            self::CONSUMER_TOKEN_GRANT_TYPE => OAuthClient::DEFAULT_GRANT_TYPE,
        ));
    }

    /**
     * Delete a consumer based on key/secret
     *
     * @param $key
     * @param $secret
     */
    public function deleteConsumer($key, $secret)
    {
        $consumers = $this->getClass(ConsumerStorage::CONSUMER_CLASS)->searchInstances(
            array(
                self::CONSUMER_CLIENT_KEY => $key,
                self::CONSUMER_CLIENT_SECRET => $secret,
            ),
            array('like' => false, 'recursive' => true)
        );

        /** @var core_kernel_classes_Resource $consumer */
        foreach ($consumers as $consumer) {
            $consumer->delete();
        }
    }

    /**
     * Get consumer root class
     *
     * @return \core_kernel_classes_Class
     */
    protected function getRootClass()
    {
        return $this->getClass(self::CONSUMER_CLASS);
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
