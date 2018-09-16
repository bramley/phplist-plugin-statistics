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
 * Sub-class that provides the populator functions for lists.
 *
 * @category  phplist
 */
use phpList\plugin\Common\PageURL;

class MessageStatisticsPlugin_Controller_Lists extends MessageStatisticsPlugin_Controller implements CommonPlugin_IPopulator
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

        foreach ($this->generateLists($start, $limit) as $row) {
            $latest = $this->model->latestMessage($row['id']);
            $selected = $row['id'] == '' && $this->model->listid === null || $row['id'] === $this->model->listid;
            $key = "{$row['id']} | {$row['name']}";

            if ($selected) {
                $key = "<b>$key</b>";
            }
            $w->addElement($key, $latest ? new PageURL(null, ['type' => 'messages', 'listid' => $row['id']]) : '');
            $w->addColumn($key, $this->i18n->get('active'), $row['active']);
            $w->addColumn($key, $this->i18n->get('total sent'), $row['count']);
            $w->addColumn(
                $key,
                $this->i18n->get('latest'),
                $latest,
                $latest
                    ? new PageURL(null, ['type' => 'opened', 'listid' => $row['id'], 'msgid' => $latest])
                    : ''
            );
        }
    }

    public function total()
    {
        return $this->model->totalLists();
    }

    private function generateLists($start, $limit)
    {
        if (!($start == 0 && $limit == 1)) {
            yield ['id' => '', 'name' => $this->i18n->get('All lists'), 'active' => '', 'count' => ''];
        }

        foreach ($this->model->fetchLists($start, $limit) as $row) {
            yield $row;
        }
    }
}
