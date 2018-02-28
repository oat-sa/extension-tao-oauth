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

namespace oat\taoOauth\model\bootstrap;

use oat\tao\helpers\Template;
use oat\tao\model\auth\AbstractAuthType;
use oat\taoOauth\model\Oauth2Service;
use oat\taoOauth\model\OAuthClient;
use oat\taoOauth\model\storage\ConsumerStorage;
use Psr\Http\Message\RequestInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class OAuth2Type extends AbstractAuthType implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function call(RequestInterface $request)
    {
        $credentials = $this->loadCredentials();

        $data['client_id'] = $credentials['http://www.taotesting.com/ontologies/taooauth.rdf#ClientId'];
        $data['client_secret'] = $credentials['http://www.taotesting.com/ontologies/taooauth.rdf#ClientSecret'];
        $data['token_url'] = $credentials['http://www.taotesting.com/ontologies/taooauth.rdf#TokenUrl'];

        $data['client_id'] = 'superKey';
        $data['client_secret'] = 'superSecret';
        $data['token_url'] = 'http://package-depp.dev/taoOauth/TokenApi/requestToken';
        $data['token_key'] = md5('token_' . $data['client_secret']);
        $data['authorize_url'] = false;
        $data['resource_owner_details_url'] = false;

        /** @var OAuthClient $client */
        $client = $this->getOauth2Service()->getClient($data);
        $response = $client->send($request, $data);
        return $response;
    }

    public function getAuthClass()
    {
        return $this->getClass('http://www.taotesting.com/ontologies/taooauth.rdf#Oauth-consumer');
    }

    public function getAuthProperties()
    {
        return [
            $this->getProperty(ConsumerStorage::CONSUMER_CLIENT_ID),
            $this->getProperty(ConsumerStorage::CONSUMER_CLIENT_SECRET),
            $this->getProperty(ConsumerStorage::CONSUMER_TOKEN_URL),
            $this->getProperty(ConsumerStorage::CONSUMER_TOKEN_TYPE),
            $this->getProperty(ConsumerStorage::CONSUMER_TOKEN_GRANT_TYPE),
        ];
    }

    public function getTemplate()
    {
        $data = $this->loadCredentials();
        return Template::inc('oauth/oAuthForm.tpl', 'taoOauth', $data);
    }

    protected function loadCredentials() {
        $instance = $this->getInstance();
        if ($instance && $instance->exists()) {

            $props = $instance->getPropertiesValues([
                $this->getProperty(ConsumerStorage::CONSUMER_CLIENT_ID),
                $this->getProperty(ConsumerStorage::CONSUMER_CLIENT_SECRET),
                $this->getProperty(ConsumerStorage::CONSUMER_TOKEN_URL),
            ]);

            $data = [
                ConsumerStorage::CONSUMER_CLIENT_ID => (string)current($props[ConsumerStorage::CONSUMER_CLIENT_ID]),
                ConsumerStorage::CONSUMER_CLIENT_SECRET => (string)current($props[ConsumerStorage::CONSUMER_CLIENT_SECRET]),
                ConsumerStorage::CONSUMER_TOKEN_URL => (string)current($props[ConsumerStorage::CONSUMER_TOKEN_URL]),
            ];
        } else {
            $data = [
                ConsumerStorage::CONSUMER_CLIENT_ID => '',
                ConsumerStorage::CONSUMER_CLIENT_SECRET => '',
                ConsumerStorage::CONSUMER_TOKEN_URL => '',
            ];
        }

        return $data;
    }

    protected function getOauth2Service()
    {
        return $this->getServiceLocator()->get(Oauth2Service::SERVICE_ID);
    }

}