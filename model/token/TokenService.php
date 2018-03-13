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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\taoOauth\model\token;

use League\OAuth2\Client\Token\AccessToken;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\taoOauth\model\OAuthClient;
use oat\taoOauth\model\storage\ConsumerStorage;
use oat\taoOauth\model\token\provider\TokenProvider;

class TokenService extends ConfigurableService
{
    use OntologyAwareTrait;
    
    const SERVICE_ID = 'taoOauth/tokenService';

    const OPTION_TOKEN_LIFETIME = 'token_lifetime';
    const OPTION_HASH = 'hash';
    const OPTION_HASH_ALGORITHM = 'algorithm';
    const OPTION_HASH_SALT_LENGTH = 'salt_length';

    /** @var TokenProvider */
    protected $provider;

    public function generateToken(TokenProvider $provider)
    {
        $this->provider = $provider;

        try {
            $consumer = $this->getConsumerStorage()->getConsumer($provider->getClientId(), $provider->getClientSecret());
            $token = $this->createToken($consumer);
            $this->getConsumerStorage()->setConsumerToken($consumer, $token);
            return $token;
        } catch (\common_exception_NotFound $e) {
            throw new \common_exception_Unauthorized('Credentials are not valid: ' . $e->getMessage());
        }
    }

    /**
     * Verify an oauth token
     *
     * Check if token is set from consumer storage, not null and not expired
     *
     * @param $tokenHash
     * @return bool
     */
    public function verifyToken($tokenHash)
    {
        $tokenHash = $this->prepareTokenHash($tokenHash);

        try {
            $token = $this->getConsumerStorage()->getToken($tokenHash);
        } catch (\common_Exception $e) {
            return false;
        }

        if (is_null($token)) {
            return false;
        }
        if ($token->hasExpired()) {
            return false;
        }
        return true;
    }

    /**
     * Prepare a token to ingest. By default remove Bearer prefix
     *
     * @param $hash
     * @return string
     */
    public function prepareTokenHash($hash)
    {
        return substr($hash, 7);
    }

    /**
     * Create a token
     *
     * @return AccessToken
     * @throws \common_exception_NotImplemented
     */
    protected function createToken(\core_kernel_classes_Resource $consumer)
    {
        if ($this->provider->getGrantType() != OAuthClient::DEFAULT_GRANT_TYPE) {
            throw new \common_exception_NotImplemented('Token service only support client_credentials value.');
        }

        return new AccessToken([
            'access_token' => $this->generateHashedToken($consumer),
            'expires_in' => $this->getTokenLifeTime(),
            'resource_owner_id' => $this->provider->getResourceOwnerId(),
        ]);
    }


    /**
     * Generate a token hash based on hash config
     *
     * @param $clientSecret
     * @return string
     */
    protected function generateHashedToken($clientSecret)
    {
        $salt = \helpers_Random::generateString($this->getSaltLength());
        return $salt.hash($this->getAlgorithm(), $salt.$clientSecret);
    }

    /**
     * Get the token lifetime
     *
     * @return int
     */
    protected function getTokenLifeTime()
    {
        return (int) $this->getOption(self::OPTION_TOKEN_LIFETIME) ?: 3600;
    }

    /**
     * Get the hash salt length
     *
     * @return int
     */
    protected function getSaltLength()
    {
        return $this->getOption(self::OPTION_HASH)[self::OPTION_HASH_SALT_LENGTH];
    }

    /**
     * Get the hash algorithm
     *
     * @return string
     */
    protected function getAlgorithm()
    {
        return $this->getOption(self::OPTION_HASH)[self::OPTION_HASH_ALGORITHM];
    }

    /**
     * Return the storage token
     *
     * @return ConsumerStorage
     */
    protected function getConsumerStorage()
    {
        return $this->getServiceLocator()->get(ConsumerStorage::SERVICE_ID);
    }
}