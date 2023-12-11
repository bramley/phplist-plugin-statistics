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
 * This class is a concrete implementation of phpList\plugin\Common\ControllerFactoryBase.
 *
 * @category  phplist
 */
use phpList\plugin\Common\Container;

class MessageStatisticsPlugin_ControllerFactory extends phpList\plugin\Common\ControllerFactoryBase
{
    const DEFAULT_TYPE = 'messages';

    /**
     * Custom implementation to create a controller using plugin and type.
     * The controller is created by the dependency injection container.
     *
     * @param string $pi     the plugin
     * @param array  $params further parameters from the URL
     *
     * @return phpList\plugin\Common\Controller
     */
    public function createController($pi, array $params)
    {
        $depends = include __DIR__ . '/depends.php';
        $container = new Container($depends);
        $type = isset($params['type']) ? $params['type'] : self::DEFAULT_TYPE;
        $class = $pi . '_Controller_' . ucfirst($type);

        return $container->get($class);
    }
}
