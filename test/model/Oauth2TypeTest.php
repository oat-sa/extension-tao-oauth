<?php

namespace oat\taoOauth\test\model;

use GuzzleHttp\Psr7\Request;
use oat\generis\test\TestCase;
use oat\taoOauth\model\bootstrap\OAuth2AuthType;
use oat\taoOauth\model\Oauth2Service;
use oat\taoOauth\model\OAuthClient;
use oat\taoOauth\model\storage\OauthCredentialsFactory;
use PHPUnit_Framework_MockObject_MockObject;
use Psr\Http\Message\RequestInterface;

/**
 * Class AuthTypeTest
 * @package oat\tao\test\unit\auth
 */
class Oauth2TypeTest extends TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject|RequestInterface  */
    private $requestMock;

    public function setUp(): void
    {
        $this->requestMock = $this->createMock(Request::class);
        $this->requestMock->method('getMethod')->willReturn('GET');
        $this->requestMock->method('getUri')->willReturn('https://test.uri');
    }

    /**
     * @dataProvider getGrantTypesCredentials
     *
     * @param $dataProvider
     * @throws \ConfigurationException
     * @throws \common_Exception
     * @throws \oat\taoOauth\model\exception\OauthException
     */
    public function testOauth2TypeWithDifferentGrantTypes($dataProvider)
    {
        $oauth2ServiceMock = $this->getOauth2ServiceMock($dataProvider['out']);
        $authType = new OAuth2AuthType;

        $authType->setServiceLocator($this->getServiceLocator([Oauth2Service::SERVICE_ID => $oauth2ServiceMock]));

        $authType->setCredentials($dataProvider['in']);
        $authType->call($this->requestMock);
    }

    public function testFaildValidationOAuth2Type()
    {

        $authType = new OAuth2AuthType;
        $authType->setServiceLocator($this->getServiceLocator());

        $credentials = [
            'client_id_faild' => 'client_id',
            'client_secret' => 'client_secret',
            'grant_type' => 'client_credentials'
        ];
        $authType->setCredentials($credentials);

        /** @var Request $requestMock */
        $requestMock = $this->createMock(Request::class);

        $this->expectException(\common_exception_ValidationFailed::class);

        $authType->call($requestMock);
    }

    private function getOauth2ServiceMock($credentials)
    {
        $Oauth2ServiceMock = $this->createMock(Oauth2Service::class);
        $clientMock = $this->createMock(OAuthClient::class);
        $clientMock
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'https://test.uri', $credentials);
        $Oauth2ServiceMock->method('getClient')->willReturn($clientMock);
        return $Oauth2ServiceMock;
    }

    private function getServiceLocator($services = [])
    {
        $serviceLocatorMock = $this->getServiceLocatorMock(array_merge($services, [
            OauthCredentialsFactory::class => new OauthCredentialsFactory()
        ]));
        return $serviceLocatorMock;
    }

    public function getGrantTypesCredentials()
    {
        return [
            [
                [
                    'in' => [
                        'client_id' => 'client_id',
                        'client_secret' => 'client_secret',
                        'token_url' => 'token_url',
                        'token_type' => 'Bearer',
                        'grant_type' => 'client_credentials',
                    ],
                    'out' => [
                        'client_id' => 'client_id',
                        'client_secret' => 'client_secret',
                        'token_url' => 'token_url',
                        'token_type' => 'Bearer',
                        'grant_type' => 'client_credentials',
                        'scope'      => '',
                        'body' => null,
                        'headers' => null
                    ]
                ]
            ],
            [
                [
                    'in' => [
                        'client_id' => 'client_id',
                        'client_secret' => 'client_secret',
                        'token_url' => 'token_url',
                        'grant_type' => 'client_credentials',
                        'scope'      => 'read-scope'
                    ],
                    'out' => [
                        'client_id' => 'client_id',
                        'client_secret' => 'client_secret',
                        'token_url' => 'token_url',
                        'grant_type' => 'client_credentials',
                        'token_type' => '',
                        'scope'      => 'read-scope',
                        'body' => null,
                        'headers' => null
                    ]
                ]
            ],
            [
                [
                    'in' => [
                        'client_id' => 'client_id',
                        'client_secret' => 'client_secret',
                        'token_url' => 'token_url',
                        'token_type' => 'Bearer',
                        'grant_type' => 'password',
                        'username' => 'password',
                        'password' => 'password'
                    ],
                    'out' => [
                        'client_id' => 'client_id',
                        'client_secret' => 'client_secret',
                        'token_url' => 'token_url',
                        'token_type' => 'Bearer',
                        'grant_type' => 'password',
                        'username' => 'password',
                        'password' => 'password',
                        'body' => null,
                        'headers' => null
                    ]
                ],
            ],
            [
                [
                    'in' => [
                        'token_url' => 'token_url',
                        'token_type' => 'Bearer',
                        'grant_type' => 'authorization_code',
                        'client_id' => 'client_id',
                        'client_secret' => 'client_secret',
                        'code' => 'code'
                    ],
                    'out' => [
                        'token_url' => 'token_url',
                        'token_type' => 'Bearer',
                        'grant_type' => 'authorization_code',
                        'code' => 'code',
                        'client_id' => 'client_id',
                        'client_secret' => 'client_secret',
                        'body' => null,
                        'headers' => null
                    ]
                ],
            ]
        ];
    }
}
