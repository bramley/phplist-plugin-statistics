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
 * @copyright 2011-2023 Duncan Cameron
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, Version 3
 */

class MessageStatisticsPlugin_Controller_Userviews extends MessageStatisticsPlugin_Controller implements CommonPlugin_IPopulator, CommonPlugin_IExportable
{
    private $userDAO;

    public function __construct(MessageStatisticsPlugin_Model $model, $userDAO)
    {
        parent::__construct($model);
        $this->userDAO = $userDAO;
    }
    /*
     * Implementation of CommonPlugin_IExportable
     */
    public function exportRows()
    {
        return $this->model->userViews();
    }

    public function exportFieldNames()
    {
        $fields = [$this->i18n->get('viewed'), 'IP', 'User Agent'];

        return $fields;
    }

    public function exportValues(array $row)
    {
        if ($row['data'] && ($data = unserialize($row['data']))) {
            $ua = $data['HTTP_USER_AGENT'];
        } else {
            $ua = '';
        }
        $values = [$row['viewed'], $row['ip'], $ua];

        return $values;
    }

    public function exportFileName()
    {
        $msgid = $this->model->msgid;

        return isset($msgid)
            ? "message_{$msgid}_{$this->model->type}"
            : $this->model->type;
    }

    /*
     * Implementation of CommonPlugin_IPopulator
     */

    public function populate(WebblerListing $w, $start, $limit)
    {
        /*
         * Populate the webbler list with user view events
         */
        $w->setElementHeading($this->i18n->get(''));
        $resultIterator = $this->model->userViews($start, $limit);

        foreach ($resultIterator as $i => $row) {
            $key = $start + $i + 1;
            $w->addElement($key);
            $w->addColumn($key, 'Time', $row['viewed']);
            $w->addColumn($key, 'IP', $row['ip']);

            if ($row['data'] && ($data = unserialize($row['data']))) {
                $ua = $data['HTTP_USER_AGENT'];
            } else {
                $ua = '';
            }
            $w->addColumn($key, 'User Agent', $ua);
        }
    }

    public function total()
    {
        return $this->model->totalUserViews();
    }

    protected function caption()
    {
        $user = $this->userDAO->userById($this->model->userid);
        $caption = sprintf(
            "%s\n%s",
            parent::caption(), $this->i18n->get('Views by subscriber %s', $user['email'])
        );

        return $caption;
    }

    protected function prevNext()
    {
        return null;
    }
}
