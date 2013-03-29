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
 * @version   SVN: $Id: Settings.php 763 2012-05-07 13:02:58Z Duncan $
 * @link      http://forums.phplist.com/viewtopic.php?f=7&t=35427
 */

class MessageStatisticsPlugin_Controller_Settings
	extends MessageStatisticsPlugin_Controller
{
	protected $showAttributeForm = true;

	protected function caption()
	{
		return $this->i18n->get(count($this->model->attributes) > 0 ? 'caption' : 'no_attrs');
	}
}
