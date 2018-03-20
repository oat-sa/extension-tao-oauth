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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoOauth\model\user;

use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;

class UserService extends ConfigurableService
{
    const SERVICE_ID = 'taoOauth/userService';

    const CONSUMER_USER = 'http://www.taotesting.com/ontologies/taooauth.rdf#ConsumerUser';

    use OntologyAwareTrait;

    /**
     * Create a generis user to be associated to consumer resource
     *
     * @param \core_kernel_classes_Resource $consumer
     * @param  $role
     * @return \core_kernel_classes_Resource
     */
    public function createConsumerUser(\core_kernel_classes_Resource $consumer, $role = null)
    {
        $consumerId = $this->getConsumerUserLabel($consumer);
        $properties = [
            GenerisRdf::PROPERTY_USER_FIRSTNAME => $consumerId,
            GenerisRdf::PROPERTY_USER_LASTNAME => $consumerId,
            GenerisRdf::PROPERTY_USER_LOGIN => $consumerId,
            GenerisRdf::PROPERTY_USER_DEFLG => \tao_helpers_I18n::getLangResourceByCode(DEFAULT_LANG),
            GenerisRdf::PROPERTY_USER_UILG => \tao_helpers_I18n::getLangResourceByCode(DEFAULT_LANG),
            GenerisRdf::PROPERTY_USER_TIMEZONE => TIME_ZONE
        ];
        
        if ($role) {
            $properties[GenerisRdf::PROPERTY_USER_ROLES] = $role;
        }
        $oauthUser = $this->getRootClass()->createInstanceWithProperties($properties);
        $consumer->setPropertyValue($this->getProperty(self::CONSUMER_USER), $oauthUser);
        return $oauthUser;
    }

    /**
     * Get the user associated to consumer
     *
     * @param \core_kernel_classes_Resource $consumer
     * @return \core_kernel_classes_Resource|null
     */
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

    /**
     * Get root class for generis user
     *
     * @return \core_kernel_classes_Class
     */
    protected function getRootClass()
    {
        return $this->getClass(GenerisRdf::CLASS_GENERIS_USER);
    }

    /**
     * Get an user label based on consumer
     *
     * @param \core_kernel_classes_Resource $consumer
     * @return string
     */
    protected function getConsumerUserLabel(\core_kernel_classes_Resource $consumer)
    {
        return 'tao-' . \tao_helpers_Uri::getUniqueId($consumer->getUri());
    }
}