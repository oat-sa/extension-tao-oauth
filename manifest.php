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

/**
 * Generated using taoDevTools 3.0.1
 */
return array(
    'name' => 'taoOauth',
    'label' => 'OAT Oauth client',
    'description' => 'Extension to easily configure an OAuth client for OAT platform.',
    'license' => 'GPL-2.0',
    'version' => '5.3.1',
    'author' => 'Open Assessment Technologies SA',
    'requires' => array(
        'generis' => '>=12.5.0',
        'tao' => '>=38.0.0'
    ),
    'acl' => array(
        array('grant', 'http://www.tao.lu/Ontologies/generis.rdf#AnonymousRole', array('ext'=>'taoOauth', 'mod' => 'TokenApi', 'act' => 'requestToken')),
    ),
    'install' => array(
        'rdf' => array(
            dirname(__FILE__) . '/install/ontology/oauth-consumer.rdf',
        ),
        'php' => array(
            \oat\taoOauth\scripts\install\RegisterPublishingOauthAction::class,
        )
    ),
    'update' => oat\taoOauth\scripts\update\Updater::class,
    'routes' => array(
        '/taoOauth' => 'oat\\taoOauth\\controller'
    ),
    'constants' => array(
        # views directory
        "DIR_VIEWS" => dirname(__FILE__).DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR,
    ),
);
