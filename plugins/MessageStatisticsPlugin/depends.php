<?php

/**
 * MessageStatisticsPlugin for phplist.
 *
 * This file is a part of MessageStatisticsPlugin.
 *
 * This plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category  phplist
 *
 * @author    Duncan Cameron
 * @copyright 2011-2017 Duncan Cameron
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, Version 3
 */

/**
 * This file provides dependencies for the dependency injection container.
 */
use Psr\Container\ContainerInterface;

return [
    'MessageStatisticsPlugin_Controller_Bounced' => function (ContainerInterface $container) {
        return new MessageStatisticsPlugin_Controller_Bounced(
            $container->get('MessageStatisticsPlugin_Model')
        );
    },
    'MessageStatisticsPlugin_Controller_Clicked' => function (ContainerInterface $container) {
        return new MessageStatisticsPlugin_Controller_Clicked(
            $container->get('MessageStatisticsPlugin_Model')
        );
    },
    'MessageStatisticsPlugin_Controller_Domain' => function (ContainerInterface $container) {
        return new MessageStatisticsPlugin_Controller_Domain(
            $container->get('MessageStatisticsPlugin_Model')
        );
    },
    'MessageStatisticsPlugin_Controller_Forwarded' => function (ContainerInterface $container) {
        return new MessageStatisticsPlugin_Controller_Forwarded(
            $container->get('MessageStatisticsPlugin_Model')
        );
    },
    'MessageStatisticsPlugin_Controller_Linkclicks' => function (ContainerInterface $container) {
        return new MessageStatisticsPlugin_Controller_Linkclicks(
            $container->get('MessageStatisticsPlugin_Model')
        );
    },
    'MessageStatisticsPlugin_Controller_Links' => function (ContainerInterface $container) {
        return new MessageStatisticsPlugin_Controller_Links(
            $container->get('MessageStatisticsPlugin_Model')
        );
    },
    'MessageStatisticsPlugin_Controller_Lists' => function (ContainerInterface $container) {
        return new MessageStatisticsPlugin_Controller_Lists(
            $container->get('MessageStatisticsPlugin_Model')
        );
    },
    'MessageStatisticsPlugin_Controller_Messages' => function (ContainerInterface $container) {
        return new MessageStatisticsPlugin_Controller_Messages(
            $container->get('MessageStatisticsPlugin_Model')
        );
    },
    'MessageStatisticsPlugin_Controller_Opened' => function (ContainerInterface $container) {
        return new MessageStatisticsPlugin_Controller_Opened(
            $container->get('MessageStatisticsPlugin_Model')
        );
    },
    'MessageStatisticsPlugin_Controller_Settings' => function (ContainerInterface $container) {
        return new MessageStatisticsPlugin_Controller_Settings(
            $container->get('MessageStatisticsPlugin_Model')
        );
    },
    'MessageStatisticsPlugin_Controller_Unopened' => function (ContainerInterface $container) {
        return new MessageStatisticsPlugin_Controller_Unopened(
            $container->get('MessageStatisticsPlugin_Model')
        );
    },
    'MessageStatisticsPlugin_Model' => function (ContainerInterface $container) {
        return new MessageStatisticsPlugin_Model(
            $container->get('MessageStatisticsPlugin_DAO_Message'),
            $container->get('MessageStatisticsPlugin_DAO_List'),
            $container->get('attributesById')
        );
    },
    'MessageStatisticsPlugin_DAO_Message' => function (ContainerInterface $container) {
        return new MessageStatisticsPlugin_DAO_Message(
            $container->get('phpList\plugin\Common\DB')
        );
    },
    'MessageStatisticsPlugin_DAO_List' => function (ContainerInterface $container) {
        return new MessageStatisticsPlugin_DAO_List(
            $container->get('phpList\plugin\Common\DB')
        );
    },
    'attributesById' => function (ContainerInterface $container) {
        $dao = $container->get('phpList\plugin\Common\DAO\Attribute');

        return $dao->attributesById(20, 15);
    },
];
