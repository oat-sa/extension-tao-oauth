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

    public function testGettingClientCredentialTypeByCredentials()
    {
        $OauthCredentialsFactory = new OauthCredentialsFactory();
        /* @noinspection PhpUnhandledExceptionInspection */
        $clientGrantType = $OauthCredentialsFactory->getCredentialTypeByCredentials([Provider::GRANT_TYPE => ClientCredentialsType::NAME]);
        $this->assertInstanceOf(ClientCredentialsType::class, $clientGrantType);
    }

    public function testGettingPasswordTypeByCredentials()
    {
        $OauthCredentialsFactory = new OauthCredentialsFactory();
        /* @noinspection PhpUnhandledExceptionInspection */
        $passwordGrantType = $OauthCredentialsFactory->getCredentialTypeByCredentials([Provider::GRANT_TYPE => PasswordType::NAME]);
        $this->assertInstanceOf(PasswordType::class, $passwordGrantType);
    }

    public function testGettingAuthCodeTypeByCredentials()
    {
        $OauthCredentialsFactory = new OauthCredentialsFactory();
        /* @noinspection PhpUnhandledExceptionInspection */
        $codeGrantType = $OauthCredentialsFactory->getCredentialTypeByCredentials([Provider::GRANT_TYPE => AuthorizationCodeType::NAME]);
        $this->assertInstanceOf(AuthorizationCodeType::class, $codeGrantType);
    }

    public function testGettingAuthCredentialTypeByCredentialsWithException()
    {
        $OauthCredentialsFactory = new OauthCredentialsFactory();
        $this->expectException(common_exception_ValidationFailed::class);
        /* @noinspection PhpUnhandledExceptionInspection */
        $OauthCredentialsFactory->getCredentialTypeByCredentials([Provider::GRANT_TYPE => 'error']);
    }


}
