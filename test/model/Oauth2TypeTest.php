<?php

namespace oat\taoOauth\test\model;

use GuzzleHttp\Psr7\Request;
use oat\generis\test\TestCase;
use oat\tao\model\auth\BasicAuthType;
use GuzzleHttp\Client;
use oat\taoOauth\model\bootstrap\OAuth2AuthType;
use oat\taoOauth\model\bootstrap\OAuth2Type;
use oat\taoOauth\model\Oauth2Service;
use oat\taoOauth\model\OAuthClient;

/**
 * Class AuthTypeTest
 * @package oat\tao\test\unit\auth
 */
class Oauth2TypeTest extends TestCase
{

    /** @var array */
    private $credentials;

    public function setUp()
    {
        $this->credentials = [
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'token_url' => 'token_url',
            'token_type' => 'token_type',
            'grant_type' => 'grant_type'
        ];
    }

    public function testOAuth2Type()
    {
        $Oauth2Service = $this->createMock(Oauth2Service::class);
        $clientMock = $this->createMock(OAuthClient::class);

        $requestMock = $this->createMock(Request::class);
        $requestMock->method('getMethod')->willReturn('GET');
        $requestMock->method('getUri')->willReturn('https://test.uri');
        $clientMock
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'https://test.uri', array_merge($this->credentials, [ 'body' => null, 'headers' => null]));

        $Oauth2Service->method('getClient')->willReturn($clientMock);

        $serviceLocatorMock = $this->getServiceLocatorMock([
            Oauth2Service::SERVICE_ID => $Oauth2Service
        ]);

        $authType = new OAuth2AuthType;

        $authType->setServiceLocator($serviceLocatorMock);

        $authType->setCredentials($this->credentials);

        $authType->call($requestMock);
    }

    public function testFaildValidationOAuth2Type()
    {
        $authType = new OAuth2AuthType;
        $credentials = [
            'client_id_faild' => 'client_id',
            'client_secret' => 'client_secret',
        ];
        $authType->setCredentials($credentials);

        /** @var Request $requestMock */
        $requestMock = $this->createMock(Request::class);

        $this->expectException(\common_exception_ValidationFailed::class);

        $authType->call($requestMock);
    }
}
