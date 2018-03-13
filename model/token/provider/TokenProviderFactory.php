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
 *
 */

namespace oat\taoOauth\model\token\provider;

use oat\taoOauth\model\provider\ProviderFactory;

class TokenProviderFactory extends ProviderFactory
{
    /**
     * Create an instance of provider for Token generation request
     *
     * @return TokenProvider
     */
    public function build()
    {
        $providerClass = $this->getProviderClass();
        return new $providerClass($this->getFormattedOptions());
    }

    /**
     * Get the provider class
     *
     * @return string
     */
    protected function getProviderClass()
    {
        return TokenProvider::class;
    }

    /**
     * Returns all options that token generation requires.
     *
     * @return array
     */
    protected function getRequiredOptions()
    {
        return [
            self::CLIENT_ID,
            self::CLIENT_SECRET,
            self::GRANT_TYPE,
        ];
    }

    /**
     * Validate and retrieve options to correctly format provider parameters
     *
     * @return array
     */
    protected function getFormattedOptions()
    {
        $this->validateOptions();
        $defaultParams = array(
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getConsumerSecret(),
            'grant_type' => $this->getGrantType(),
        );
        return array_merge($this->getCleanOptions(), $defaultParams);
    }

}