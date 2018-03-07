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

namespace oat\taoOauth\scripts\update;

use oat\tao\scripts\update\OntologyUpdater;
use oat\taoOauth\model\bootstrap\OAuth2Type;
use oat\taoOauth\model\storage\ConsumerStorage;
use oat\taoOauth\model\token\TokenService;
use oat\taoPublishing\model\publishing\PublishingAuthService;

class Updater extends \common_ext_ExtensionUpdater
{
    /**
     * @param $initialVersion
     * @return string|void
     * @throws \Exception
     */
    public function update($initialVersion)
    {
        $this->skip('0.0.1', '0.0.6');

        if ($this->isVersion('0.0.6')) {
            OntologyUpdater::syncModels();

            $tokenService = new TokenService();
            $this->getServiceManager()->register(TokenService::SERVICE_ID, $tokenService);

            $consumerStorage = new ConsumerStorage(array(
                ConsumerStorage::OPTION_PERSISTENCE => ConsumerStorage::DEFAULT_PERSISTENCE,
                ConsumerStorage::OPTION_CACHE => ConsumerStorage::DEFAULT_CACHE,
            ));
            $this->getServiceManager()->register(ConsumerStorage::SERVICE_ID, $consumerStorage);

            /** @var PublishingAuthService $publishingAuthService */
            $publishingAuthService = $this->getServiceManager()->get(PublishingAuthService::SERVICE_ID);

            $types = $publishingAuthService->getTypes();
            $oauthType = new OAuth2Type();
            if (!in_array($oauthType, $types)) {
                $types[] = $oauthType;
                $publishingAuthService->setOption(PublishingAuthService::OPTION_TYPES, $types);
                $this->getServiceManager()->register(PublishingAuthService::SERVICE_ID, $publishingAuthService);
            }

            $this->setVersion('0.1.0');
        }

    }
}