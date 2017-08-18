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

use GuzzleHttp\ClientInterface as HttpClientInterface;
use GuzzleHttp\Exception\RequestException;
use League\OAuth2\Client\Provider\GenericProvider;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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
     * Get the response from the server.
     *
     * @param RequestInterface $request
     * @param bool $parse
     * @return array|ResponseInterface
     */
    public function getResponse(RequestInterface $request, $parse = true)
    {
        $response = $this->sendRequest($request);

        if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 202) {
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
     */
    public function parseResponse(ResponseInterface $response)
    {
        $parsed = parent::parseResponse($response);
        $this->checkResponse($response, $parsed);

        //@see https://github.com/thephpleague/oauth2-client/issues/466#issuecomment-183746522
        if (!is_array($parsed)) {
            throw new \UnexpectedValueException('Failed to parse server response.');
        }

        return $parsed;
    }

    /**
     * Add headers and params to access token request.
     *
     * @param array $params
     * @return array
     */
    protected function getAccessTokenOptions(array $params)
    {
        $options = ['headers' =>
            ['content-type' => 'application/json'],
            ['cache-control' => 'no-cache'],
        ];

        if ($this->getAccessTokenMethod() === self::METHOD_POST) {
            $options['body'] = json_encode($params, true);
        }

        return $options;
    }

    /**
     * Get the additional header for all requests.
     *
     * @return array
     */
    protected function getDefaultHeaders()
    {
        return ['Content-Type' => 'application/json'];
    }

}