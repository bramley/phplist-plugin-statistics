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
 * @version   SVN: $Id: main.php 665 2012-03-14 09:59:09Z Duncan $
 * @link      http://forums.phplist.com/viewtopic.php?f=7&t=35427
 */

/**
 * This file is the entry code invoked by phplist
 * 
 * @category  phplist
 * @package   MessageStatisticsPlugin
 */
 
if (!(phplistPlugin::isEnabled('CommonPlugin'))) {
    echo "phplist-plugin-common must be installed and enabled to use this plugin";
    return;
}

CommonPlugin_Main::run(new MessageStatisticsPlugin_ControllerFactory());
