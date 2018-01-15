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

    const CONSUMER_CLIENT_ID_PROPERTY = 'http://www.taotesting.com/ontologies/taooauth.rdf#ClientId';

    const CONSUMER_CLIENT_SECRET_PROPERTY = 'http://www.taotesting.com/ontologies/taooauth.rdf#ClientSecret';

    const CONSUMER_TOKEN_HASH_PROPERTY = 'http://www.taotesting.com/ontologies/taooauth.rdf#TokenHash';

    const CONSUMER_TOKEN_PROPERTY = 'http://www.taotesting.com/ontologies/taooauth.rdf#Token';


    public function setToken(AccessToken $token)
    {
        $consumer = $this->getRootClass()->createInstanceWithProperties(
           // $clientId . $clientSecret,

        );
        $consumer->setPropertyValue(new \core_kernel_classes_Property('client-id-uri'), $clientId);
        $consumer->setPropertyValue(new \core_kernel_classes_Property('client-secret-uri'), $clientSecret);
        $consumer->setPropertyValue(new \core_kernel_classes_Property('token-hash'), $token->getToken());
        $consumer->setPropertyValue(new \core_kernel_classes_Property('token-uri'), json_encode($token));

        $this->getCache()->set($token->getToken(), json_encode($token));
    }

    public function getToken($tokenHash)
    {
        if ($this->getCache()->exists($tokenHash)) {
            $token = new AccessToken(json_decode($this->getCache()->get($tokenHash), true));
        } else {
            $consumers = $this->getRootClass()->searchInstances(
                array(self::CONSUMER_TOKEN_HASH_PROPERTY => $tokenHash),
                array('like' => false, 'recursive' => true)
            );
            if (count($consumers) != 1) {
                return null;
            }
            $encodedToken = $consumers[0]->getOnePropertyValue(self::CONSUMER_TOKEN_PROPERTY);
            $token = new AccessToken(json_decode($encodedToken, true));
            $this->getCache()->set($token->getToken(), $encodedToken);
        }

        return $token;
    }

    public function consumerExists($clientId, $clientSecret)
    {
        $consumers = $this->getRootClass()->searchInstances(
            array(
                TokenStorage::CONSUMER_CLIENT_ID_PROPERTY => $clientId,
                TokenStorage::CONSUMER_CLIENT_SECRET_PROPERTY => $clientSecret,
            ),
            array('like' => false, 'recursive' => true)
        );

        return count($consumers) == 1;
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