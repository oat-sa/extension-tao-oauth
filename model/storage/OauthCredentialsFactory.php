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
    const SERVICE_ID = 'taoOauth/oauthCredentialsFactory';
    const OPTION_GRANT_MAP = 'grantMap';

    /**
     * @param array $parameters
     * @return OauthCredentials|ClientCredentialsType|PasswordType|AuthorizationCodeType
     * @throws common_exception_ValidationFailed
     */
    public function getCredentialTypeByCredentials($parameters = [])
    {
        $grantType = !empty($parameters[Provider::GRANT_TYPE]) ? $parameters[Provider::GRANT_TYPE] : OAuthClient::DEFAULT_GRANT_TYPE;
        $grantMap = $this->getOption(self::OPTION_GRANT_MAP);

        if ($grantMap[$grantType] === ClientCredentialsType::class) {
            return new ClientCredentialsType($parameters);
        }

        if ($grantMap[$grantType] === PasswordType::class) {
            return new PasswordType($parameters);
        }

        if ($grantMap[$grantType] === AuthorizationCodeType::class) {
            return new AuthorizationCodeType($parameters);
        }

        throw new common_exception_ValidationFailed($parameters[Provider::GRANT_TYPE]);
    }

}
