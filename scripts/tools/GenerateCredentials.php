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

class GenerateCredentials extends AbstractAction
{
    use OntologyAwareTrait;

    public function __invoke($params)
    {
        $class = $this->getClass(Oauth2Service::CLASS_URI_OAUTH_CONSUMER);

        $key = 'superKey';
        $secret = 'superSecret';

        $consumers = $class->searchInstances(
            array(
                Oauth2Service::PROPERTY_OAUTH_KEY => $key,
                Oauth2Service::PROPERTY_OAUTH_SECRET => $secret,
            ),
            array('like' => false, 'recursive' => true)
        );

        foreach ($consumers as $consumer) {
            $consumer->delete();
        }

        $class->createInstanceWithProperties(array(
             Oauth2Service::PROPERTY_OAUTH_KEY => $key,
             Oauth2Service::PROPERTY_OAUTH_SECRET => $secret,
             Oauth2Service::PROPERTY_OAUTH_CALLBACK => false,
             Oauth2Service::PROPERTY_OAUTH_TOKEN => '',
             Oauth2Service::PROPERTY_OAUTH_TOKEN_HASH => '',
             Oauth2Service::PROPERTY_OAUTH_TOKEN_URL => _url('requestToken', 'TokenApi', 'taoOauth'),
        
             Oauth2Service::PROPERTY_OAUTH_TOKEN_TYPE => 'Bearer',
             Oauth2Service::PROPERTY_OAUTH_GRANT_TYPE => 'client_credentials',
        ));

        return \common_report_Report::createSuccess('Client generated');
    }

}