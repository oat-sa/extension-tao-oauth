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
    'version' => '0.0.3',
    'author' => 'Open Assessment Technologies SA',
    'requires' => array(
        'generis' => '>=4.0.1',
        'tao' => '>=12.8.1'
    ),
    'managementRole' => 'http://www.tao.lu/Ontologies/generis.rdf#taoOauthManager',
    'acl' => array(
        array('grant', 'http://www.tao.lu/Ontologies/generis.rdf#taoOauthManager', array('ext' => 'taoOauth')),
    ),
    'install' => array(
        'php' => array(
        )
    ),
    'update' => oat\taoOauth\scripts\update\Updater::class,
);