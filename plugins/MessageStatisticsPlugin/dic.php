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
 * This file creates a dependency injection container.
 */
use Mouf\Picotainer\Picotainer;
use Psr\Container\ContainerInterface;

return new Picotainer([
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
        return new MessageStatisticsPlugin_DAO_Message(new CommonPlugin_DB());
    },
    'MessageStatisticsPlugin_DAO_List' => function (ContainerInterface $container) {
        return new MessageStatisticsPlugin_DAO_List(new CommonPlugin_DB());
    },
    'attributesById' => function (ContainerInterface $container) {
        $dao = new CommonPlugin_DAO_Attribute(new CommonPlugin_DB());

        return $dao->attributesById();
    },
]);
