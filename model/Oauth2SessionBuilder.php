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

namespace oat\taoOauth\model;

use oat\tao\model\routing\Resolver;
use oat\tao\model\session\sessionFactory\SessionBuilder;
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
        $authAdapter = new Oauth2AuthAdapter($request);
        $authAdapter->setServiceLocator($this->getServiceLocator());
        $user = $authAdapter->authenticate();
        return new \common_session_RestSession($user);
    }

}