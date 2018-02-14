<?php
/**
 * Created by PhpStorm.
 * User: siwane
 * Date: 13/02/18
 * Time: 16:41
 */

namespace oat\taoOauth\model;


use oat\oatbox\service\ConfigurableService;

class Oauth2Service extends ConfigurableService
{
    public function getClient(array $data)
    {
        $data = array_merge(
            [
                'token_storage' => 'cache'
            ],
            $data
        );

        return $this->propagate(new OAuthClient($data));
    }
}