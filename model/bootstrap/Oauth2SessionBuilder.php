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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoOauth\model\bootstrap;

use oat\oatbox\user\LoginFailedException;
use oat\tao\model\routing\Resolver;
use oat\tao\model\session\restSessionFactory\SessionBuilder;
use oat\taoOauth\model\Oauth2Service;
use oat\taoOauth\model\OauthController;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class Oauth2SessionBuilder implements SessionBuilder, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function isApplicable(\common_http_Request $request, Resolver $resolver)
    {
        return is_subclass_of($resolver->getControllerClass(), OauthController::class);
    }

    public function getSession(\common_http_Request $request)
    {
        $service = $this->getServiceLocator()->get(Oauth2Service::SERVICE_ID);

        try {
            $user = $service
                ->validate($request)
                ->getConsumer();
            return new \common_session_RestSession($user);
        } catch (\common_http_InvalidSignatureException $e) {
            throw new LoginFailedException([$e->getMessage()]);
        }

    }

}