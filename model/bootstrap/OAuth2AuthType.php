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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\taoOauth\model\bootstrap;

use oat\tao\helpers\Template;
use oat\tao\model\auth\AbstractAuthType;
use oat\taoOauth\model\Oauth2Service;
use oat\taoOauth\model\OAuthClient;
use oat\taoOauth\model\provider\Provider;
use oat\taoOauth\model\storage\grant\OauthCredentials;
use oat\taoOauth\model\storage\OauthCredentialsFactory;
use Prophecy\Exception\Doubler\MethodNotFoundException;
use Psr\Http\Message\RequestInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Psr\Http\Message\ResponseInterface;
use oat\tao\model\auth\AbstractCredentials;
use core_kernel_classes_Resource;
use oat\taoOauth\model\exception\OauthException;
use ConfigurationException;
use common_Exception;
use core_kernel_classes_Class;
use common_exception_ValidationFailed;

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
     * @return ResponseInterface
     * @throws ConfigurationException
     * @throws common_Exception
     * @throws OauthException
     */
    public function call(RequestInterface $request, array $clientOptions = [])
    {
        $data = $this->getCredentials();

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
     * Get the OauthCredentials
     * @param array $parameters
     * @return core_kernel_classes_Class|OauthCredentials
     * @throws common_exception_ValidationFailed
     */
    public function getAuthClass($parameters = [])
    {
        $oauthCredentialsFactory = $this->getOauthCredentialsFactory();
        return $oauthCredentialsFactory->getCredentialTypeByCredentials($parameters);
    }

    /**
     * Get the properties used to load Oauth2 authentication
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
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(Oauth2Service::SERVICE_ID);
    }

    /**
     * @return OauthCredentialsFactory
     */
    private function getOauthCredentialsFactory()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(OauthCredentialsFactory::class);
    }

    /**
     * @return core_kernel_classes_Resource|void
     */
    public function getInstance()
    {
        throw new MethodNotFoundException('getInstance method was deprecated', __CLASS__, __METHOD__);
    }

    /**
     * @param core_kernel_classes_Resource|null $instance
     */
    public function setInstance(core_kernel_classes_Resource $instance = null)
    {
        throw new MethodNotFoundException('setInstance method was deprecated', __CLASS__, __METHOD__);
    }
}
