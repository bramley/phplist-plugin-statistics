<?php 
/**
 * MessageStatisticsPlugin for phplist
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
 * @package   MessageStatisticsPlugin
 * @author    Duncan Cameron
 * @copyright 2011-2012 Duncan Cameron
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, Version 3
 * @version   SVN: $Id: ControllerFactory.php 1235 2013-03-17 15:45:44Z Duncan $
 * @link      http://forums.phplist.com/viewtopic.php?f=7&t=35427
 */


/**
 * This class is a concrete implementation of CommonPlugin_ControllerFactoryBase
 * 
 * @category  phplist
 * @package   MessageStatisticsPlugin
 */
 
class MessageStatisticsPlugin_ControllerFactory extends CommonPlugin_ControllerFactoryBase
{
    protected $defaultType = 'messages';

    /**
     * Custom implementation to create a controller using plugin and type
     *
     * @param string $pi the plugin
     * @param array $params further parameters from the URL
     *
     * @return CommonPlugin_Controller 
     * @access public
     */
    public function createController($pi, array $params)
    {
        return $this->createControllerType($pi, $params);
    }
}
