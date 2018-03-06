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

namespace oat\taoOauth\scripts\tools;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\AbstractAction;
use oat\taoOauth\model\Oauth2Service;
use oat\taoOauth\model\OAuthClient;

class GenerateCredentials extends AbstractAction
{
    use OntologyAwareTrait;

    public function __invoke($params)
    {
        $key = $this->getClientKey();
        $secret = $this->getClientSecret();
        $tokenUrl = $this->getTokenUrl();

        $this->deleteConsumer($key, $secret);
        $this->createConsumer($key, $secret, $tokenUrl);

        return \common_report_Report::createSuccess(
            'Client generated with credentials : ' . PHP_EOL .
            ' - client key  : ' . $key . PHP_EOL .
            ' - client secret  : ' . $secret . PHP_EOL .
            ' - token url  : ' . $tokenUrl . PHP_EOL
        );
    }

    protected function createConsumer($key, $secret, $tokenUrl)
    {
        $this->getClass(Oauth2Service::CLASS_URI_OAUTH_CONSUMER)->createInstanceWithProperties(array(
            Oauth2Service::PROPERTY_OAUTH_KEY => $key,
            Oauth2Service::PROPERTY_OAUTH_SECRET => $secret,
            Oauth2Service::PROPERTY_OAUTH_CALLBACK => false,
            Oauth2Service::PROPERTY_OAUTH_TOKEN => '',
            Oauth2Service::PROPERTY_OAUTH_TOKEN_HASH => '',
            Oauth2Service::PROPERTY_OAUTH_TOKEN_URL => $tokenUrl,

            Oauth2Service::PROPERTY_OAUTH_TOKEN_TYPE => OAuthClient::DEFAULT_TOKEN_TYPE,
            Oauth2Service::PROPERTY_OAUTH_GRANT_TYPE => OAuthClient::DEFAULT_GRANT_TYPE,
        ));
    }

    protected function deleteConsumer($key, $secret)
    {
        $consumers = $this->getClass(Oauth2Service::CLASS_URI_OAUTH_CONSUMER)->searchInstances(
            array(
                Oauth2Service::PROPERTY_OAUTH_KEY => $key,
                Oauth2Service::PROPERTY_OAUTH_SECRET => $secret,
            ),
            array('like' => false, 'recursive' => true)
        );

        /** @var \core_kernel_classes_Resource $consumer */
        foreach ($consumers as $consumer) {
            $consumer->delete();
        }
    }

    protected function getClientKey()
    {
        return 'superKey';
    }

    protected function getClientSecret()
    {
        return 'superSecret';
    }

    protected function getTokenUrl()
    {
        return _url('requestToken', 'TokenApi', 'taoOauth');
    }

}