<?php

return new \oat\taoOauth\model\token\TokenService(array(
    \oat\taoOauth\model\token\TokenService::OPTION_HASH => array(
        \oat\taoOauth\model\token\TokenService::OPTION_HASH_ALGORITHM => 'sha256',
        \oat\taoOauth\model\token\TokenService::OPTION_HASH_SALT_LENGTH => 10
    ),
    \oat\taoOauth\model\token\TokenService::OPTION_TOKEN_LIFETIME => 3600
));