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
 * This class provides database access to the list table.
 *
 * @category  phplist
 */
class MessageStatisticsPlugin_DAO_List extends CommonPlugin_DAO_List
{
    private $selectStatus;

    public function __construct($db)
    {
        parent::__construct($db);
        $this->selectStatus = MessageStatisticsPlugin_DAO_Message::MESSAGE_SELECT;
    }

    /*
     *
     */
    public function fetchLists($loginid, $start = null, $limit = null)
    {
        $owner = $loginid ? " WHERE l.owner = $loginid" : '';
        $limitClause = is_null($start) ? '' : "LIMIT $start, $limit";
        $sql =
            "SELECT l.id, REPLACE(l.name, '&amp;', '&') as name, l.description, l.active,
                count(lm.messageid) as count
            FROM {$this->tables['list']} l
            LEFT OUTER JOIN ({$this->tables['listmessage']} lm, {$this->tables['message']} m)
                ON (l.id = lm.listid AND lm.messageid = m.id AND m.status IN ($this->selectStatus))
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
