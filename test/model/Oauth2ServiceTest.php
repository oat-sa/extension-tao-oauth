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

use common_http_InvalidSignatureException;
use core_kernel_classes_Resource;
use oat\taoOauth\model\Oauth2Service;
use oat\taoOauth\model\OAuthClient;
use oat\taoOauth\model\storage\ConsumerStorage;
use oat\taoOauth\model\token\TokenService;
use oat\taoOauth\model\user\UserService;
use Zend\ServiceManager\ServiceLocatorInterface;
use oat\generis\test\TestCase;
use oat\generis\test\MockObject;

class Oauth2ServiceTest extends TestCase
{

    /**
     * @throws common_http_InvalidSignatureException
     * @dataProvider getValidateDataProvider
     */
    public function testValidate($dataProvider)
    {
        $service = $this->getService($dataProvider);

        $request = $this->getMockBuilder(\common_http_Request::class)->disableOriginalConstructor()->getMock();
        $request
            ->method('getHeaderValue')
            ->willReturn($dataProvider['headers']['Authorization'] ?? null);

        $this->assertInstanceOf(Oauth2Service::class, $service->validate($request));
        $this->assertInstanceOf(core_kernel_classes_Resource::class, $service->getConsumer());
    }

    /**
     * @throws common_http_InvalidSignatureException
     * @dataProvider getValidateDataProviderFailed
     */
    public function testValidateFailed($dataProvider)
    {
        $this->expectException(common_http_InvalidSignatureException::class);
        $service = $this->getService($dataProvider);

        $request = $this->getMockBuilder(\common_http_Request::class)->disableOriginalConstructor()->getMock();
        $request
            ->method('getHeaderValue')
            ->willReturn($dataProvider['headers']['Authorization'] ?? null);

        $this->assertInstanceOf(Oauth2Service::class, $service->validate($request));
    }

    public function testCallGetConsumerWithoutCallingValidateFirst()
    {
        $this->expectException(common_http_InvalidSignatureException::class);
        $service = $this->getService([]);

        $service->getConsumer();
    }

    public function testGetClient()
    {
        $service = $this->getService([]);

        $this->assertInstanceOf(OAuthClient::class, $service->getClient([
            'client_id' => 'someClientId'
        ]));
    }

    public function testSpawnConsumer()
    {
        $service = $this->getService([]);

        $this->assertInstanceOf(core_kernel_classes_Resource::class, $service->spawnConsumer('key', 'secret', 'tokenUrl'));
    }

    public function testGenerateClientKey()
    {
        $service = $this->getService([]);

        $this->assertIsString($service->generateClientKey());
    }

    public function testGenerateClientSecret()
    {
        $service = $this->getService([]);

        $this->assertIsString($service->generateClientSecret('client_key'));
    }

    public function testGetDefaultTokenUrl()
    {
        $service = $this->getService([]);

        $this->assertIsString($service->getDefaultTokenUrl());
    }

    protected function getService($dataProvider)
    {
        $service = new Oauth2Service();

        $service->setServiceLocator($this->mockServiceLocator($dataProvider));

        return $service;
    }

    protected function mockServiceLocator($dataProvider)
    {
        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $serviceLocator->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($prop) use ($dataProvider){
                switch ($prop){
                    case TokenService::SERVICE_ID:
                        return $this->mockTokenService($dataProvider);
                        break;
                    case ConsumerStorage::SERVICE_ID:
                        return $this->mockConsumerStorage($dataProvider);
                        break;
                    case UserService::SERVICE_ID:
                        return $this->mockUserService($dataProvider);
                        break;
                }
            }));

        return $serviceLocator;
    }

    protected function mockTokenService($data)
    {
        $service = $this->getMockBuilder(TokenService::class)->disableOriginalConstructor()->getMock();

        $service
            ->method('verifyToken')
            ->willReturn($data['verifyToken'] ?? null);
        $service
            ->method('prepareTokenHash')
            ->willReturn($data['prepareTokenHash'] ?? null);

        return $service;
    }

    protected function mockConsumerStorage($data)
    {
        $service = $this->getMockBuilder(ConsumerStorage::class)->disableOriginalConstructor()->getMock();
        if (isset($data['getConsumerByTokenHashException'])) {
            $service
                ->method('getConsumerByTokenHash')
                ->willThrowException($data['getConsumerByTokenHashException']);
        }else {
            $service
                ->method('getConsumerByTokenHash')
                ->willReturn($this->mockResource());
        }

        $service
            ->method('createConsumer')
            ->willReturn($this->mockResource());

        return $service;
    }

    protected function mockUserService($dataProvider)
    {
        $service = $this->getMockBuilder(UserService::class)->disableOriginalConstructor()->getMock();

        $service
            ->method('getConsumerUser')
            ->willReturn($this->mockResource());

        return $service;
    }

    public function getValidateDataProvider()
    {
        return [
            [
                [
                    'headers' => [
                        'Authorization' => 'Bearer buhuu'
                    ],
                    'verifyToken' => true,
                    'prepareTokenHash' => 'some hash',
                ],
            ],
        ];
    }

    public function getValidateDataProviderFailed()
    {
        return [
            [
                [
                    'headers' => [
                        'Authorization' => 'Bearer buhuu'
                    ],
                    'verifyToken' => false,
                ],
            ],
            [
                [
                    'headers' => [],
                    'verifyToken' => true,
                ],
            ],
            [
                [
                    'headers' => [
                        'Authorization' => 'Bearer buhuu'
                    ],
                    'verifyToken' => true,
                    'getConsumerByTokenHashException' => $this->getMockBuilder(\common_exception_NotFound::class)->disableOriginalConstructor()->getMock()
                ],
            ],
        ];
    }

    /**
     * @return MockObject
     */
    protected function mockResource()
    {
        $mock = $this->getMockBuilder(core_kernel_classes_Resource::class)->disableOriginalConstructor()->getMock();

        return $mock;
    }
}
