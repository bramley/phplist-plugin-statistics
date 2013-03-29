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
 * @version   SVN: $Id: NoMessagesException.php 574 2012-02-02 14:01:11Z Duncan $
 * @link      http://forums.phplist.com/viewtopic.php?f=7&t=35427
 */


/**
 * This exception is thrown when there are no messages that the current admin can view
 * 
 * @category  phplist
 * @package   MessageStatisticsPlugin
 */
 
class MessageStatisticsPlugin_NoMessagesException extends CommonPlugin_Exception
{
	/*
	 *	Public methods
	 */
	public function __construct()
	{
		parent::__construct('no_messages');
	}
}
