<?php

namespace oat\taoOauth\test\model;

use oat\generis\test\TestCase;
use oat\taoOauth\model\provider\Provider;
use oat\taoOauth\model\storage\grant\AuthorizationCodeType;
use oat\taoOauth\model\storage\grant\ClientCredentialsType;
use oat\taoOauth\model\storage\grant\PasswordType;
use oat\taoOauth\model\storage\OauthCredentialsFactory;
use common_exception_ValidationFailed;

/**
 * Class OauthCredentialsFactoryTest
 */
class OauthCredentialsFactoryTest extends TestCase
{
    /**
     * @throws common_exception_ValidationFailed
     */
    public function testGettingClientCredentialTypeByCredentials()
    {
        $OauthCredentialsFactory = new OauthCredentialsFactory();

        $clientGrantType = $OauthCredentialsFactory->getCredentialTypeByCredentials([Provider::GRANT_TYPE => ClientCredentialsType::NAME]);
        $this->assertInstanceOf(ClientCredentialsType::class, $clientGrantType);
    }

    /**
     * @throws common_exception_ValidationFailed
     */
    public function testGettingPasswordTypeByCredentials()
    {
        $OauthCredentialsFactory = new OauthCredentialsFactory();

        $passwordGrantType = $OauthCredentialsFactory->getCredentialTypeByCredentials([Provider::GRANT_TYPE => PasswordType::NAME]);
        $this->assertInstanceOf(PasswordType::class, $passwordGrantType);
    }

    /**
     * @throws common_exception_ValidationFailed
     */
    public function testGettingAuthCodeTypeByCredentials()
    {
        $OauthCredentialsFactory = new OauthCredentialsFactory();

        $codeGrantType = $OauthCredentialsFactory->getCredentialTypeByCredentials([Provider::GRANT_TYPE => AuthorizationCodeType::NAME]);
        $this->assertInstanceOf(AuthorizationCodeType::class, $codeGrantType);
    }

    /**
     * @throws common_exception_ValidationFailed
     */
    public function testGettingAuthCredentialTypeByCredentialsWithException()
    {
        $OauthCredentialsFactory = new OauthCredentialsFactory();
        $this->expectException(common_exception_ValidationFailed::class);
        $OauthCredentialsFactory->getCredentialTypeByCredentials([Provider::GRANT_TYPE => 'error']);
    }


}
