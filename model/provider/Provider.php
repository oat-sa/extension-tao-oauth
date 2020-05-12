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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\taoOauth\model\provider;

interface Provider
{
    /** The client ID assigned to you by the provider */
    const CLIENT_ID = 'client_id';

    /** The client password assigned to you by the provider */
    const CLIENT_SECRET = 'client_secret';

    /** Url to request the token */
    const TOKEN_URL = 'token_url';

    /** Authorization url. Should be <i>false</i> if grant type is 'client_credentials' */
    const AUTHORIZE_URL = 'authorize_url';

    /** @see https://github.com/guzzle/guzzle/blob/master/src/Client.php */
    const HTTP_CLIENT_OPTIONS = 'http_client_options';

    /** URL for requesting the resource owner's details */
    const RESOURCE_OWNER_DETAILS_URL = 'resource_owner_details_url';

    /** Resource owner id */
    const RESOURCE_OWNER_ID = 'resource_owner_id';

    /** Grant type of the oauth token */
    const GRANT_TYPE = 'grant_type';

    /** @var string Type of token */
    const TOKEN_TYPE = 'token_type';

    /** @var string Username for password grant type */
    const USERNAME = 'username';

    /** @var string Password for password grant type */
    const PASSWORD = 'password';

    /** @var string Code for authorization_code grant type */
    const CODE = 'code';

    /** @var string Scopes definition */
    const SCOPE = 'scope';
}
