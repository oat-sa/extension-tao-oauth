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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types=1);

namespace oat\taoOauth\test\model\storage;

use common_exception_NotFound;
use common_persistence_KeyValuePersistence;
use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use League\OAuth2\Client\Token\AccessToken;
use oat\generis\model\data\Ontology;
use oat\generis\persistence\PersistenceManager;
use oat\generis\test\TestCase;
use oat\oatbox\log\LoggerService;
use oat\taoOauth\model\storage\ConsumerStorage;
use PHPUnit\Framework\MockObject\MockObject;

class ConsumerStorageTest extends TestCase
{
    private const VALID_JSON_TOKEN = '{"access_token": "abc123", "expires": 1645172996}';
    private const VALID_ARRAY_TOKEN = [
        'access_token' => 'abc123',
        'expires' => '1645172996',
    ];

    /** @var ConsumerStorage */
    private $subject;

    /** @var PersistenceManager|MockObject */
    private $persistenceManager;

    /** @var common_persistence_KeyValuePersistence|MockObject */
    private $cachePersistence;

    /** @var Ontology|MockObject */
    private $ontology;

    /** @var core_kernel_classes_Class|MockObject */
    private $consumerClass;

    /** @var core_kernel_classes_Resource|MockObject */
    private $consumerResource;

    /** @var core_kernel_classes_Property|MockObject */
    private $tokenProperty;

    public function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->logger = $this->createMock(LoggerService::class);
        $this->persistenceManager = $this->createMock(PersistenceManager::class);
        $this->cachePersistence = $this->createMock(common_persistence_KeyValuePersistence::class);

        $this->consumerClass = $this->createMock(core_kernel_classes_Class::class);
        $this->consumerResource = $this->createMock(core_kernel_classes_Resource::class);
        $this->tokenProperty = $this->createMock(core_kernel_classes_Property::class);

        $this->persistenceManager
            ->method('getPersistenceById')
            ->willReturn($this->cachePersistence);

        $this->subject = new ConsumerStorage();
        $this->subject->setServiceManager(
            $this->getServiceLocatorMock(
                [
                    PersistenceManager::SERVICE_ID => $this->persistenceManager,
                    Ontology::SERVICE_ID => $this->ontology,
                    LoggerService::SERVICE_ID => $this->logger,
                ]
            )
        );
    }

    public function testGetValidTokenFromCache(): void
    {
        $this->cachePersistence
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $this->cachePersistence
            ->expects($this->once())
            ->method('get')
            ->with('abc123')
            ->willReturn(self::VALID_JSON_TOKEN);

        $this->assertEquals(
            new AccessToken(self::VALID_ARRAY_TOKEN),
            $this->subject->getToken('abc123')
        );
    }

    public function testGetInvalidTokenFromCacheWillRestoreTokenFromDataBase(): void
    {
        $this->cachePersistence
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $this->cachePersistence
            ->expects($this->once())
            ->method('get')
            ->with('abc123')
            ->willReturn('invalid {json}');

        $this->cachePersistence
            ->expects($this->once())
            ->method('set');

        $this->ontology
            ->expects($this->once())
            ->method('getClass')
            ->with(ConsumerStorage::CONSUMER_CLASS)
            ->willReturn($this->consumerClass);

        $this->ontology
            ->expects($this->once())
            ->method('getProperty')
            ->with(ConsumerStorage::CONSUMER_TOKEN)
            ->willReturn($this->tokenProperty);

        $this->consumerClass
            ->expects($this->once())
            ->method('searchInstances')
            ->willReturn(
                [
                    $this->consumerResource,
                ]
            );

        $this->consumerResource
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->with($this->tokenProperty)
            ->willReturn(self::VALID_JSON_TOKEN);

        $this->assertEquals(
            new AccessToken(self::VALID_ARRAY_TOKEN),
            $this->subject->getToken('abc123')
        );
    }

    public function testGetInvalidTokenFromDataBaseWillThrowException(): void
    {
        $this->cachePersistence
            ->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $this->ontology
            ->expects($this->once())
            ->method('getClass')
            ->with(ConsumerStorage::CONSUMER_CLASS)
            ->willReturn($this->consumerClass);

        $this->ontology
            ->expects($this->once())
            ->method('getProperty')
            ->with(ConsumerStorage::CONSUMER_TOKEN)
            ->willReturn($this->tokenProperty);

        $this->consumerClass
            ->expects($this->once())
            ->method('searchInstances')
            ->willReturn(
                [
                    $this->consumerResource,
                ]
            );

        $this->consumerResource
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->with($this->tokenProperty)
            ->willReturn('invalid {json}');

        $this->expectException(common_exception_NotFound::class);
        $this->expectExceptionMessage('The token abc123... contains an invalid JSON: Syntax error');

        $this->subject->getToken('abc123');
    }
}
