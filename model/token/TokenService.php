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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\taoOauth\model\token;

use League\OAuth2\Client\Token\AccessToken;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\taoOauth\model\token\provider\TokenProvider;
use oat\taoOauth\model\token\storage\TokenStorage;

class TokenService extends ConfigurableService
{
    use OntologyAwareTrait;
    
    const SERVICE_ID = 'taoOauth/tokenService';

    /**
     * @var TokenProvider
     */
    protected $provider;

    public function __construct(array $options = array())
    {
        parent::__construct($options);
        $this->setOptions([
            'hash' => array(
                'algorithm' => 'sha256',
                'salt' => 10
            ),
            'storage' => array(
                'class' => 'tokenstorage',
                'persistence' => 'default',
                'cache' => 'cache'
            ),
            'token_lifetime' => 3600
        ]);
    }


    public function generateToken(TokenProvider $provider)
    {
//        $this->getClass('http://www.taotesting.com/ontologies/taooauth.rdf#Oauth-consumer')
//            ->createInstanceWithProperties([
//                'http://www.taotesting.com/ontologies/taooauth.rdf#ClientId'=> 'id',
//                'http://www.taotesting.com/ontologies/taooauth.rdf#ClientSecret'=> 'secret',
////                'http://www.taotesting.com/ontologies/taooauth.rdf#TokenUrl'=> '',
////                'http://www.taotesting.com/ontologies/taooauth.rdf#TokenType'=> '',
////                'http://www.taotesting.com/ontologies/taooauth.rdf#GrantType'=> '',
//            ]);

        $this->provider = $provider;

        try {
//            $consumer = $this->getTokenStorage()->getConsumer($provider->getClientId(), $provider->getClientSecret());
            $token = $this->createToken();
//            $this->getTokenStorage()->setConsumerToken($consumer, $token);
            return $token;
        } catch (\common_exception_NotFound $e) {
            throw new \common_exception_Unauthorized('Credentials are not valid.', 0, $e);
        }
    }

    public function verifyToken($tokenHash)
    {
        $token = $this->getTokenStorage()->getToken($tokenHash);
        if (is_null($token)) {
            return false;
        }
        if ($token->hasExpired()) {
            return false;
        }
        return true;
    }

    protected function createToken()
    {
        if ($this->provider->getGrantType() != 'client_credentials') {
            throw new \common_exception_NotImplemented();
        }

        return new AccessToken([
            'access_token' => $this->generateHashedToken($this->provider->getClientId()),
            'expires_in' => $this->getTokenLifeTime(),
            'resource_owner_id' => $this->provider->getResourceOwnerId(),
        ]);
    }

    protected function getTokenLifeTime()
    {
        return (int) $this->getOption('token_ttl') ?: 3600;
    }

    protected function generateHashedToken($clientSecret)
    {
        $salt = \helpers_Random::generateString($this->getSaltLength());
        return $salt.hash($this->getAlgorithm(), $salt.$clientSecret);
    }

    protected function getSaltLength()
    {
        return $this->getOption('hash')['salt'];
    }

    protected function getAlgorithm()
    {
        return $this->getOption('hash')['algorithm'];
    }

    /**
     * Return the storage token
     *
     * @return TokenStorage
     */
    protected function getTokenStorage()
    {
        return $this->getServiceLocator()->get(TokenStorage::SERVICE_ID);
    }
}