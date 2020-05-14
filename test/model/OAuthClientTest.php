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
 * Copyright (c) 2017-2018 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoOauth\test\model;

use common_Exception;
use common_exception_NotImplemented;
use common_persistence_KeyValuePersistence;
use common_persistence_Manager;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use oat\taoOauth\model\exception\OauthException;
use oat\taoOauth\model\OAuthClient;
use oat\taoOauth\model\provider\OauthProvider;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use oat\generis\test\TestCase;
use function FastRoute\TestFixtures\empty_options_cached;

class OAuthClientTest extends TestCase
{

    /**
     * @dataProvider getDataProviderSuccessCode
     * @param $dataProvider
     */
    public function testRequest($dataProvider)
    {
        $uri = $this->getMockForAbstractClass(UriInterface::class);

        if (!empty($dataProvider['exception'])) {
            $this->expectException(OauthException::class);
        }

        $this->assertInstanceOf(ResponseInterface::class, $this->getClient($dataProvider)->request('POST', $uri));
    }

    /**
     * @dataProvider getDataProviderFailedCode
     * @param $dataProvider
     */
    public function testRequestFailed($dataProvider)
    {
        $this->expectException(OauthException::class);
        $uri = $this->getMockForAbstractClass(UriInterface::class);

        $this->assertInstanceOf(ResponseInterface::class, $this->getClient($dataProvider)->request('POST', $uri, [], true));
    }

    /**
     * @dataProvider getDataProviderFailedResponse
     * @param $dataProvider
     */
    public function testResponseFailed($dataProvider)
    {
        $this->expectException(OauthException::class);
        $uri = $this->getMockForAbstractClass(UriInterface::class);

        $this->assertInstanceOf(ResponseInterface::class, $this->getClient($dataProvider)->request('POST', $uri, [], true));
    }

    /**
     */
    public function testNotImplementedSendAsync()
    {
        $this->expectException(common_exception_NotImplemented::class);
        $this->getClient([])->sendAsync($this->getMockForAbstractClass(RequestInterface::class));
    }

    /**
     */
    public function testNotImplementedRequestAsync()
    {
        $this->expectException(common_exception_NotImplemented::class);
        $uri = $this->getMockForAbstractClass(UriInterface::class);

        $this->getClient([])->requestAsync('POST', $uri);
    }

    /**
     */
    public function testNotImplementedgetConfig()
    {
        $this->expectException(common_exception_NotImplemented::class);
        $this->getClient([])->getConfig();
    }

    /**
     * @param $dataProvider
     * @return OAuthClient
     */
    protected function getClient($dataProvider)
    {
        $client = $this->getMockBuilder(OAuthClient::class)->setMethods([
            'getProvider','logInfo'
        ])->setConstructorArgs([
            [
                OAuthClient::OPTION_TOKEN_STORAGE => 'some_storage',
                OAuthClient::OPTION_TOKEN_KEY => 'token_key',
            ]
        ])->getMockForAbstractClass();

        $client->setServiceLocator($this->mockServiceLocator($dataProvider));

        $client
            ->method('getProvider')
            ->willReturn($this->mockProvider($dataProvider));

        return $client;
    }

    protected function mockServiceLocator($dataProvider)
    {
        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $serviceLocator->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($prop) use ($dataProvider){
                switch ($prop){
                    case \common_persistence_Manager::SERVICE_ID:
                        return $this->mockPersistenceManager($dataProvider);
                        break;
                }
            }));

        return $serviceLocator;
    }

    protected function mockProvider($data)
    {
        $provider = $this->getMockBuilder(OauthProvider::class)->disableOriginalConstructor()->getMock();
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $requestMock
            ->method('getBody')
            ->willReturn($this->getMockForAbstractClass(StreamInterface::class));

        $provider
            ->method('getAuthenticatedRequest')
            ->willReturn($requestMock);

        $provider->method('getAccessToken')
            ->with('client_credentials', []);

        if (isset($data['exception'])){
            $provider
                ->method('getResponse')
                ->willThrowException($data['exception']);
        } else {
            $provider
                ->method('getResponse')
                ->willReturn($this->mockResponse($data));
        }

        return $provider;
    }

    protected function mockResponse($data)
    {
        $responseMock = $this->getMockForAbstractClass(ResponseInterface::class);
        $responseMock
            ->method('getStatusCode')
            ->willReturn($data['statusCode'] ?? null);

        return $responseMock;

    }
    protected function mockPersistenceManager($dataProvider)
    {
        $manager = $this->getMockBuilder(common_persistence_Manager::class)->disableOriginalConstructor()->getMock();
        $persistence = $this->getMockBuilder(common_persistence_KeyValuePersistence::class)->disableOriginalConstructor()->getMock();

        $persistence
            ->method('get')
            ->willReturn($this->mockAccessToken($dataProvider));

        $manager
            ->method('getPersistenceById')
            ->willReturn($persistence);

        return $manager;
    }

    protected function mockAccessToken($dataProvider)
    {
        $token = $this->getMockBuilder(AccessToken::class)->disableOriginalConstructor()->getMock();

        $token
            ->method('hasExpired')
            ->willReturn($dataProvider['hasExpired'] ?? null);

        return $token;
    }

    public function getDataProviderSuccessCode()
    {
        $requestException = $this->getMockBuilder(RequestException::class)->disableOriginalConstructor()->getMock();
        $requestException
            ->method('getResponse')
            ->willReturn($this->mockResponse(['statusCode' => 200]));

        return [
            [
                [
                    'statusCode' => 200,
                    'hasExpired' => false,
                ],
            ],
            [
                [
                    'statusCode' => 200,
                    'hasExpired' => true,
                    'exception' => $requestException,
                ],
            ]

        ];
    }

    public function getDataProviderFailedCode()
    {
        return [
            [
                [
                    'statusCode' => 401
                ],
            ],
            [
                [
                    'statusCode' => 401
                ],
            ],
            [
                [
                    'statusCode' => 500
                ],
            ],

        ];
    }

    public function getDataProviderFailedResponse()
    {
        return [
            [
                [
                    'exception' => $this->getMockBuilder(ConnectException::class)->disableOriginalConstructor()->getMock()
                ],
            ],
            [
                [
                    'exception' => $this->getMockBuilder(IdentityProviderException::class)->disableOriginalConstructor()->getMock()
                ],
            ],
            [
                [
                    'exception' =>  $this->getMockBuilder(\Exception::class)->disableOriginalConstructor()->getMock()
                ],
            ]
        ];
    }
}
