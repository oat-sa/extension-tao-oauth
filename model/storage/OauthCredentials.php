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

use oat\tao\model\auth\AbstractCredentials;
use oat\taoOauth\model\OAuthClient;
use oat\taoOauth\model\provider\Provider;
use common_exception_ValidationFailed;

/**
 * Class OauthCredentials
 * @package oat\taoOauth\model\storage
 */
class OauthCredentials extends AbstractCredentials
{

    /**
     * @return array
     */
    public function getProperties()
    {
        $grantType = !empty($this->properties[Provider::GRANT_TYPE]) ? $this->properties[Provider::GRANT_TYPE] : OAuthClient::DEFAULT_GRANT_TYPE;
        return $this->getCredentialsByGrantType($grantType);
    }

    /**
     * Generate list of credentials for specific grant type
     *
     * @param $grantType
     * @return array
     */
    private function getCredentialsByGrantType($grantType)
    {
        // Default credentials for any grant types
        $credentials = [
            Provider::GRANT_TYPE => $grantType,
            Provider::TOKEN_URL => $this->properties[Provider::TOKEN_URL]
        ];

        if (!empty($this->properties[Provider::TOKEN_TYPE]) ? $this->properties[Provider::TOKEN_TYPE] : '') {
            $credentials[Provider::TOKEN_TYPE] = $this->properties[Provider::TOKEN_TYPE];
        }

        // Generate a list of credentials for a specific grant type
        switch ($grantType) {
            case OAuthClient::PASSWORD_GRANT_TYPE:
                $credentials = array_merge($credentials, [
                    Provider::CLIENT_ID => $this->properties[Provider::CLIENT_ID],
                    Provider::CLIENT_SECRET => $this->properties[Provider::CLIENT_SECRET],
                    Provider::PASSWORD => $this->properties[Provider::PASSWORD],
                    Provider::USERNAME => $this->properties[Provider::USERNAME]
                ]);
                break;
            case OAuthClient::DEFAULT_GRANT_TYPE:
                $credentials = array_merge($credentials, [
                    Provider::CLIENT_ID => $this->properties[Provider::CLIENT_ID],
                    Provider::CLIENT_SECRET => $this->properties[Provider::CLIENT_SECRET]
                ]);
                break;
            case OAuthClient::AUTHORIZATION_CODE_GRANT_TYPE:
                $credentials = array_merge($credentials, [
                    Provider::CODE => $this->properties[Provider::CODE]
                ]);
                break;

        }
        return $credentials;
    }

    /**
     * Validate credentials based on a grant type
     *
     * @param $properties
     * @throws common_exception_ValidationFailed
     */
    protected function validate($properties)
    {
        $grantType = !empty($properties[Provider::GRANT_TYPE]) ? $properties[Provider::GRANT_TYPE] : OAuthClient::DEFAULT_GRANT_TYPE;
        $validatedProperties = array_keys($this->getCredentialsByGrantType($grantType));
        foreach ($validatedProperties as $validatedProperty) {
            if (!in_array($validatedProperty, array_keys($properties), false)) {
                throw new \common_exception_ValidationFailed($validatedProperty);
            }
        }
    }

}
