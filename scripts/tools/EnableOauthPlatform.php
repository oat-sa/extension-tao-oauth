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
 * Copyright (c) 2018 (update and modification) Open Assessment Technologies SA;
 *
 */

namespace oat\taoOauth\scripts\tools;

use oat\oatbox\extension\InstallAction;
use oat\tao\model\session\restSessionFactory\RestSessionFactory;
use oat\taoOauth\model\bootstrap\Oauth2OnlySessionBuilder;

class EnableOauthPlatform extends InstallAction
{
    public function __invoke($params)
    {
        /** @var RestSessionFactory $service */
        $service = $this->getServiceLocator()->get(RestSessionFactory::SERVICE_ID);
        $service->setOption(RestSessionFactory::OPTION_BUILDERS, [Oauth2OnlySessionBuilder::class]);
        $this->registerService(RestSessionFactory::SERVICE_ID, $service);
    }

}