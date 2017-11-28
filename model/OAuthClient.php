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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoOauth\model;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use oat\oatbox\service\ConfigurableService;
use oat\taoOauth\model\exception\OauthException;
use oat\taoOauth\model\provider\OauthProvider;
use oat\taoOauth\model\provider\ProviderFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class OAuthConnector
 *
 * A client to handle an oauth connection, with possibility to request token.
 *
 */
class OAuthClient extends ConfigurableService implements ClientInterface
{
    /** Required access token grant */
    const GRANT_TYPE = 'client_credentials';

    /** Key to store token in cache */
    const OPTION_TOKEN_KEY = 'token_key';

    /** Storage of token */
    const OPTION_TOKEN_STORAGE = 'token_storage';

    /** Additional token request parameter */
    const OPTION_TOKEN_REQUEST_PARAMS = 'tokenParameters';

    /** @var AbstractProvider The provider to embed Oauth parameters */
    protected $provider;

    /**
     * Create and send an HTTP request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well.
     *
     * @param string              $method  HTTP method.
     * @param string|UriInterface $uri     URI object or string.
     * @param array               $options Request options to apply.
     * @param bool                $repeatIfUnauthorized To try to launch the request again (with new token)
     *
     * @return ResponseInterface
     * @throws OauthException
     */
    public function request($method, $uri, array $options = [], $repeatIfUnauthorized = true)
    {
        return $this->send($this->getAuthenticatedRequest($uri, $method, $options), [], $repeatIfUnauthorized);
    }

    /**
     * Send an HTTP request.
     *
     * @param RequestInterface $request Request to send
     * @param array            $options Request options to apply to the given
     *                                  request and to the transfer.
     * @param bool             $repeatIfUnauthorized To try to launch the request again (with new token)
     *
     * @return ResponseInterface
     * @throws OauthException
     */
    public function send(RequestInterface $request, array $options = [], $repeatIfUnauthorized = true)
    {
        $response = null;

        try {
            $response = $this->getResponse($request);
        } catch (ConnectException $e) {
            \common_Logger::i($e->getMessage());
            throw new OauthException('No response from the server, connection cannot be established.', 0, $e);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        } catch (IdentityProviderException $e) {
            throw new OauthException('The provider response contains errors, connection cannot be established.', 0, $e);
        } catch (\Exception $e) {
            throw new OauthException('Connection cannot be established.', 0, $e);
        }

        if ($response && $response->getStatusCode() === 401) {
            if ($repeatIfUnauthorized) {
                $this->requestAccessToken();
                $params = json_decode($request->getBody()->__toString(), true);
                if (!is_array($params)) {
                    throw new OauthException('Server has returned a response with a 401 code, Unable to resend requesy.');
                }
                $response = $this->request($request->getMethod(), $request->getUri(), $params, false);
            } else {
                throw new OauthException('Server has returned a response with a 401 code, connection cannot be established.');
            }
        }

        if ($response->getStatusCode() == 500) {
            throw new OauthException('A internal error has occurred during server request.');
        }

        return $response;
    }

    public function sendAsync(RequestInterface $request, array $options = [])
    {
        throw new \common_exception_NotImplemented(__METHOD__ . ' is not implemented.');
    }

    public function requestAsync($method, $uri, array $options = [])
    {
        throw new \common_exception_NotImplemented(__METHOD__ . ' is not implemented.');
    }

    public function getConfig($option = null)
    {
        throw new \common_exception_NotImplemented(__METHOD__ . ' is not implemented.');
    }

    /**
     * Create a request with the token to have an oauth2 call
     *
     * @param $url
     * @param string $method
     * @param array $options
     * @return RequestInterface
     */
    protected function getAuthenticatedRequest($url, $method = AbstractProvider::METHOD_GET, array $options = array())
    {
        return $this->getProvider()->getAuthenticatedRequest(
            $method,
            $url,
            $this->getAccessToken(),
            $options
        );
    }

    /**
     * After $this->getRequest(), you can have the associated response from the provider
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    protected function getResponse(RequestInterface $request)
    {
        return $this->getProvider()->getResponse($request, false);
    }

    /**
     * Request access token and put it to the storage.
     *
     * @param array $params
     * @return AccessToken
     */
    protected function requestAccessToken($params = [])
    {
        $params = $this->addTokenRequestParameters($params);
        $accessToken = $this->getProvider()->getAccessToken(self::GRANT_TYPE, $params);
        $this->setAccessToken($accessToken);

        return $accessToken;
    }

    /**
     * If request parameters are provided as $this->option, merge it to $params
     *
     * @param array $params
     * @return array
     */
    protected function addTokenRequestParameters($params = [])
    {
        if ($this->hasOption(self::OPTION_TOKEN_REQUEST_PARAMS)) {
            $options = $this->getOption(self::OPTION_TOKEN_REQUEST_PARAMS);
            if (is_array($options)) {
                $params = array_merge($params, $options);
            }
        }
        return $params;
    }

    /**
     * Get stored access token. If there is no token in the storage or token has expired then request new token.
     *
     * @return AccessToken access token instance
     */
    protected function getAccessToken()
    {
        /** @var AccessToken $token */
        $token = $this->getTokenStorage()->get($this->getTokenKey());
        if ($token === false || $token->hasExpired()) {
            $token = $this->requestAccessToken();
        }
        return $token;
    }

    /**
     * Store access token
     *
     * @param string $token
     * @return void
     */
    protected function setAccessToken($token)
    {
        $this->getTokenStorage()->set($this->getTokenKey(), $token);
    }

    /**
     * Get token storage
     *
     * @return \common_persistence_KeyValuePersistence
     * @throws \ConfigurationException
     */
    protected function getTokenStorage()
    {
        if (!$this->hasOption(self::OPTION_TOKEN_STORAGE)) {
            throw new \ConfigurationException(
                'An oauth connection requires the option "' . self::OPTION_TOKEN_STORAGE . '" to store the token'
            );
        }
        return \common_persistence_Persistence::getPersistence($this->getOption(self::OPTION_TOKEN_STORAGE));
    }

    /**
     * Get the token key to store his value into token storage.
     *
     * @return \common_persistence_Persistence
     * @throws \ConfigurationException
     */
    protected function getTokenKey()
    {
        if (!$this->hasOption(self::OPTION_TOKEN_KEY)) {
            throw new \ConfigurationException(
                'An oauth connection requires the option "' . self::OPTION_TOKEN_KEY . '" to store the token'
            );
        }
        return $this->getOption(self::OPTION_TOKEN_KEY);
    }

    /**
     * Get the provider to manage oauth2 connection.
     *
     * @return OauthProvider
     */
    protected function getProvider()
    {
        if (!$this->provider) {
            $this->provider = (new ProviderFactory($this->getOptions()))->build();
        }

        return $this->provider;
    }

}