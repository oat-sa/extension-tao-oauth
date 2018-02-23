<?php
/**
 * Created by PhpStorm.
 * User: siwane
 * Date: 16/02/18
 * Time: 11:45
 */

namespace oat\taoOauth\model;

abstract class OauthController extends \tao_actions_RestController
{
    public function __construct()
    {
        parent::__construct();
        $this->verifyToken();
    }

    protected function verifyToken()
    {
        \common_Logger::i('test');
    }

}