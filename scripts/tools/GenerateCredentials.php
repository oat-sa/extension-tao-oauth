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

namespace oat\taoOauth\scripts\tools;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\script\ScriptAction;
use oat\taoOauth\model\Oauth2Service;

class GenerateCredentials extends ScriptAction
{
    use OntologyAwareTrait;

    protected $createdConsumer;

    protected $key;

    protected $secret;

    protected $tokenUrl;

    public function run()
    {
        $role = $this->getOption('role');
        $this->key = $this->getOauthService()->generateClientKey();
        $this->secret = $this->getOauthService()->generateClientSecret($this->key);
        $this->tokenUrl = $this->getOauthService()->getDefaultTokenUrl();

        /** @var Oauth2Service $service */
        $service = $this->getServiceLocator()->get(Oauth2Service::SERVICE_ID);
        $this->createdConsumer = $service->spawnConsumer($this->key, $this->secret, $this->tokenUrl, $role);

        return \common_report_Report::createSuccess(
            'Client generated with credentials : ' . PHP_EOL .
            ' - client key  : ' . $this->key . PHP_EOL .
            ' - client secret  : ' . $this->secret . PHP_EOL .
            ' - token url  : ' . $this->tokenUrl . PHP_EOL
        );
    }

    /**
     * @return Oauth2Service
     */
    protected function getOauthService()
    {
        return $this->getServiceLocator()->get(Oauth2Service::SERVICE_ID);
    }


    /**
     * Describe args to run this script
     *
     * @return array
     */
    protected function provideOptions()
    {
        return [
            'role' => [
                'prefix' => 'r',
                'longPrefix' => 'role',
                'required' => false,
                'description' => 'User role',
            ]
        ];
    }

    /**
     * Provide a script description
     *
     * @return string
     */
    protected function provideDescription()
    {
        return 'Generate Oauth credentials to authenticate against the platform.';
    }

    /**
     * Allow to display help
     *
     * @return array
     */
    protected function provideUsage()
    {
        return [
            'prefix' => 'h',
            'longPrefix' => 'help',
            'description' => 'description',
        ];
    }

}