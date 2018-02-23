<?php

namespace oat\taoOauth\model;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\oauth\DataStore;

class Oauth2Service extends ConfigurableService
{
    const CLASS_URI_OAUTH_CONSUMER = DataStore::CLASS_URI_OAUTH_CONSUMER;

    const PROPERTY_OAUTH_KEY = DataStore::PROPERTY_OAUTH_KEY;
    const PROPERTY_OAUTH_SECRET = DataStore::PROPERTY_OAUTH_SECRET;
    const PROPERTY_OAUTH_CALLBACK = DataStore::PROPERTY_OAUTH_CALLBACK;
    const PROPERTY_OAUTH_TOKEN = 'http://www.taotesting.com/ontologies/taooauth.rdf#Token';
    const PROPERTY_OAUTH_TOKEN_HASH = 'http://www.taotesting.com/ontologies/taooauth.rdf#TokenHash';
    const PROPERTY_OAUTH_TOKEN_URL = 'http://www.taotesting.com/ontologies/taooauth.rdf#TokenUrl';

    const PROPERTY_OAUTH_TOKEN_TYPE = 'http://www.taotesting.com/ontologies/taooauth.rdf#TokenType';
    const PROPERTY_OAUTH_GRANT_TYPE = 'http://www.taotesting.com/ontologies/taooauth.rdf#GrantType';


    public function validate(\common_http_Request $request)
    {
        $headers = $request->getHeaders();

        if (isset($headers['Authorization'])) {
            \common_Logger::i(print_r($headers['Authorization'], true));

        }
        throw new \common_http_InvalidSignatureException();
        return $this;
    }

    public function getConsumer()
    {
        return new \core_kernel_users_GenerisUser(\core_kernel_users_Service::singleton()->getOneUser('admin'));
    }

    public function getClient(array $data)
    {
        $data = array_merge(
            [
                'token_storage' => 'cache',
                'grant_type' => 'client_credentials',
            ],
            $data
        );

        return $this->propagate(new OAuthClient($data));
    }
}