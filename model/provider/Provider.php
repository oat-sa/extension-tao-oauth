<?php
/**
 * Created by PhpStorm.
 * User: siwane
 * Date: 12/12/17
 * Time: 16:24
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

    const RESOURCE_OWNER_ID = 'resource_owner_id';

    const GRANT_TYPE = 'grant_type';
}