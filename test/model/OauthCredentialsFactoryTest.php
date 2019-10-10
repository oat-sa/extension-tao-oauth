<?php

namespace oat\taoOauth\test\model;

use oat\generis\test\TestCase;
use oat\taoOauth\model\provider\Provider;
use oat\taoOauth\model\storage\grant\AuthorizationCodeType;
use oat\taoOauth\model\storage\grant\ClientCredentialsType;
use oat\taoOauth\model\storage\grant\PasswordType;
use oat\taoOauth\model\storage\OauthCredentialsFactory;

/**
 * Class OauthCredentialsFactoryTest
 */
class OauthCredentialsFactoryTest extends TestCase
{
    /**
     * @throws \common_exception_ValidationFailed
     */
    public function testGettingCredentialTypeByCredentials()
    {
        $OauthCredentialsFactory = new OauthCredentialsFactory();

        $clientGrantType = $OauthCredentialsFactory->getCredentialTypeByCredentials([Provider::GRANT_TYPE => ClientCredentialsType::NAME]);
        $this->assertInstanceOf(ClientCredentialsType::class, $clientGrantType);

        $passwordGrantType = $OauthCredentialsFactory->getCredentialTypeByCredentials([Provider::GRANT_TYPE => PasswordType::NAME]);
        $this->assertInstanceOf(PasswordType::class, $passwordGrantType);

        $codeGrantType = $OauthCredentialsFactory->getCredentialTypeByCredentials([Provider::GRANT_TYPE => AuthorizationCodeType::NAME]);
        $this->assertInstanceOf(AuthorizationCodeType::class, $codeGrantType);
    }

}
