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
use oat\tao\model\oauth\DataStore;
use oat\taoOauth\model\provider\Provider;
use oat\taoOauth\model\storage\ConsumerStorage;
use oat\taoOauth\model\token\TokenService;

class Oauth2Service extends ConfigurableService
{
    const SERVICE_ID = 'taoOauth/oauth2Service';

    const CLASS_URI_OAUTH_CONSUMER = DataStore::CLASS_URI_OAUTH_CONSUMER;

    const PROPERTY_OAUTH_KEY = DataStore::PROPERTY_OAUTH_KEY;
    const PROPERTY_OAUTH_SECRET = DataStore::PROPERTY_OAUTH_SECRET;
    const PROPERTY_OAUTH_CALLBACK = DataStore::PROPERTY_OAUTH_CALLBACK;
    const PROPERTY_OAUTH_TOKEN = 'http://www.taotesting.com/ontologies/taooauth.rdf#Token';
    const PROPERTY_OAUTH_TOKEN_HASH = 'http://www.taotesting.com/ontologies/taooauth.rdf#TokenHash';
    const PROPERTY_OAUTH_TOKEN_URL = 'http://www.taotesting.com/ontologies/taooauth.rdf#TokenUrl';

    const PROPERTY_OAUTH_TOKEN_TYPE = 'http://www.taotesting.com/ontologies/taooauth.rdf#TokenType';
    const PROPERTY_OAUTH_GRANT_TYPE = 'http://www.taotesting.com/ontologies/taooauth.rdf#GrantType';

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
        $headers = $request->getHeaders();
        $tokenService = $this->getTokenService();

        if (!isset($headers['Authorization'])) {
            throw new \common_http_InvalidSignatureException('invalid_client');
        }
        $tokenHash = $headers['Authorization'];

        if (!$tokenService->verifyToken($tokenHash)) {
            throw new \common_http_InvalidSignatureException('invalid_client');
        }

        try {
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

        return $this->consumer;
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
            ],
            $data
        );

        return $this->propagate(new OAuthClient($data));
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
}