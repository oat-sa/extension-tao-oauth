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
 * Copyright (c) 2017-2018 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoOauth\scripts\update;

use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\auth\AbstractAuthService;
use oat\tao\model\session\restSessionFactory\RestSessionFactory;
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoOauth\model\bootstrap\Oauth2SessionBuilder;
use oat\taoOauth\model\bootstrap\OAuth2Type;
use oat\taoOauth\model\Oauth2Service;
use oat\taoOauth\model\OAuthClient;
use oat\taoOauth\model\storage\ConsumerStorage;
use oat\taoOauth\model\storage\grant\AuthorizationCodeType;
use oat\taoOauth\model\storage\grant\ClientCredentialsType;
use oat\taoOauth\model\storage\grant\PasswordType;
use oat\taoOauth\model\storage\OauthCredentialsFactory;
use oat\taoOauth\model\token\TokenService;
use oat\taoOauth\model\user\UserService;

/**
 * @deprecated use migrations instead. See https://github.com/oat-sa/generis/wiki/Tao-Update-Process
 */
class Updater extends \common_ext_ExtensionUpdater
{
    /**
     * @param $initialVersion
     * @return string|void
     * @throws \Exception
     */
    public function update($initialVersion)
    {
        $this->skip('0.0.1', '0.0.6');

        if ($this->isVersion('0.0.6')) {
            OntologyUpdater::syncModels();

            // This part not needed any more. Please use RegisterPublishingAuthTypeAction for configure auth types
//            /** @var PublishingAuthService $service */
//            $service = $this->getServiceManager()->get(PublishingAuthService::SERVICE_ID);
//            $types = $service->getOption(AbstractAuthService::OPTION_TYPES);
//            $alreadyRegistered = false;
//            foreach ($types as $type) {
//                if ($type instanceof OAuth2Type) {
//                    $alreadyRegistered = true;
//                    break;
//                }
//            }
//            if (!$alreadyRegistered) {
//                $types[] = new OAuth2Type();
//                $service->setOption(AbstractAuthService::OPTION_TYPES, $types);
//                $this->getServiceManager()->register(PublishingAuthService::SERVICE_ID, $service);
//            }

            /** @var RestSessionFactory $service */
            $service = $this->getServiceManager()->get(RestSessionFactory::SERVICE_ID);
            $builders = $service->getOption(RestSessionFactory::OPTION_BUILDERS);
            if (!in_array(Oauth2SessionBuilder::class, $builders)) {
                array_unshift($builders, Oauth2SessionBuilder::class);
                $service->setOption(RestSessionFactory::OPTION_BUILDERS, $builders);
                $this->getServiceManager()->register(RestSessionFactory::SERVICE_ID, $service);
            }


            $this->getServiceManager()->register(UserService::SERVICE_ID, new UserService());
            $this->getServiceManager()->register(TokenService::SERVICE_ID, new TokenService(array(
                TokenService::OPTION_HASH => array(
                    TokenService::OPTION_HASH_ALGORITHM => 'sha256',
                    TokenService::OPTION_HASH_SALT_LENGTH => 10
                ),
                TokenService::OPTION_TOKEN_LIFETIME => 3600
            )));
            $this->getServiceManager()->register(Oauth2Service::SERVICE_ID, new Oauth2Service());
            $this->getServiceManager()->register(ConsumerStorage::SERVICE_ID, new ConsumerStorage(array(
                ConsumerStorage::OPTION_PERSISTENCE => 'default',
                ConsumerStorage::OPTION_CACHE => 'cache',
            )));

            AclProxy::applyRule(new AccessRule(
                'grant', 'http://www.tao.lu/Ontologies/generis.rdf#AnonymousRole', array('ext'=>'taoOauth', 'mod' => 'TokenApi', 'act' => 'requestToken')
            ));

            $this->setVersion('0.1.0');
        }

        $this->skip('0.1.0', '5.2.1');
        
        //Updater files are deprecated. Please use migrations.
        //See: https://github.com/oat-sa/generis/wiki/Tao-Update-Process

        $this->setVersion($this->getExtension()->getManifest()->getVersion());
    }
}
