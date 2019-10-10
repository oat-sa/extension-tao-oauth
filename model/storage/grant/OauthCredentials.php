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
 * Copyright (c) 2019  (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoOauth\model\storage\grant;

use oat\tao\model\auth\AbstractCredentials;
use oat\taoOauth\model\provider\Provider;

/**
 * Class OauthCredentials
 * @package oat\taoOauth\model\storage\grant
 */
class OauthCredentials extends AbstractCredentials
{
    /**
     * @return array
     */
    public function getProperties()
    {
        return [
            Provider::CLIENT_ID => $this->properties[Provider::CLIENT_ID],
            Provider::CLIENT_SECRET => $this->properties[Provider::CLIENT_SECRET],
            Provider::TOKEN_URL => !empty($this->properties[Provider::TOKEN_URL]) ? $this->properties[Provider::TOKEN_URL] : '',
            Provider::TOKEN_TYPE => !empty($this->properties[Provider::TOKEN_TYPE]) ? $this->properties[Provider::TOKEN_TYPE] : '',
            Provider::GRANT_TYPE => !empty($this->properties[Provider::GRANT_TYPE]) ? $this->properties[Provider::GRANT_TYPE]: ''
        ];
    }
}
