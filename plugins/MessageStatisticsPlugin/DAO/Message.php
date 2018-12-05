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
 * This class provides database access to the message, usermessage and related tables.
 *
 * @category  phplist
 */
class MessageStatisticsPlugin_DAO_Message extends CommonPlugin_DAO_Message
{
    const MESSAGE_SELECT = "'sent', 'inprocess', 'suspended'";
    /**
     * Private methods.
     */
    private $orderByAlias = 'COALESCE(m.sent, m.sendstart, m.embargo, m.entered)';
    private $orderBy = 'COALESCE(sent, sendstart, embargo, entered)';
    private $selectStatus;

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
        return is_null($start) ? '' : "LIMIT $start, $limit";
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
     * Generate base query for messages.
     *
     * @param int|null    $listId       Include only users who belong to the list
     * @param string|null $excludeRegex Exclude matching URLs from click tracking results
     *
     * @return string the query
     */
    private function baseMessageQuery($listId, $excludeRegex = null)
    {
        $urlExclude = $excludeRegex !== null
            ? sprintf("AND fw.url NOT RLIKE('%s')", sql_escape($excludeRegex))
            : '';
        $m_lm_exists = $listId
            ? "AND EXISTS (
                SELECT 1 FROM {$this->tables['listmessage']} lm
                WHERE m.id = lm.messageid AND lm.listid = $listId)"
            : '';
        $um_lu_exists = $this->xx_lu_exists('um.userid', $listId);
        $uml_lu_exists = $this->xx_lu_exists('uml.userid', $listId);
        $umb_lu_exists = $this->xx_lu_exists('umb.user', $listId);
        $umf_lu_exists = $this->xx_lu_exists('umf.user', $listId);
        $sql = <<<END
            SELECT
            m.id,
            fromfield AS 'from',
            viewed,
            owner,
            $this->orderByAlias AS end,
            m.sendstart AS start,
            REPLACE(COALESCE(md.data, subject), '\\\\', '') AS subject,
            md2.data AS campaigntitle,
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
                $urlExclude
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
                $urlExclude
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
            LEFT JOIN {$this->tables['messagedata']} md ON m.id = md.id AND md.name = 'subject'
            LEFT JOIN {$this->tables['messagedata']} md2 ON m.id = md2.id AND md2.name = 'campaigntitle'
            WHERE m.status IN ($this->selectStatus)
            $m_lm_exists
END;

        return $sql;
    }

    /**
     * Public methods.
     */
    public function __construct($db)
    {
        parent::__construct($db);
        $this->selectStatus = self::MESSAGE_SELECT;
    }

    /*
     * Methods for messages
     */
    public function latestMessage($loginid, $listid)
    {
        $owner = $loginid ? "AND m.owner = $loginid" : '';
        $list = $listid ? "AND lm.listid = $listid" : '';

        $sql =
            "SELECT m.id AS id
            FROM {$this->tables['message']} m
            JOIN {$this->tables['listmessage']} lm ON lm.messageid = m.id
            WHERE m.status IN ($this->selectStatus)
            $owner
            $list
            ORDER BY $this->orderByAlias DESC
            LIMIT 1";

        return $this->dbCommand->queryOne($sql, 'id');
    }

    public function prevNextMessage($listId, $msgID, $loginid)
    {
        $owner_and = $loginid ? "AND owner = $loginid" : '';
        $m_lm_join = $listId
            ? "JOIN {$this->tables['listmessage']} lm ON m.id = lm.messageid AND lm.listid = $listId"
            : '';

        $sql =
            "SELECT $this->orderBy AS ref
            FROM {$this->tables['message']}
            WHERE id = $msgID";

        $ref = $this->dbCommand->queryOne($sql, 'ref');

        $sql =
            "SELECT m.id AS prev
            FROM {$this->tables['message']} m
            $m_lm_join
            WHERE m.status IN ($this->selectStatus)
            AND $this->orderByAlias < '$ref'
            $owner_and
            ORDER BY $this->orderByAlias DESC
            LIMIT 1";

        $prev = $this->dbCommand->queryOne($sql, 'prev');

        $sql =
            "SELECT m.id AS next
            FROM {$this->tables['message']} m
            $m_lm_join
            WHERE m.status IN ($this->selectStatus)
            AND $this->orderByAlias > '$ref'
            $owner_and
            ORDER BY $this->orderByAlias ASC
            LIMIT 1";

        $next = $this->dbCommand->queryOne($sql, 'next');

        return array($prev, $next);
    }

    public function fetchMessages($listId, $loginid, $ascOrder = false, $start = null, $limit = null)
    {
        $owner_and = $loginid ? "AND owner = $loginid" : '';
        $order = $ascOrder ? 'ASC' : 'DESC';
        $limitClause = is_null($start) ? '' : "LIMIT $start, $limit";

        $query = $this->baseMessageQuery($listId);
        $query .= <<<END
            $owner_and
            ORDER BY $this->orderBy $order
            $limitClause
END;

        return $this->dbCommand->queryAll($query);
    }

    public function fetchMessage($msgId, $listId, $excludeRegex)
    {
        $query = $this->baseMessageQuery($listId, $excludeRegex);
        $query .= " AND m.id = $msgId";

        return $this->dbCommand->queryRow($query);
    }

    public function totalMessages($listId, $loginid)
    {
        $owner_and = $loginid ? "AND owner = $loginid" : '';
        $lm_exists = $listId
            ? "AND EXISTS (
                SELECT * FROM {$this->tables['listmessage']} lm
                WHERE lm.messageid =  m.id AND lm.listid = $listId)"
            : '';

        $sql =
            "SELECT COUNT(m.id) AS t
            FROM {$this->tables['message']} m
            WHERE m.status IN ($this->selectStatus)
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

    public function totalMessageOpens($opened, $msgid, $listid, $attributes, $searchTerm, $searchAttr)
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
                fw.personalise,
                MIN(uml.firstclick) AS firstclick,
                MAX(uml.latestclick) AS latestclick,
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
            uml.firstclick as firstclick,
            uml.latestclick as latestclick,
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
