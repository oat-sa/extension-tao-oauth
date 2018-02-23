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

use oat\oatbox\user\LoginFailedException;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class Oauth2AuthAdapter implements \common_user_auth_Adapter, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /** @var \common_http_Request */
    private $request;

    /**
     * Creates an Authentication adapter from an OAuth Request
     *
     * @param \common_http_Request $request
     */
    public function __construct(\common_http_Request $request)
    {
        $this->request = $request;
    }

    /**
     * (non-PHPdoc)
     * @see common_user_auth_Adapter::authenticate()
     */
    public function authenticate()
    {
        $service = new Oauth2Service();

        try {
            return $service
                ->validate($this->request)
                ->getConsumer();
        } catch (\common_http_InvalidSignatureException $e) {
//            \common_Logger::i($e->getMessage());
            throw new LoginFailedException([$e->getMessage()]);
        }
    }

}