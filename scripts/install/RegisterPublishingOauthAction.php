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
 */

namespace oat\taoOauth\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\taoOauth\model\OAuth2Type;
use oat\taoPublishing\model\publishing\PublishingAuthService;

class RegisterPublishingOauthAction extends InstallAction
{
    public function __invoke($params)
    {
        /** @var PublishingAuthService $publishingAuthService */
        $publishingAuthService = $this->getServiceLocator()->get(PublishingAuthService::SERVICE_ID);

        $types = $publishingAuthService->getTypes();
        $oauthType = new OAuth2Type();
        if (!in_array($oauthType, $types)) {
            $types[] = $oauthType;
            $publishingAuthService->setOption(PublishingAuthService::OPTION_TYPES, $types);
            $this->registerService(PublishingAuthService::SERVICE_ID, $publishingAuthService);
        }

        return \common_report_Report::createSuccess('Oauth publishing action successfully registered.');
    }

}