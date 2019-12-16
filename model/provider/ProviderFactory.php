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
 */

namespace oat\taoOauth\model\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use oat\oatbox\Configurable;
use oat\taoOauth\model\exception\OauthException;
use oat\taoOauth\model\OAuthClient;

/**
 * Class ProviderFactory
 *
 * The factory to create the bridge that provide the connection to connector.
 *
 * @package oat\taoOauth\model\provider
 */
class ProviderFactory extends Configurable implements Provider
{

    /**
     * Create an instance of provider for Oauth connection.
     * A provider is required to follow Guzzle architecture, and usefull to manage all parameters required for an oauth connection.
     *
     * @return mixed
     * @throws OauthException
     */
    public function build()
    {
        try {
            $providerClass = $this->getProviderClass();
            $this->logInfo('Using provider class: "'. $providerClass .'"');
            if (is_a($providerClass, AbstractProvider::class, true)) {
                return new $providerClass($this->getFormattedOptions());
            }
            throw new \common_exception_InconsistentData('A provider class name has to extend AbstractProvider');
        } catch (\Exception $e) {
            throw new OauthException('Cannot build the Oauth provider: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get the provider class
     *
     * @return string
     */
    protected function getProviderClass()
    {
        if ($this->hasOption(OAuthClient::OPTION_OAUTH_PROVIDER)) {
            return $this->getOption(OAuthClient::OPTION_OAUTH_PROVIDER);
        }

        return OauthProvider::class;
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
            'clientId' => $this->getClientId(),
            'clientSecret' => $this->getConsumerSecret(),
            'urlAccessToken' => $this->getTokenUrl(),
            'urlAuthorize' => $this->getAuthorizeUrl(),
            'urlResourceOwnerDetails' => $this->getResourceOwnerDetailsUrl(),
        );

        return array_merge($this->getCleanOptions(), $defaultParams, $this->getHttpClientOptions());
    }

    /**
     * Validate the options against required options.
     *
     * @throws \InvalidArgumentException
     */
    protected function validateOptions()
    {
        $missing = $this->getCleanOptions();

        if (!empty($missing)) {
            throw new \InvalidArgumentException(
                'Required options not defined: ' . implode(', ', array_keys($missing))
            );
        }
    }

    /**
     * Extract the option not required by oauth connection.
     *
     * @return array
     */
    protected function getCleanOptions()
    {
        return array_diff_key(array_flip($this->getRequiredOptions()), $this->getOptions());
    }

    /**
     * Returns all options that an oauth connections are required.
     *
     * @return array
     */
    protected function getRequiredOptions()
    {
        return [
            self::CLIENT_ID,
            self::CLIENT_SECRET,
            self::AUTHORIZE_URL,
            self::TOKEN_URL,
            self::RESOURCE_OWNER_DETAILS_URL,
        ];
    }

    /**
     * Get the http client options
     *
     * @return array
     */
    protected function getHttpClientOptions()
    {
        $httpClientOptions = $this->getOption(self::HTTP_CLIENT_OPTIONS);
        return is_array($httpClientOptions) ? $httpClientOptions : array();

    }

    /**
     * Get the resource owner details url
     *
     * @return bool|mixed
     */
    protected function getResourceOwnerDetailsUrl()
    {
        $resourceOwnerDetailsUrl = $this->getOption(self::RESOURCE_OWNER_DETAILS_URL);
        return !is_null($resourceOwnerDetailsUrl) ? $resourceOwnerDetailsUrl : false;
    }

    /**
     * Get client id option value
     *
     * @return string
     */
    protected function getClientId()
    {
        return $this->getOption(self::CLIENT_ID);
    }

    /**
     * Get consumer secret option value
     *
     * @return string
     */
    protected function getConsumerSecret()
    {
        return $this->getOption(self::CLIENT_SECRET);
    }

    /**
     * Get url to request token
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return $this->getOption(self::TOKEN_URL);
    }

    /**
     * Get authorize url
     *
     * @return string
     */
    protected function getAuthorizeUrl()
    {
        return $this->getOption(self::AUTHORIZE_URL);
    }

    /**
     * Get grant type
     *
     * @return string
     */
    protected function getGrantType()
    {
        return $this->getOption(self::GRANT_TYPE);
    }
}