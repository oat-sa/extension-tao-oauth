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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoOauth\model\connector;

use oat\taoOauth\model\connector\implementation\NoAuthConnector;
use oat\taoOauth\model\connector\implementation\OAuthConnector;

/**
 * Class ConnectorFactory
 *
 * A factory to create connector for oauth 2 connection or no auth connection
 *
 * @package oat\taoOauth\model\connector
 */
class ConnectorFactory
{
    /**
     * Create a no auth connector.
     *
     * @param array $options
     * @return NoAuthConnector
     */
    public function buildNoAuthConnector(array $options = [])
    {
        return new NoAuthConnector();
    }

    /**
     * Create a oauth2 connection.
     *
     * @param array $options
     * @return OAuthConnector
     */
    public function buildOauthConnector(array $options = [])
    {
        return new OAuthConnector($options);
    }
}