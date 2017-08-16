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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoOauth\model\connector\implementation;

use function GuzzleHttp\Psr7\stream_for;
use League\OAuth2\Client\Provider\AbstractProvider;
use oat\taoOauth\model\connector\Connector;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

/**
 * Class NoAuthConnector
 *
 * Class to handle a non oauth2 connection.
 *
 * @package oat\taoOauth\model\connector\implementation
 */
class NoAuthConnector implements Connector
{
    /**
     * Request the $url, and return the response.
     *
     * @param $url
     * @param array $params
     * @param string $method
     * @param array $headers
     * @return mixed
     */
    public function request($url, array $params = array(), $method = AbstractProvider::METHOD_GET, array $headers = array())
    {
        $request = $this->getRequest($url, $method, $params);
        return $this->send($request);
    }

    /**
     * Get a request, add $params to request body and return it
     *
     * @param $url
     * @param string $method
     * @param array $params
     * @return Request
     */
    protected function getRequest($url, $method = AbstractProvider::METHOD_GET, array $params = array())
    {
        $request = new Request($method, $url);
        if (!empty($params)) {
            $body = stream_for(json_encode($params));
            $request = $request->withBody($body)->withAddedHeader('Content-Type', 'application/json');
        }
        return $request;
    }

    /**
     * Send the request to the server and return the decoded content.
     *
     * @param RequestInterface $request
     * @return mixed
     */
    protected function send(RequestInterface $request)
    {
        $response = (new Client())->send($request);
        return json_decode($response->getBody()->getContents(), true);
    }

}