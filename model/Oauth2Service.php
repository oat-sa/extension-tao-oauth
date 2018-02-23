<?php

namespace oat\taoOauth\model;

use oat\oatbox\service\ConfigurableService;

class Oauth2Service extends ConfigurableService
{
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