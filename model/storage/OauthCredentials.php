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
 * Copyright (c) 2018  (original work) Open Assessment Technologies SA;
 *
 * @author Alexander Zagovorichev <olexander.zagovorychev@1pt.com>
 */

namespace oat\taoOauth\model\storage;

use oat\tao\model\auth\AbstractCredentials;
use oat\taoOauth\model\provider\Provider;

/**
 * Class OauthCredentials
 * @package oat\taoOauth\model\storage
 */
class OauthCredentials extends AbstractCredentials
{
    /** @var string */
    protected $client_id;

    /** @var string */
    protected $client_secret;

    /** @var string */
    protected $token_url;

    /** @var string */
    protected $token_type;

    /** @var string */
    protected $token_grant_type;

    /**
     * @return string
     */
    public function client_id()
    {
        return $this->client_id;
    }

    /**
     * @return string
     */
    public function client_secret()
    {
        return $this->client_secret;
    }

    /**
     * @return string
     */
    public function token_url()
    {
        return $this->token_url;
    }

    /**
     * @return string
     */
    public function token_type()
    {
        return $this->token_type;
    }

    /**
     * @return string
     */
    public function token_grant_type()
    {
        return $this->token_grant_type;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return [
            Provider::CLIENT_ID,
            Provider::CLIENT_SECRET,
            Provider::TOKEN_URL,
            Provider::TOKEN_TYPE,
            Provider::GRANT_TYPE
        ];
    }
}
