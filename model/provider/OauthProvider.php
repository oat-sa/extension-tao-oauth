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
 */

namespace oat\taoOauth\model\provider;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use League\OAuth2\Client\Provider\GenericProvider;
use oat\taoOauth\model\exception\OauthException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use \League\OAuth2\Client\Provider\Exception\IdentityProviderException;

/**
 * Class OauthProvider
 *
 * Provides response and request to oauth connector
 *
 * @package oat\taoOauth\model\provider
 */
class OauthProvider extends GenericProvider
{
    /**
     * @inheritDoc
     */
    protected function getAllowedClientOptions(array $options)
    {
        $clientOptions = parent::getAllowedClientOptions($options);
        $clientOptions[] = RequestOptions::ON_STATS;

        return $clientOptions;
    }

    /**
     * Get the response from the server.
     *
     * @param RequestInterface $request
     * @param bool $parse
     * @param array $options
     * @return array|ResponseInterface
     * @throws OauthException
     * @throws IdentityProviderException
     */
    public function getResponse(RequestInterface $request, $parse = true, $options = [])
    {
        $response = $this->sendRequest($request, $options);

        if (!preg_match('/2\d\d/', (string)$response->getStatusCode())) {
            throw new RequestException($response->getReasonPhrase(), $request, $response);
        }

        if ($parse) {
            return $this->parseResponse($response);
        }

        return $response;
    }

    /**
     * Parse the response
     *
     * @param ResponseInterface $response
     * @return array
     * @throws OauthException
     */
    public function parseResponse(ResponseInterface $response)
    {
        try {
            $parsed = parent::parseResponse($response);
            $this->checkResponse($response, $parsed);
        } catch (IdentityProviderException $e) {
            throw new OauthException('An error has occurred during response parsing', 0, $e);
        }

        //@see https://github.com/thephpleague/oauth2-client/issues/466#issuecomment-183746522
        if (!is_array($parsed)) {
            throw new \UnexpectedValueException('Failed to parse server response.');
        }

        return $parsed;
    }

    /**
     * Sends a request instance and returns a response instance.
     *
     * @param RequestInterface $request
     * @param array $options
     * @return ResponseInterface
     */
    protected function sendRequest(RequestInterface $request, $options = [])
    {
        try {
            $response = $this->getHttpClient()->send($request, $options);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        }
        return $response;
    }
}