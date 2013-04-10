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
 * @version   SVN: $Id: List.php 574 2012-02-02 14:01:11Z Duncan $
 * @link      http://forums.phplist.com/viewtopic.php?f=7&t=35427
 */


/**
 * This class provides database access to the list table
 * 
 * @category  phplist
 * @package   MessageStatisticsPlugin
 */

class MessageStatisticsPlugin_DAO_List extends CommonPlugin_DAO_List
{
    /*
     *
     */
    public function fetchLists($loginid, $start = null, $limit = null)
    {
        $owner = $loginid ? " WHERE l.owner = $loginid" : '';
        $limitClause = is_null($start) ? '' : "LIMIT $start, $limit";
        $sql = 
            "SELECT l.id, REPLACE(l.name, '&amp;', '&') as name, l.description, l.active,
                count(lm.messageid) as count, max(lm.messageid) as max
            FROM {$this->tables['list']} l
            LEFT OUTER JOIN ({$this->tables['listmessage']} lm, {$this->tables['message']} m)
                ON (l.id = lm.listid AND lm.messageid = m.id AND m.status='sent')
            $owner
            GROUP BY l.id
            ORDER BY listorder
            $limitClause";

        return $this->dbCommand->queryAll($sql);
    }

    public function totalLists($loginid)
    {
        $owner = $loginid ? "WHERE l.owner = $loginid" : '';
        $sql = 
            "SELECT count(*) as t
            FROM {$this->tables['list']} l
            $owner";

        return $this->dbCommand->queryOne($sql, 't');
    }

}
