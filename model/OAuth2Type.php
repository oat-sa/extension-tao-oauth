<?php
/**
 * Created by PhpStorm.
 * User: siwane
 * Date: 09/02/18
 * Time: 11:37
 */

namespace oat\taoOauth\model;

use oat\oatbox\service\ServiceManager;
use oat\tao\helpers\Template;
use oat\tao\model\auth\AbstractAuthType;
use Psr\Http\Message\RequestInterface;

class OAuth2Type extends AbstractAuthType
{
    public function call(RequestInterface $request)
    {
        $credentials = $this->loadCredentials();

        $data['client_id'] = $credentials['http://www.taotesting.com/ontologies/taooauth.rdf#ClientId'];
        $data['client_secret'] = $credentials['http://www.taotesting.com/ontologies/taooauth.rdf#ClientSecret'];
        $data['token_url'] = $credentials['http://www.taotesting.com/ontologies/taooauth.rdf#TokenUrl'];

        $data['client_id'] = 'id';
        $data['client_secret'] = 'secret';
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
            $this->getProperty('http://www.taotesting.com/ontologies/taooauth.rdf#ClientId'),
            $this->getProperty('http://www.taotesting.com/ontologies/taooauth.rdf#ClientSecret'),
            $this->getProperty('http://www.taotesting.com/ontologies/taooauth.rdf#TokenUrl'),
            $this->getProperty('http://www.taotesting.com/ontologies/taooauth.rdf#TokenType'),
            $this->getProperty('http://www.taotesting.com/ontologies/taooauth.rdf#GrantType'),
        ];
    }

    public function getTemplate()
    {
        $data = $this->loadCredentials();
        return Template::inc('oauth/oAuthForm.tpl', 'taoOauth', $data);
    }

    public function getCredentials()
    {

    }

    protected function loadCredentials() {
        $instance = $this->getInstance();
        if ($instance && $instance->exists()) {

            $props = $instance->getPropertiesValues([
                $this->getProperty('http://www.taotesting.com/ontologies/taooauth.rdf#ClientId'),
                $this->getProperty('http://www.taotesting.com/ontologies/taooauth.rdf#ClientSecret'),
                $this->getProperty('http://www.taotesting.com/ontologies/taooauth.rdf#TokenUrl'),
            ]);

            $data = [
                'http://www.taotesting.com/ontologies/taooauth.rdf#ClientId' => (string)current($props['http://www.taotesting.com/ontologies/taooauth.rdf#ClientId']),
                'http://www.taotesting.com/ontologies/taooauth.rdf#ClientSecret' => (string)current($props['http://www.taotesting.com/ontologies/taooauth.rdf#ClientSecret']),
                'http://www.taotesting.com/ontologies/taooauth.rdf#TokenUrl' => (string)current($props['http://www.taotesting.com/ontologies/taooauth.rdf#TokenUrl']),
            ];
        } else {
            $data = [
                'http://www.taotesting.com/ontologies/taooauth.rdf#ClientId' => '',
                'http://www.taotesting.com/ontologies/taooauth.rdf#ClientSecret' => '',
                'http://www.taotesting.com/ontologies/taooauth.rdf#TokenUrl' => '',
            ];
        }

        return $data;
    }

    protected function getOauth2Service()
    {
        $service = new Oauth2Service();
        $service->setServiceLocator(ServiceManager::getServiceManager());
        return $service;
    }

}