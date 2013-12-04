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
 * @version   SVN: $Id$
 * @link      http://forums.phplist.com/viewtopic.php?f=7&t=35427
 */

/**
 * This class provides database access to the message, usermessage and related tables
 * 
 * @category  phplist
 * @package   MessageStatisticsPlugin
 */
 
class MessageStatisticsPlugin_DAO_Message extends CommonPlugin_DAO_Message
{
    /**
     * Private methods
     */

    private function xx_lu_exists($field, $listid)
    {
        return $listid
            ? "AND EXISTS (
                SELECT 1 FROM {$this->tables['listuser']} lu
                WHERE $field = lu.userid AND lu.listid = $listid)"    
            : '';
    }
    
    private function u_lu_join($listid)
    {
        return $listid
            ? "JOIN {$this->tables['listuser']} lu ON lu.userid = u.id AND lu.listid = $listid"    
            : '';
    }
    
    private function limitClause($start, $limit)
    {
        return is_null($start) ? ''    : "LIMIT $start, $limit";
    }
    
    private function userAttributeJoin($attributes, $searchTerm, $searchAttr)
    {
        global $tables;
        global $table_prefix;

        $searchTerm = sql_escape($searchTerm);
        $attr_fields = '';
        $attr_join = '';

        foreach ($attributes as $attr) {
            $id = $attr['id'];
            $tableName = "{$table_prefix}listattr_{$attr['tablename']}";

            $joinType = ($searchTerm && $searchAttr == $id) ? 'JOIN' : 'LEFT JOIN';
            $thisJoin = "
                $joinType {$this->tables['user_attribute']} ua{$id} ON ua{$id}.userid = u.id AND ua{$id}.attributeid = {$id} ";
            
            switch ($attr['type']) {
            case 'radio':
            case 'select':
                $thisJoin .= "
                $joinType {$tableName} la{$id} ON la{$id}.id = ua{$id}.value ";
                
                if ($searchTerm && $searchAttr == $id) {
                    $thisJoin .= "AND la{$id}.name LIKE '%$searchTerm%' ";
                }
                $attr_fields .= ", la{$id}.name AS attr{$id}";
                break;
            default:
                if ($searchTerm && $searchAttr == $id) {
                    $thisJoin .= "AND ua{$id}.value LIKE '%$searchTerm%' ";
                }
                $attr_fields .= ", ua{$id}.value AS attr{$id}";
                break;
            }
            $attr_join .= $thisJoin;
        }
        return array($attr_join, $attr_fields);
    }

    /**
     * Public methods
     */
    /*
     * Methods for messages
     */
    public function latestMessage($loginid, $listid)
    {
        $owner = $loginid ? "AND m.owner = $loginid" : '';
        $list = $listid ? "AND lm.listid = $listid" : '';
        $sql = 
            "SELECT MAX(lm.messageid) AS msgid
            FROM {$this->tables['listmessage']} lm
            JOIN {$this->tables['message']} m ON lm.messageid = m.id
            JOIN {$this->tables['usermessage']} um ON um.messageid = m.id 
            WHERE m.status = 'sent' $owner $list";

        return $this->dbCommand->queryOne($sql, 'msgid');
    }

    public function prevNextMessage($listId, $msgID, $loginid)
    {
        $owner_and = $loginid ? "AND owner = $loginid" : '';
        $m_lm_exists = $listId
            ? "AND EXISTS (
                SELECT 1 FROM {$this->tables['listmessage']} lm
                WHERE m.id = lm.messageid AND lm.listid = $listId)"
            : "";

        $sql = 
            "SELECT MAX(a.id) AS prev
            FROM (
                SELECT DISTINCT id
                FROM {$this->tables['message']} m
                WHERE m.status = 'sent'
                AND id < $msgID
                $m_lm_exists
                $owner_and
            ) AS a";

        $prev = $this->dbCommand->queryOne($sql, 'prev');

        $sql = 
            "SELECT MIN(a.id) AS next
            FROM (
                SELECT DISTINCT id
                FROM {$this->tables['message']} m
                WHERE m.status = 'sent'
                AND id > $msgID
                $m_lm_exists
                $owner_and
            ) AS a";

        $next = $this->dbCommand->queryOne($sql, 'next');

        return array($prev, $next);
    }

    public function fetchMessages($listId, $loginid, $ascOrder = false, $start = null, $limit = null)
    {
        $order = $ascOrder ? 'ASC' : 'DESC';
        $owner_and = $loginid ? "AND owner = $loginid" : '';
        $limitClause = is_null($start) ? ''    : "LIMIT $start, $limit";
        
        $m_lm_exists = $listId
            ? "AND EXISTS (
                SELECT 1 FROM {$this->tables['listmessage']} lm
                WHERE m.id = lm.messageid AND lm.listid = $listId)"
            : "";

        $um_lu_exists = $this->xx_lu_exists('um.userid', $listId);
        $uml_lu_exists = $this->xx_lu_exists('uml.userid', $listId);
        $umb_lu_exists = $this->xx_lu_exists('umb.user', $listId);
        $umf_lu_exists = $this->xx_lu_exists('umf.user', $listId);

        $sql =
            "SELECT m.id, fromfield AS 'from', subject, viewed, owner,
            date_format(m.sent,'%e %b %Y') AS end, date_format(m.sendstart,'%e %b %Y') AS start,
            (SELECT COUNT(viewed)
                FROM {$this->tables['usermessage']} um
                WHERE messageid = m.id 
                $um_lu_exists
            ) AS openUsers,
            (SELECT COUNT(status)
                FROM {$this->tables['usermessage']} um
                WHERE messageid = m.id
                AND status = 'sent'
                $um_lu_exists
            ) AS sent,

            (SELECT COUNT(DISTINCT uml.userid)
                FROM {$this->tables['linktrack_uml_click']} uml
                WHERE uml.messageid = m.id
                AND EXISTS (
                    SELECT * FROM {$this->tables['usermessage']} um
                    WHERE uml.userid = um.userid AND uml.messageid = um.messageid
                )
                $uml_lu_exists
            ) as clickUsers,
            
            (SELECT COALESCE(SUM(clicked), 0)
                FROM {$this->tables['linktrack_uml_click']} uml
                WHERE uml.messageid = m.id
                AND EXISTS (
                    SELECT * FROM {$this->tables['usermessage']} um
                    WHERE uml.userid = um.userid AND uml.messageid = um.messageid
                )
                $uml_lu_exists
            ) as totalClicks,
            
            (SELECT count(distinct umb.user)
                FROM {$this->tables['user_message_bounce']} umb
                WHERE umb.message = m.id
                AND EXISTS (
                    SELECT 1 FROM {$this->tables['usermessage']} um
                    WHERE umb.user = um.userid AND umb.message = um.messageid
                )
                $umb_lu_exists
            ) AS bouncecount,
           (SELECT COUNT(DISTINCT umf.user)
                FROM {$this->tables['user_message_forward']} AS umf
                WHERE umf.message = m.id
                AND EXISTS (
                    SELECT 1 FROM {$this->tables['usermessage']} um 
                    WHERE um.userid = umf.user AND umf.message = um.messageid
                )
                $umf_lu_exists
            ) AS forwardcount
            FROM {$this->tables['message']} m
            WHERE m.status = 'sent'
            $m_lm_exists
            $owner_and
            ORDER BY id $order
            $limitClause";

        return $this->dbCommand->queryAll($sql);
    }

    public function fetchMessage($msgId, $listId, $excludeRegex)
    {
        $excludeRegex = sql_escape($excludeRegex);
        $m_lm_exists = $listId
            ? "AND EXISTS (
                SELECT 1 FROM {$this->tables['listmessage']} lm
                WHERE m.id = lm.messageid AND lm.listid = $listId)"
            : "";

        $um_lu_exists = $this->xx_lu_exists('um.userid', $listId);
        $umb_lu_exists = $this->xx_lu_exists('umb.user', $listId);
        $umf_lu_exists = $this->xx_lu_exists('umf.user', $listId);
        $uml_lu_exists = $this->xx_lu_exists('uml.userid', $listId);

        $sql =
            "SELECT m.id, fromfield AS 'from', subject, viewed, owner,
            date_format(m.sent,'%e %b %Y') AS end, date_format(m.sendstart,'%e %b %Y') AS start,
            (SELECT COUNT(viewed)
                FROM {$this->tables['usermessage']} um
                WHERE messageid = m.id 
                $um_lu_exists
            ) AS openUsers,

            (SELECT COUNT(status)
                FROM {$this->tables['usermessage']} um
                WHERE messageid = m.id
                AND status = 'sent'
                $um_lu_exists
            ) AS sent,

            (SELECT COUNT(DISTINCT uml.userid)
                FROM {$this->tables['linktrack_uml_click']} uml
                JOIN {$this->tables['linktrack_forward']} fw ON fw.id = uml.forwardid
                WHERE uml.messageid = m.id
                AND fw.url NOT RLIKE('$excludeRegex')
                AND EXISTS (
                    SELECT * FROM {$this->tables['usermessage']} um
                    WHERE uml.userid = um.userid AND uml.messageid = um.messageid
                )
                $uml_lu_exists
            ) as clickUsers,

            (SELECT COALESCE(SUM(clicked), 0)
                FROM {$this->tables['linktrack_uml_click']} uml
                JOIN {$this->tables['linktrack_forward']} fw ON fw.id = uml.forwardid
                WHERE uml.messageid = m.id
                AND fw.url NOT RLIKE('$excludeRegex')
                AND EXISTS (
                    SELECT * FROM {$this->tables['usermessage']} um
                    WHERE uml.userid = um.userid AND uml.messageid = um.messageid
                )
                $uml_lu_exists
            ) as totalClicks,

            (SELECT COUNT(DISTINCT umb.user)
                FROM {$this->tables['user_message_bounce']} umb
                WHERE umb.message = m.id
                AND EXISTS (
                    SELECT 1 FROM {$this->tables['usermessage']} um
                    WHERE umb.user = um.userid AND umb.message = um.messageid
                )
                $umb_lu_exists
            ) AS bouncecount,

           (SELECT COUNT(DISTINCT umf.user)
                FROM {$this->tables['user_message_forward']} AS umf
                WHERE umf.message = m.id
                AND EXISTS (
                    SELECT 1 FROM {$this->tables['usermessage']} um 
                    WHERE um.userid = umf.user AND umf.message = um.messageid
                )
                $umf_lu_exists
            ) AS forwardcount

            FROM {$this->tables['message']} m
            WHERE m.status = 'sent'
            AND m.id = $msgId
            $m_lm_exists";

        return $this->dbCommand->queryRow($sql);
    }

    public function totalMessages($listId, $loginid)
    {
        $owner_and = $loginid ? "AND owner = $loginid" : '';
        $lm_exists = $listId
            ? "AND EXISTS (
                SELECT * FROM {$this->tables['listmessage']} lm
                WHERE lm.messageid =  m.id AND lm.listid = $listId)"
            : "";

        $sql = 
            "SELECT COUNT(m.id) AS t
            FROM {$this->tables['message']} m
            WHERE m.status = 'sent' 
            $lm_exists
            $owner_and";

        return $this->dbCommand->queryOne($sql, 't');
    }
    /*
     * Methods for message views
     */
    public function fetchMessageOpens($opened, $msgid, $listid, 
        $attributes, $searchTerm, $searchAttr,
        $start = null, $limit = null)
    {
        list($attr_join, $attr_fields) = $this->userAttributeJoin($attributes, $searchTerm, $searchAttr);
        $limitClause = $this->limitClause($start, $limit);
        $u_lu_exists = $this->xx_lu_exists('u.id', $listid);

        if ($opened) {
            $isOpened = 'NOT NULL';
            $order = 'um.viewed';
        } else {
            $isOpened = 'NULL';
            $order = 'u.email';
        }

        $sql = 
            "SELECT u.email, um.userid, um.entered, um.viewed $attr_fields
            FROM {$this->tables['usermessage']} um
            JOIN {$this->tables['user']} u ON um.userid = u.id
            $attr_join
            WHERE um.messageid = $msgid
            AND um.status = 'sent'
            AND um.viewed IS $isOpened
            $u_lu_exists
            ORDER BY $order
            $limitClause";
        return $this->dbCommand->queryAll($sql);
    }

    public function totalMessageOpens($opened, $msgid, $listid,    $attributes, $searchTerm, $searchAttr)
    {
        if ($opened) {
            $isOpened = 'NOT NULL';
        } else {
            $isOpened = 'NULL';
        }

        if ($searchTerm) {
            list($attr_join) = $this->userAttributeJoin($attributes, $searchTerm, $searchAttr);
        } else {
            $attr_join = '';
        }

        $u_lu_exists = $this->xx_lu_exists('u.id', $listid);
        $sql = 
            "SELECT COUNT(*) AS t
            FROM {$this->tables['usermessage']} um
            JOIN {$this->tables['user']} u ON um.userid = u.id
            $attr_join
            WHERE um.messageid = $msgid
            AND um.status = 'sent'
            AND um.viewed IS $isOpened
            $u_lu_exists
            ";
        return $this->dbCommand->queryOne($sql, 't');
    }
    /*
     * Methods for message clicks
     */
    public function fetchMessageClicks($msgid, $listid, $attributes, $searchTerm, $searchAttr, $start = null, $limit = null)
    {
        list($attr_join, $attr_fields) = $this->userAttributeJoin($attributes, $searchTerm, $searchAttr);
        $u_lu_exists = $this->xx_lu_exists('u.id', $listid);
        $limitClause = $this->limitClause($start, $limit);

        $sql =
            "SELECT uml.userid as userid, u.email as email, count(uml.forwardid) as links, sum(uml.clicked) as clicks  $attr_fields
            FROM {$this->tables['linktrack_uml_click']} uml
            LEFT JOIN {$this->tables['user']} u ON uml.userid = u.id
            $attr_join
            WHERE uml.messageid = $msgid
            AND EXISTS (
                SELECT 1 FROM {$this->tables['usermessage']} um 
                WHERE um.userid = uml.userid AND uml.messageid = um.messageid
            )
            $u_lu_exists
            GROUP BY uml.userid
            ORDER BY u.email
            $limitClause";

        return $this->dbCommand->queryAll($sql);
    }
    public function totalMessageClicks($msgid, $listid, $attributes, $searchTerm, $searchAttr)
    {
        $u_lu_exists = $this->xx_lu_exists('u.id', $listid);

        if ($searchTerm) {
            list($attr_join) = $this->userAttributeJoin($attributes, $searchTerm, $searchAttr);
        } else {
            $attr_join = '';
        }

        $sql =
            "SELECT COUNT(DISTINCT uml.userid) as t
            FROM {$this->tables['linktrack_uml_click']} uml
            LEFT JOIN {$this->tables['user']} u ON uml.userid = u.id
            $attr_join
            WHERE uml.messageid = $msgid
            AND EXISTS (
                SELECT 1 from {$this->tables['usermessage']} um 
                WHERE um.userid = uml.userid AND uml.messageid = um.messageid
            )
            $u_lu_exists";
        return $this->dbCommand->queryOne($sql, 't');
    }
    /*
     * Methods for message bounces
     */
     public function fetchMessageBounces($mid, $listid, $attributes, $searchTerm, $searchAttr, $start = null, $limit = null)
     {
        list($attr_join, $attr_fields) = $this->userAttributeJoin($attributes, $searchTerm, $searchAttr);
        $umb_lu_exists = $this->xx_lu_exists('umb.user', $listid);
        $limitClause = $this->limitClause($start, $limit);

        $sql = 
           "SELECT u.email, umb.user, umb.bounce $attr_fields
            FROM {$this->tables['user_message_bounce']} AS umb
            JOIN {$this->tables['user']} AS u ON umb.user = u.id
            $attr_join
            WHERE umb.message = $mid
            AND EXISTS (
                SELECT 1 FROM {$this->tables['usermessage']} um 
                WHERE um.userid = umb.user AND umb.message = um.messageid
            )
            $umb_lu_exists
            $limitClause";
        return $this->dbCommand->queryAll($sql);
    }

    public function totalMessageBounces($mid, $listid, $attributes, $searchTerm, $searchAttr)
    {
        $umb_lu_exists = $this->xx_lu_exists('umb.user', $listid);

        if ($searchTerm) {
            list($attr_join) = $this->userAttributeJoin($attributes, $searchTerm, $searchAttr);
        } else {
            $attr_join = '';
        }

        $sql = 
           "SELECT COUNT(umb.user) AS t
            FROM {$this->tables['user_message_bounce']} AS umb
            JOIN {$this->tables['user']} AS u ON umb.user = u.id
            $attr_join
            WHERE umb.message = $mid
            AND EXISTS (
                SELECT 1 FROM {$this->tables['usermessage']} um 
                WHERE um.userid = umb.user AND umb.message = um.messageid
            )
            $umb_lu_exists";

        return $this->dbCommand->queryOne($sql, 't');
    }
    /*
     * Methods for message forwards
     */
     public function fetchMessageForwards($mid, $listid, $attributes, $searchTerm, $searchAttr, $start = null, $limit = null)
     {
        list($attr_join, $attr_fields) = $this->userAttributeJoin($attributes, $searchTerm, $searchAttr);
        $u_lu_exists = $this->xx_lu_exists('u.id', $listid);
        $limitClause = $this->limitClause($start, $limit);

        $sql = 
           "SELECT u.email, u.id, COUNT(umf.id) AS count $attr_fields
            FROM {$this->tables['user_message_forward']} AS umf
            JOIN {$this->tables['user']} AS u ON umf.user = u.id
            $attr_join
            WHERE umf.message = $mid
            AND EXISTS (
                SELECT 1 FROM {$this->tables['usermessage']} um 
                WHERE um.userid = umf.user AND umf.message = um.messageid
            )
            $u_lu_exists
            GROUP BY umf.user
            $limitClause";
        return $this->dbCommand->queryAll($sql);
    }

    public function totalMessageForwards($mid, $listid, $attributes, $searchTerm, $searchAttr)
    {
        $u_lu_exists = $this->xx_lu_exists('u.id', $listid);

        if ($searchTerm) {
            list($attr_join) = $this->userAttributeJoin($attributes, $searchTerm, $searchAttr);
        } else {
            $attr_join = '';
        }

        $sql = 
           "SELECT COUNT(DISTINCT umf.user) AS t
            FROM {$this->tables['user_message_forward']} AS umf
            JOIN {$this->tables['user']} AS u ON umf.user = u.id
            $attr_join
            WHERE umf.message = $mid
            AND EXISTS (
                SELECT 1 FROM {$this->tables['usermessage']} um 
                WHERE um.userid = umf.user AND umf.message = um.messageid
            )
            $u_lu_exists";

        return $this->dbCommand->queryOne($sql, 't');
    }
    /*
     * Methods for domains
     */
    public function messageByDomain($msgID, $listid, $start = null, $limit = null)
    {
        $listuser_join = $this->u_lu_join($listid);
        $limitClause = $this->limitClause($start, $limit);

        $sql =
            "SELECT SUBSTRING_INDEX(u.email, '@', -1) AS domain,
                COUNT(um.viewed) AS opened, COUNT(um.status) AS sent, COUNT(lt.userid) AS clicked
            FROM {$this->tables['user']} u 
            JOIN {$this->tables['usermessage']} um ON u.id = um.userid
            $listuser_join
            LEFT OUTER JOIN 
                (SELECT DISTINCT userid
                FROM {$this->tables['linktrack_uml_click']}
                WHERE messageid = $msgID ) AS lt ON u.id = lt.userid
            WHERE um.messageid = $msgID
            AND um.status = 'sent'
            GROUP BY domain
            $limitClause";

        return $this->dbCommand->queryAll($sql);
    }

    public function totalMessageByDomain($msgID, $listid)
    {
        $listuser_join = $this->u_lu_join($listid);
        $sql =
            "SELECT COUNT(*) AS t 
            FROM (
                SELECT SUBSTRING_INDEX(u.email, '@', -1) AS domain
                FROM {$this->tables['user']} u 
                JOIN {$this->tables['usermessage']} um ON u.id = um.userid
                $listuser_join
                WHERE um.messageid = $msgID
                AND um.status = 'sent'
                GROUP BY domain
            ) AS domain
            ";

        return $this->dbCommand->queryOne($sql, 't');
    }
    /*
     * Methods for links
     */
    public function prevNextForwardId($msgID, $forwardId)
    {
        $url = $this->linkUrl($forwardId);

        $sql = "
            SELECT id AS prev
            FROM {$this->tables['linktrack_forward']}
            WHERE url = (
                SELECT MAX(a.url) 
                FROM (
                    SELECT url
                    FROM {$this->tables['linktrack_forward']} fw
                    JOIN {$this->tables['linktrack_ml']} ml ON ml.forwardid = fw.id
                    WHERE ml.messageid = $msgID
                    AND fw.url < '$url'
                    ORDER BY url
                ) AS a
            )";

        $prev = $this->dbCommand->queryOne($sql, 'prev');

        $sql = "
            SELECT id AS next
            FROM {$this->tables['linktrack_forward']}
            WHERE url = (
                SELECT MIN(a.url)
                FROM (
                    SELECT url
                    FROM {$this->tables['linktrack_forward']} fw
                    JOIN {$this->tables['linktrack_ml']} ml ON ml.forwardid = fw.id
                    WHERE ml.messageid = $msgID
                    AND fw.url > '$url'
                    ORDER BY url
                ) AS a
            )";

        $next = $this->dbCommand->queryOne($sql, 'next');

        return array($prev, $next);
    }

    public function links($msgID, $listid, $start = null, $limit = null)
    {
        $uml_lu_exists = $this->xx_lu_exists('uml.userid', $listid);
        $um_lu_exists = $this->xx_lu_exists('um.userid', $listid);
        $limitClause = $this->limitClause($start, $limit);

        $sql = 
            "SELECT
                fw.url,
                fw.id AS forwardid,
                DATE_FORMAT(MIN(uml.firstclick), '%Y-%m-%d %H:%i') AS firstclick,
                DATE_FORMAT(MAX(uml.latestclick), '%Y-%m-%d %H:%i') AS latestclick,
                COALESCE(SUM(uml.clicked), 0) AS numclicks,
                    (SELECT COUNT(userid) 
                    FROM {$this->tables['usermessage']} um
                    WHERE um.messageid = lt.messageid
                    AND um.status = 'sent'
                    $um_lu_exists
                    ) AS totalsent,
                COALESCE(COUNT(uml.userid), 0) as usersclicked
            FROM {$this->tables['linktrack_ml']} lt
            JOIN {$this->tables['linktrack_forward']} fw ON fw.id = lt.forwardid
            LEFT JOIN {$this->tables['linktrack_uml_click']} uml ON uml.messageid = lt.messageid AND uml.forwardid = lt.forwardid $uml_lu_exists
            WHERE lt.messageid = $msgID 
            GROUP BY lt.forwardid
            ORDER BY fw.url
            $limitClause";

        return $this->dbCommand->queryAll($sql);
    }

    public function totalLinks($msgID, $listid)
    {
        $listuser_join = $this->u_lu_join($listid);
        $sql =
            "SELECT COUNT(*) AS t
            FROM {$this->tables['linktrack_ml']} lt
            WHERE lt.messageid = $msgID
            ";

        return $this->dbCommand->queryOne($sql, 't');
    }

    /*
     * Methods for link clicks
     */
    public function linkClicks($forwardId, $msgID, $listid, $attributes, $searchTerm, $searchAttr, $start = null, $limit = null)
    {
        list($attr_join, $attr_fields) = $this->userAttributeJoin($attributes, $searchTerm, $searchAttr);
        $uml_lu_exists = $this->xx_lu_exists('uml.userid', $listid);
        $limitClause = $this->limitClause($start, $limit);

        $sql = 
            "SELECT u.email, u.id,
            fw.url,
            DATE_FORMAT(uml.firstclick, '%Y-%m-%d %H:%i') as firstclick,
            DATE_FORMAT(uml.latestclick, '%Y-%m-%d %H:%i') as latestclick,
            uml.clicked
            $attr_fields
            FROM {$this->tables['linktrack_uml_click']}  AS uml
            JOIN {$this->tables['user']} AS u ON uml.userid = u.id
            JOIN {$this->tables['linktrack_forward']} AS fw ON fw.id = uml.forwardid
            $attr_join
            WHERE uml.messageid = $msgID
            AND uml.forwardid = $forwardId
            $uml_lu_exists
            $limitClause
            ";

        return $this->dbCommand->queryAll($sql);
    }

    public function totalLinkClicks($forwardId, $msgID, $listid, $attributes, $searchTerm, $searchAttr)
    {
        if ($searchTerm) {
            list($attr_join) = $this->userAttributeJoin($attributes, $searchTerm, $searchAttr);
        } else {
            $attr_join = '';
        }
        $uml_lu_exists = $this->xx_lu_exists('uml.userid', $listid);
        $sql =
            "SELECT COUNT(*) as t
            FROM {$this->tables['linktrack_uml_click']} uml
            JOIN {$this->tables['user']} AS u ON uml.userid = u.id
            $attr_join
            WHERE uml.messageid = $msgID
            AND uml.forwardid = $forwardId
            $uml_lu_exists
            ";

        return $this->dbCommand->queryOne($sql, 't');
    }

    public function linkUrl($forwardid)
    {
        $sql = "
            SELECT url 
            FROM {$this->tables['linktrack_forward']} fw
            WHERE id = $forwardid";

            return $this->dbCommand->queryOne($sql, 'url');
    }
}
