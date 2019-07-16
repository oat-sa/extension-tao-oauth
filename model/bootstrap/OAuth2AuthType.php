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
use oat\taoOauth\model\storage\OauthCredentials;
use Prophecy\Exception\Doubler\MethodNotFoundException;
use Psr\Http\Message\RequestInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class OAuth2AuthType
 * @package oat\taoOauth\model\bootstrap
 */
class OAuth2AuthType extends AbstractAuthType implements ServiceLocatorAwareInterface
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
        $credentials = $this->getCredentials();
        $data[Provider::CLIENT_ID] = $credentials[Provider::CLIENT_ID];
        $data[Provider::CLIENT_SECRET] = $credentials[Provider::CLIENT_SECRET];
        $data[Provider::TOKEN_URL] = $credentials[Provider::TOKEN_URL];

        if (!empty($credentials[Provider::TOKEN_TYPE])) {
            $data[Provider::TOKEN_TYPE] = $credentials[Provider::TOKEN_TYPE];
        }

        if (!empty($credentials[Provider::GRANT_TYPE])) {
            $data[Provider::GRANT_TYPE] = $credentials[Provider::GRANT_TYPE];
        }

        $data['body'] = $request->getBody();
        $data['headers'] = $request->getHeaders();
        if (!empty($clientOptions)) {
            $data[Provider::HTTP_CLIENT_OPTIONS] = $clientOptions;
        }
        /** @var OAuthClient $client */
        $client = $this->getOauth2Service()->getClient($data);
        return $client->request($request->getMethod(), $request->getUri(), $data);
    }

    /**
     * Get the OauthCredentials
     * @param array $parameters
     * @return \core_kernel_classes_Class|\oat\tao\model\auth\AbstractCredentials|OauthCredentials
     * @throws \common_exception_ValidationFailed
     */
    public function getAuthClass($parameters = [])
    {
        return new OauthCredentials($parameters);
    }

    /**
     * Get the properties used to load oauh2 authentication
     *
     * @return array
     */
    public function getAuthProperties()
    {
        return array_values($this->getCredentials());
    }

    /**
     * Get the template associated to oauth2 authentication
     *
     * @return string
     * @throws \common_exception_InvalidArgumentType
     */
    public function getTemplate()
    {
        $data = $this->getCredentials();
        return Template::inc('oauth/oAuthForm.tpl', 'taoOauth', $data);
    }

    /**
     * @return Oauth2Service
     */
    protected function getOauth2Service()
    {
        return $this->getServiceLocator()->get(Oauth2Service::SERVICE_ID);
    }

    /**
     * @return \core_kernel_classes_Resource|void
     */
    public function getInstance()
    {
        throw new MethodNotFoundException('getInstance method was deprecated', __CLASS__, __METHOD__);
    }

    /**
     * @param \core_kernel_classes_Resource|null $instance
     */
    public function setInstance(\core_kernel_classes_Resource $instance = null)
    {
        throw new MethodNotFoundException('setInstance method was deprecated', __CLASS__, __METHOD__);
    }
}
