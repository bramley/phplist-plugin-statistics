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
 * @version   SVN: $Id: Lists.php 756 2012-04-24 15:44:56Z Duncan $
 * @link      http://forums.phplist.com/viewtopic.php?f=7&t=35427
 */

/**
 * Sub-class that provides the populator functions for lists
 * 
 * @category  phplist
 * @package   MessageStatisticsPlugin
 */

class MessageStatisticsPlugin_Controller_Lists
    extends MessageStatisticsPlugin_Controller
    implements CommonPlugin_IPopulator
{
    protected $itemsPerPage = array(array(10, 25), 10);

    protected function caption()
    {
        return '';
    }

    protected function prevNext()
    {
        return null;
    }

    /*
     * Implementation of CommonPlugin_IPopulator
     */
    public function populate(WebblerListing $w, $start, $limit)
    {
        /*
         * Populates the webbler list with list details
         */
        $w->setTitle($this->i18n->get('Lists'));
        $resultIterator = $this->model->fetchLists($start, $limit);
        $rows = iterator_to_array($resultIterator);

        if (!($start == 0 && $limit == 1)) {
            $rows[] = array('id' => '', 'name' => $this->i18n->get('All lists'), 'description' => '',
                'active' => '', 'count' => ''
            );
        }

        foreach ($rows as $row) {
            $key = "{$row['id']} | {$row['name']}";
            $latest = $this->model->latestMessage($row['id']);
            $w->addElement($key,
                $latest
                    ? new CommonPlugin_PageURL(null, array('type' => 'messages', 'listid' => $row['id']))
                    : ''
            );
            $w->addColumn($key, $this->i18n->get('active'), $row['active']);
            $w->addColumn($key, $this->i18n->get('total sent'), $row['count']);
            $w->addColumn($key, $this->i18n->get('latest'), $latest,
                $latest
                    ? new CommonPlugin_PageURL(null, array('listid' => $row['id'], 'msgid' => $latest))
                    : ''
            );
        }
    }

    public function total()
    {
        return $this->model->totalLists();
    }
}
