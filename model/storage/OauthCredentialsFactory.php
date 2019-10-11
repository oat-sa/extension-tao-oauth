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
 * Copyright (c) 2019  (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoOauth\model\storage;

use oat\oatbox\service\ConfigurableService;
use oat\taoOauth\model\OAuthClient;
use oat\taoOauth\model\provider\Provider;
use oat\taoOauth\model\storage\grant\AuthorizationCodeType;
use oat\taoOauth\model\storage\grant\ClientCredentialsType;
use oat\taoOauth\model\storage\grant\OauthCredentials;
use oat\taoOauth\model\storage\grant\PasswordType;
use common_exception_ValidationFailed;

/**
 * Class OauthCredentials
 * @package oat\taoOauth\model\storage
 */
class OauthCredentialsFactory extends ConfigurableService
{
    /**
     * @param array $parameters
     * @return OauthCredentials
     * @throws common_exception_ValidationFailed
     */
    public function getCredentialTypeByCredentials(array $parameters = [])
    {
        $grantType = !empty($parameters[Provider::GRANT_TYPE]) ? $parameters[Provider::GRANT_TYPE] : OAuthClient::DEFAULT_GRANT_TYPE;

        if ($grantType === PasswordType::NAME) {
            return new PasswordType($parameters);
        }
        if ($grantType === ClientCredentialsType::NAME) {
            return new ClientCredentialsType($parameters);
        }
        if ($grantType === AuthorizationCodeType::NAME) {
            return new AuthorizationCodeType($parameters);
        }

        throw new common_exception_ValidationFailed(Provider::GRANT_TYPE);
    }

}
