<?php

namespace oat\taoOauth\model\token\storage;

use League\OAuth2\Client\Token\AccessToken;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;

class TokenStorage extends ConfigurableService
{
    use OntologyAwareTrait;

    const SERVICE_ID = 'taoOauth/tokenStorage';

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
     * @param \core_kernel_classes_Resource $consumer
     * @param AccessToken $token
     * @throws \common_Exception
     */
    public function setConsumerToken(\core_kernel_classes_Resource $consumer, AccessToken $token)
    {
        $consumer->setPropertiesValues(array(
            self::CONSUMER_TOKEN => json_encode($token),
            self::CONSUMER_TOKEN_HASH => $token->getToken()
        ));

        $this->getCache()->set($token->getToken(), json_encode($token));
    }

    /**
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
            $encodedToken = $this->getConsumerByTokenHash($tokenHash)->getOnePropertyValue($this->getProperty(self::CONSUMER_TOKEN));
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
                TokenStorage::CONSUMER_CLIENT_ID => $clientId,
                TokenStorage::CONSUMER_CLIENT_SECRET => $clientSecret,
            ),
            array('like' => false, 'recursive' => true)
        );

        if (count($consumers) == 1) {
            return reset($consumers);
        } else {
            throw new \common_exception_NotFound('Consumer does not exist.');
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
            array(self::CONSUMER_TOKEN_HASH => $hash),
            array('like' => false, 'recursive' => true)
        );

        if (count($consumers) == 1) {
            return reset($consumers);
        } else {
            throw new \common_exception_NotFound('Consumer does not exist.');
        }
    }

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