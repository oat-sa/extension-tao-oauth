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

class ImportConsumer extends ScriptAction
{
    use OntologyAwareTrait;

    protected $createdConsumer;

    /**
     * Run the script
     *
     * Create a consumer based on key, secret and token url
     *
     * @return \common_report_Report
     */
    protected function run()
    {
        $key = $this->getOption('key');
        $secret = $this->getOption('secret');
        $tokenUrl = $this->getOption('tokenUrl');
        $role = $this->getOption('role');

        /** @var Oauth2Service $oauth2Service */
        $oauth2Service = $this->getServiceLocator()->get(Oauth2Service::SERVICE_ID);
        $this->createdConsumer = $oauth2Service->spawnConsumer($key, $secret, $tokenUrl, $role);

        return \common_report_Report::createSuccess('Consumer successfully created');
    }

    /**
     * Describe args to run this script
     *
     * It requires key, secret and token-url to be ran
     *
     * @return array
     */
    protected function provideOptions()
    {
        return [
            'key' => [
                'prefix' => 'k',
                'longPrefix' => 'key',
                'required' => true,
                'description' => 'Client id of http consumer',
            ],
            'secret' => [
                'prefix' => 's',
                'longPrefix' => 'secret',
                'required' => true,
                'description' => 'Client secret of http consumer',
            ],
            'tokenUrl' => [
                'prefix' => 'tu',
                'longPrefix' => 'token-url',
                'required' => true,
                'description' => 'The url to request a new oauth token',
            ],
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
        return 'Create an oauth client from credentials. It will be used during oauth2 token request and http request signature.';
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