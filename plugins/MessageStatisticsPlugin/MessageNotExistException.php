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
 * This exception is thrown when a message does not exist.
 *
 * @category  phplist
 */
class MessageStatisticsPlugin_MessageNotExistException extends CommonPlugin_Exception
{
    /*
     *    Public methods
     */
    public function __construct($msgid)
    {
        parent::__construct('Message %d does not exist', 0, $msgid);
    }
}
