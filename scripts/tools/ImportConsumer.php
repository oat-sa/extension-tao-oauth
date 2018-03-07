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
use oat\taoOauth\model\OAuthClient;
use oat\taoOauth\model\storage\ConsumerStorage;

class ImportConsumer extends ScriptAction
{
    use OntologyAwareTrait;

    protected $importedConsumer;

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
            ]
        ];
    }

    protected function provideDescription()
    {
        return 'Create an oauth client from credentials. It will be used during oauth2 token request and http request signature.';
    }

    protected function run()
    {
        $key = $this->getOption('id');
        $secret = $this->getOption('secret');
        $tokenUrl = $this->getOption('tokenUrl');

        $this->createConsumer($key, $secret, $tokenUrl);
        return \common_report_Report::createSuccess('Consumer successfully created');
    }

    protected function createConsumer($key, $secret, $tokenUrl)
    {
        return $this->importedConsumer = $this->getClass(ConsumerStorage::CONSUMER_CLASS)->createInstanceWithProperties(array(
            ConsumerStorage::CONSUMER_CLIENT_KEY => $key,
            ConsumerStorage::CONSUMER_CLIENT_SECRET => $secret,
            ConsumerStorage::CONSUMER_CALLBACK_URL => false,
            ConsumerStorage::CONSUMER_TOKEN => '',
            ConsumerStorage::CONSUMER_TOKEN_HASH => '',
            ConsumerStorage::CONSUMER_TOKEN_URL => $tokenUrl,
            ConsumerStorage::CONSUMER_TOKEN_TYPE => OAuthClient::DEFAULT_TOKEN_TYPE,
            ConsumerStorage::CONSUMER_TOKEN_GRANT_TYPE => OAuthClient::DEFAULT_GRANT_TYPE,
        ));
    }

}