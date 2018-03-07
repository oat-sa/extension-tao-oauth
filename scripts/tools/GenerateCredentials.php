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
use oat\oatbox\extension\AbstractAction;
use oat\taoOauth\model\Oauth2Service;

class GenerateCredentials extends AbstractAction
{
    use OntologyAwareTrait;

    protected $createdConsumer;

    public function __invoke($params)
    {
        $key = $this->getClientKey();
        $secret = $this->getClientSecret();
        $tokenUrl = $this->getTokenUrl();

        /** @var Oauth2Service $service */
        $service = $this->getServiceLocator()->get(Oauth2Service::SERVICE_ID);
        $this->createdConsumer = $service->spawnConsumer($key, $secret, $tokenUrl);

        return \common_report_Report::createSuccess(
            'Client generated with credentials : ' . PHP_EOL .
            ' - client key  : ' . $key . PHP_EOL .
            ' - client secret  : ' . $secret . PHP_EOL .
            ' - token url  : ' . $tokenUrl . PHP_EOL
        );
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