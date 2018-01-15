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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\taoOauth\controller;

use League\OAuth2\Client\Provider\AbstractProvider;
use oat\taoOauth\model\token\provider\TokenProviderFactory;
use oat\taoOauth\model\token\TokenService;

class TokenApi extends \tao_actions_RestController
{
    /** The credentials to identify client */
    const CLIENT_ID_PARAM = 'client_id';

    /** The credentials to authenticate client */
    const CLIENT_SECRET_PARAM = 'client_secret';

    /** see https://tools.ietf.org/html/rfc6749#section-1.3 */
    const GRANT_TYPE_PARAMETER = 'grant_type';

    public function requestToken()
    {
        try {
            $parameters = $this->getParameters();
            /** @var AbstractProvider $provider */
            $provider = (new TokenProviderFactory($parameters))->build();
//            $provider->getAccessToken()getAccessToken()

//            $clientId = $this->getRequestParameter(self::CLIENT_ID_PARAM);
//            $domain = $this->getRequestParameter(self::DOMAIN_PARAM);

            $token = $this->getTokenService()->generateToken($provider);
            $this->returnJson($token);
        } catch (\Exception $e) {
            $this->returnFailure($e);
        }
    }

    protected function getParameters()
    {
        if (!$this->hasRequestParameter(self::CLIENT_ID_PARAM)) {
            throw new \common_exception_MissingParameter(self::CLIENT_ID_PARAM, __CLASS__);
        }

        if (!$this->hasRequestParameter(self::CLIENT_SECRET_PARAM)) {
            throw new \common_exception_MissingParameter(self::CLIENT_SECRET_PARAM, __CLASS__);
        }

        if (!$this->hasRequestParameter(self::GRANT_TYPE_PARAMETER)) {
            $grantType = $this->getDefaultGrantType();
        } else {
            $grantType = $this->getRequestParameter(self::GRANT_TYPE_PARAMETER);
        }

        return [
            self::CLIENT_ID_PARAM => $this->getRequestParameter(self::CLIENT_ID_PARAM),
            self::CLIENT_SECRET_PARAM => $this->getRequestParameter(self::CLIENT_SECRET_PARAM),
            self::GRANT_TYPE_PARAMETER => $grantType
        ];
    }

    protected function getDefaultGrantType()
    {
        return 'client_credentials';
    }

    protected function getTokenService()
    {
        return $this->getServiceManager()->get(TokenService::SERVICE_ID);
    }

}