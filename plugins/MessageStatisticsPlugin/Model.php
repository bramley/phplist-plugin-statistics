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
 * This class provides the model in MVC.
 *
 * @category  phplist
 */
class MessageStatisticsPlugin_Model extends CommonPlugin_Model
{
    /*
     *    Private variables
     */
    private $messageDAO;
    private $listDAO;
    private $attributeDAO;
    private $owner;
    /*
     *    Inherited protected variables
     */
    protected $properties = array(
        'type' => 'messages',
        'listid' => null,
        'msgid' => null,
        'forwardid' => null,
        'selectedAttrs' => array(),
        'searchTerm' => null,
        'searchBy' => null,
    );
    protected $persist = array(
        'listid' => '',
        'selectedAttrs' => '',
        'searchTerm' => '',
        'searchBy' => '',
    );
    /*
     *    Public variables
     */
    public $msgSubject;
    public $access;
    public $listNames = [];
    public $attributes = [];

    /*
     *    Private methods
     */
    private function verifySelectedAttributes()
    {
        /*
         * remove selected attributes that no longer exist
         */
        $this->properties['selectedAttrs'] =
            array_values(
                array_filter(
                    $this->properties['selectedAttrs'],
                    function ($attrID) {
                        return isset($this->attributes[$attrID]);
                    }
                )
            );
    }

    /*
     *    Public methods
     */
    public function __construct(
        MessageStatisticsPlugin_DAO_Message $messageDAO,
        MessageStatisticsPlugin_DAO_List $listDAO,
        array $attributesById
    ) {
        parent::__construct('MessageStatistics');
        $this->messageDAO = $messageDAO;
        $this->listDAO = $listDAO;
        $this->attributes = $attributesById;
        $this->access = accessLevel('mviews');
        $this->owner = ($this->access == 'owner') ? $_SESSION['logindetails']['id'] : '';
        $this->verifySelectedAttributes();
    }

    public function validateProperties()
    {
        if (!(isset($this->properties['listid']) && ctype_digit($this->properties['listid']))) {
            $this->listid = null;
        }

        if (!(isset($this->properties['msgid']) && ctype_digit($this->properties['msgid']))) {
            $this->msgid = null;
        }

        switch ($this->type) {
            case 'lists':
                break;
            case 'messages':
                if ($this->listid) {
                    $row = $this->listDAO->listById($this->listid);
                    $this->listNames = array($row['name']);
                } elseif ($this->msgid) {
                    // currently used only by the print action
                    $this->listNames = $this->listDAO->listsForMessage($this->msgid, 'name');
                } else {
                    $this->listNames = null;
                }
                break;
            default:
                if (is_null($this->msgid)) {
                    $msgid = $this->messageDAO->latestMessage($this->owner, $this->listid);

                    if (!$msgid) {
                        throw new MessageStatisticsPlugin_NoMessagesException();
                    }

                    $this->msgid = $msgid;
                    $message = loadMessageData($this->msgid);
                } else {
                    $message = loadMessageData($this->msgid);

                    if (!$message) {
                        throw new MessageStatisticsPlugin_MessageNotExistException($this->msgid);
                    }

                    if ($this->owner && $message['owner'] != $this->owner) {
                        throw new MessageStatisticsPlugin_NotAuthorisedException($this->msgid);
                    }
                }
                $this->msgSubject = self::useSubject($message);

                if ($this->listid) {
                    $row = $this->listDAO->listById($this->listid);
                    $this->listNames = array($row['name']);
                } else {
                    $this->listNames = $this->listDAO->listsForMessage($this->msgid, 'name');
                }
        }
    }

    public function fetchMessageOpens($isOpened, $start = null, $limit = null)
    {
        return $this->messageDAO->fetchMessageOpens(
            $isOpened, $this->msgid, $this->listid, $this->attributes,
            $this->searchTerm, $this->searchBy, $start, $limit
        );
    }

    public function totalMessageOpens($isOpened)
    {
        return $this->messageDAO->totalMessageOpens(
            $isOpened, $this->msgid, $this->listid, $this->attributes, $this->searchTerm, $this->searchBy
        );
    }

    public function fetchMessageClicks($start = null, $limit = null)
    {
        return $this->messageDAO->fetchMessageClicks(
            $this->msgid, $this->listid, $this->attributes,
            $this->searchTerm, $this->searchBy, $start, $limit
        );
    }

    public function totalMessageClicks($msgID = null)
    {
        return $this->messageDAO->totalMessageClicks(
            is_null($msgID) ? $this->msgid : $msgID, $this->listid, $this->attributes,
            $this->searchTerm, $this->searchBy
        );
    }

    public function fetchMessageBounces($start = null, $limit = null)
    {
        return $this->messageDAO->fetchMessageBounces(
            $this->msgid, $this->listid, $this->attributes, $this->searchTerm, $this->searchBy,
            $start, $limit
        );
    }

    public function totalMessageBounces()
    {
        return $this->messageDAO->totalMessageBounces(
            $this->msgid, $this->listid, $this->attributes, $this->searchTerm, $this->searchBy
        );
    }

    public function fetchMessage($excludeRegex)
    {
        return $this->messageDAO->fetchMessage($this->msgid, $this->listid, $excludeRegex);
    }

    public function fetchMessages($ascOrder = false, $start = null, $limit = null)
    {
        return $this->messageDAO->fetchMessages($this->listid, $this->owner, $ascOrder, $start, $limit);
    }

    public function totalMessages()
    {
        return $this->messageDAO->totalMessages($this->listid, $this->owner);
    }

    public function prevNextMessage()
    {
        return $this->messageDAO->prevNextMessage($this->listid, $this->msgid, $this->owner);
    }

    public function fetchLists($start = null, $limit = null)
    {
        return $this->listDAO->fetchLists($this->owner, $start, $limit);
    }

    public function totalLists()
    {
        return $this->listDAO->totalLists($this->owner);
    }

    public function messageByDomain($start = null, $limit = null)
    {
        return $this->messageDAO->messageByDomain($this->msgid, $this->listid, $start, $limit);
    }

    public function totalMessageByDomain()
    {
        return $this->messageDAO->totalMessageByDomain($this->msgid, $this->listid);
    }

    public function fetchMessageForwards($start = null, $limit = null)
    {
        return $this->messageDAO->fetchMessageForwards(
            $this->msgid, $this->listid, $this->attributes, $this->searchTerm, $this->searchBy,
            $start, $limit
        );
    }

    public function totalMessageForwards()
    {
        return $this->messageDAO->totalMessageForwards(
            $this->msgid, $this->listid, $this->attributes, $this->searchTerm, $this->searchBy
        );
    }

    public function latestMessage($listid = null)
    {
        return $this->messageDAO->latestMessage($this->owner, is_null($listid) ? $this->listid : $listid);
    }

    public function prevNextForwardId()
    {
        return $this->messageDAO->prevNextForwardId($this->msgid, $this->forwardid);
    }

    public function links($start = null, $limit = null)
    {
        return $this->messageDAO->links($this->msgid, $this->listid, $start, $limit);
    }

    public function totalLinks()
    {
        return $this->messageDAO->totalLinks($this->msgid, $this->listid);
    }

    public function linkClicks($start = null, $limit = null)
    {
        return $this->messageDAO->linkClicks($this->forwardid, $this->msgid, $this->listid,
            $this->attributes, $this->searchTerm, $this->searchBy, $start, $limit);
    }

    public function totalLinkClicks()
    {
        return $this->messageDAO->totalLinkClicks($this->forwardid, $this->msgid, $this->listid,
            $this->attributes, $this->searchTerm, $this->searchBy
        );
    }

    public function linkUrl()
    {
        return $this->messageDAO->linkUrl($this->forwardid);
    }

    public static function useSubject(array $message)
    {
        $useSubject = (bool) getConfig('statistics_display_subject');

        return $useSubject || !$message['campaigntitle'] ? $message['subject'] : $message['campaigntitle'];
    }
}
