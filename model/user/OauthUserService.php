<?php
/**
 * Created by PhpStorm.
 * User: siwane
 * Date: 08/03/18
 * Time: 09:31
 */

namespace oat\taoOauth\model\user;

use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;

class OauthUserService extends ConfigurableService
{
    const SERVICE_ID = 'taoOauth/userService';

    const TAO_SYNC_ROLE = 'http://www.tao.lu/Ontologies/generis.rdf#SyncManagerRole';
    const CONSUMER_USER = 'http://www.tao.lu/Ontologies/generis.rdf#SyncManagerRole';

    use OntologyAwareTrait;

    public function createOauthUser(\core_kernel_classes_Resource $consumer)
    {
        $consumerId = $this->getConsumerUserLabel($consumer);
        $oauthUser = $this->getRootClass()->createInstanceWithProperties(array(
            GenerisRdf::PROPERTY_USER_ROLES => self::TAO_SYNC_ROLE,
            GenerisRdf::PROPERTY_USER_FIRSTNAME => $consumerId,
            GenerisRdf::PROPERTY_USER_LASTNAME => $consumerId,
            GenerisRdf::PROPERTY_USER_LOGIN => $consumerId,
            GenerisRdf::PROPERTY_USER_DEFLG => DEFAULT_LANG,
            GenerisRdf::PROPERTY_USER_UILG => DEFAULT_LANG,
            GenerisRdf::PROPERTY_USER_TIMEZONE => TIME_ZONE,

        ));
        $consumer->setPropertyValue($this->getProperty(self::CONSUMER_USER), $oauthUser);
        return $consumer;
    }

    public function getConsumerUser(\core_kernel_classes_Resource $consumer)
    {
        try {
            $user = $this->getResource($consumer->getOnePropertyValue($this->getProperty(self::CONSUMER_USER)));
            if (empty((string) $user)) {
                return null;
            }
            return $user;
        } catch (\common_Exception $e) {
            return null;
        }
    }

    protected function getRootClass()
    {
        return $this->getClass(GenerisRdf::CLASS_GENERIS_USER);;
    }

    protected function getConsumerUserLabel(\core_kernel_classes_Resource $consumer)
    {
        return 'tao-' . \tao_helpers_Uri::getUniqueId($consumer);;
    }
}