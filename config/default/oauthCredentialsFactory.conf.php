<?php

use \oat\taoOauth\model\storage\OauthCredentialsFactory;
use oat\taoOauth\model\storage\grant\ClientCredentialsType;
use oat\taoOauth\model\storage\grant\PasswordType;
use oat\taoOauth\model\storage\grant\AuthorizationCodeType;

return new OauthCredentialsFactory([
    OauthCredentialsFactory::OPTION_GRANT_MAP => [
        ClientCredentialsType::NAME => ClientCredentialsType::class,
        PasswordType::NAME => PasswordType::class,
        AuthorizationCodeType::NAME => AuthorizationCodeType::class,
]
]);
