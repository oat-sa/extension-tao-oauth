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
use oat\taoOauth\model\provider\Provider;
use oat\taoOauth\model\storage\ConsumerStorage;
use Psr\Http\Message\RequestInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * @deprecated Please use OAuth2AuthType
 * Class OAuth2Type
 * @package oat\taoOauth\model\bootstrap
 */
class OAuth2Type extends AbstractAuthType implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    /**
     * Call a remote environment through Oauth http client
     *
     * Load the consumer credentials, create the authenticated client and send request
     *
     * @param RequestInterface $request
     * @param array $clientOptions Http client options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \ConfigurationException
     * @throws \common_Exception
     * @throws \common_exception_InvalidArgumentType
     * @throws \oat\taoOauth\model\exception\OauthException
     */
    public function call(RequestInterface $request, array $clientOptions = [])
    {
        $credentials = $this->loadCredentials();
        $data[Provider::CLIENT_ID] = $credentials[ConsumerStorage::CONSUMER_CLIENT_KEY];
        $data[Provider::CLIENT_SECRET] = $credentials[ConsumerStorage::CONSUMER_CLIENT_SECRET];
        $data[Provider::TOKEN_URL] = $credentials[ConsumerStorage::CONSUMER_TOKEN_URL];
        $data['body'] = $request->getBody();
        $data['headers'] = $request->getHeaders();
        if (!empty($clientOptions)) {
            $data[Provider::HTTP_CLIENT_OPTIONS] = $clientOptions;
        }
        if (!empty($clientOptions['curl'])) {
            $data['curl'] = $clientOptions['curl'];
        }
        /** @var OAuthClient $client */
        $client = $this->getOauth2Service()->getClient($data);
        return $client->request($request->getMethod(), $request->getUri(), $data);
    }
    /**
     * Get the root class
     *
     * @return \core_kernel_classes_Class
     */
    public function getAuthClass($parameters = [])
    {
        return $this->getClass(ConsumerStorage::CONSUMER_CLASS);
    }
    /**
     * Get the properties used to load oauh2 authentication
     *
     * @return array
     */
    public function getAuthProperties()
    {
        return [
            $this->getProperty(ConsumerStorage::CONSUMER_CLIENT_KEY),
            $this->getProperty(ConsumerStorage::CONSUMER_CLIENT_SECRET),
            $this->getProperty(ConsumerStorage::CONSUMER_TOKEN_URL),
            $this->getProperty(ConsumerStorage::CONSUMER_TOKEN_TYPE),
            $this->getProperty(ConsumerStorage::CONSUMER_TOKEN_GRANT_TYPE),
        ];
    }
    /**
     * Get the template associated to oauth2 authentication
     *
     * @return string
     * @throws \common_exception_InvalidArgumentType
     */
    public function getTemplate()
    {
        $data = $this->loadCredentials();
        return Template::inc('oauth/oAuthForm.tpl', 'taoOauth', $data);
    }
    /**
     * Load Oauth credentials from current consumer instance
     *
     * @return array
     * @throws \common_exception_InvalidArgumentType
     */
    protected function loadCredentials()
    {
        $instance = $this->getInstance();
        if ($instance && $instance->exists()) {
            $props = $instance->getPropertiesValues([
                $this->getProperty(ConsumerStorage::CONSUMER_CLIENT_KEY),
                $this->getProperty(ConsumerStorage::CONSUMER_CLIENT_SECRET),
                $this->getProperty(ConsumerStorage::CONSUMER_TOKEN_URL),
            ]);
            $data = [
                ConsumerStorage::CONSUMER_CLIENT_KEY => (string)current($props[ConsumerStorage::CONSUMER_CLIENT_KEY]),
                ConsumerStorage::CONSUMER_CLIENT_SECRET => (string)current($props[ConsumerStorage::CONSUMER_CLIENT_SECRET]),
                ConsumerStorage::CONSUMER_TOKEN_URL => (string)current($props[ConsumerStorage::CONSUMER_TOKEN_URL]),
            ];
        } else {
            $data = [
                ConsumerStorage::CONSUMER_CLIENT_KEY => '',
                ConsumerStorage::CONSUMER_CLIENT_SECRET => '',
                ConsumerStorage::CONSUMER_TOKEN_URL => '',
            ];
        }
        return $data;
    }
    /**
     * @return Oauth2Service
     */
    protected function getOauth2Service()
    {
        return $this->getServiceLocator()->get(Oauth2Service::SERVICE_ID);
    }
}
