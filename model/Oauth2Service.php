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

namespace oat\taoOauth\model;

use oat\oatbox\service\ConfigurableService;
use oat\taoOauth\model\provider\Provider;
use oat\taoOauth\model\storage\ConsumerStorage;
use oat\taoOauth\model\token\TokenService;
use oat\taoOauth\model\user\UserService;

class Oauth2Service extends ConfigurableService
{
    const SERVICE_ID = 'taoOauth/oauth2Service';

    /** @var \core_kernel_classes_Resource The oauth consumer Of the current request */
    protected $consumer;

    /**
     * Validate a request by checking the http header authorization
     *
     * Verify the token then fetch and load the request consumer
     *
     * @param \common_http_Request $request
     * @return $this
     * @throws \common_http_InvalidSignatureException
     */
    public function validate(\common_http_Request $request)
    {

        $tokenService = $this->getTokenService();
        $tokenHash    = $request->getHeaderValue('Authorization');

        if (!$tokenHash) {
            throw new \common_http_InvalidSignatureException('invalid_client');
        }
        if (!$tokenService->verifyToken($tokenHash)) {
            throw new \common_http_InvalidSignatureException('invalid_client');
        }

        try {
            $tokenHash = $tokenService->prepareTokenHash($tokenHash);
            $this->consumer = $this->getConsumerStorage()->getConsumerByTokenHash($tokenHash);
        } catch (\common_exception_NotFound $e) {
            throw new \common_http_InvalidSignatureException('invalid_client');
        }

        return $this;
    }

    /**
     * Get loaded consumer of request
     *
     * Must be called after $this->valid() method to have a valided consumer
     *
     * @return \core_kernel_classes_Resource
     * @throws \common_http_InvalidSignatureException
     */
    public function getConsumer()
    {
        if (!$this->consumer) {
            throw new \common_http_InvalidSignatureException();
        }

        return $this->getUserService()->getConsumerUser($this->consumer);
    }

    /**
     * Create Oauth http client from $data
     *
     * Add default option to $data
     *
     * @param array $data
     * @return mixed
     */
    public function getClient(array $data)
    {
        $data = array_merge(
            [
                'token_storage' => ConsumerStorage::DEFAULT_CACHE,
                Provider::GRANT_TYPE => OAuthClient::DEFAULT_GRANT_TYPE,
                OAuthClient::OPTION_TOKEN_KEY => md5('token_' . $data[Provider::CLIENT_ID]),
                Provider::AUTHORIZE_URL => false,
                Provider::RESOURCE_OWNER_DETAILS_URL => false,
            ],
            $data
        );

        return $this->propagate(new OAuthClient($data));
    }

    /**
     * Spawn a consumer based on key, secret and token url
     *
     * Delete all others key/secret consumer
     *
     * @param $key
     * @param $secret
     * @param $tokenUrl
     * @param $role
     * @return \core_kernel_classes_Resource
     */
    public function spawnConsumer($key, $secret, $tokenUrl, $role = null)
    {
        $this->getConsumerStorage()->deleteConsumer($key, $secret);
        $consumer = $this->getConsumerStorage()->createConsumer($key, $secret, $tokenUrl);
        $this->getUserService()->createConsumerUser($consumer, $role);
        return $consumer;
    }

    /**
     * Generate a random client key
     *
     * @return string
     */
    public function generateClientKey()
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }

    /**
     * Generate a random client secret based on client key
     *
     * @param $clientKey
     * @return string
     */
    public function generateClientSecret($clientKey)
    {
        $salt = \helpers_Random::generateString(32);
        return $salt.hash('sha256', $salt.$clientKey);
    }

    /**
     * Get the default token url in taoOauth extension
     *
     * @return string
     */
    public function getDefaultTokenUrl()
    {
        return _url('requestToken', 'TokenApi', 'taoOauth');
    }

    /**
     * Return the consumer storage
     *
     * @return ConsumerStorage
     */
    protected function getConsumerStorage()
    {
        return $this->getServiceLocator()->get(ConsumerStorage::SERVICE_ID);
    }

    /**
     * Return the token service
     *
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->getServiceLocator()->get(TokenService::SERVICE_ID);
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getServiceLocator()->get(UserService::SERVICE_ID);
    }
}
