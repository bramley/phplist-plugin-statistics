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
 * Sub-class that provides the populator and exportable functions
 * for opened messages.
 *
 * @category  phplist
 */
class MessageStatisticsPlugin_Controller_Opened extends MessageStatisticsPlugin_Controller implements CommonPlugin_IPopulator, CommonPlugin_IExportable
{
    private $isOpened = true;

    /*
     * Implementation of CommonPlugin_IExportable
     */
    public function exportRows()
    {
        return $this->model->fetchMessageOpens($this->isOpened);
    }

    public function exportFieldNames()
    {
        $fields = array($this->i18n->get('subscriber'));

        foreach ($this->model->selectedAttrs as $attr) {
            $fields[] = $this->model->attributes[$attr]['name'];
        }
        $fields[] = $this->i18n->get('first view');
        $fields[] = $this->i18n->get('total views');
        $fields[] = $this->i18n->get('links clicked');
        $fields[] = $this->i18n->get('clicks_total');

        return $fields;
    }

    public function exportValues(array $row)
    {
        $values = array($row['email']);

        foreach ($this->model->selectedAttrs as $attr) {
            $values[] = $row["attr{$attr}"];
        }
        $values[] = $row['viewed'];
        $values[] = $row['total_views'];
        $values[] = $row['links_clicked'];
        $values[] = $row['total_clicks'];

        return $values;
    }

    /*
     * Implementation of CommonPlugin_IPopulator
     */

    public function populate(WebblerListing $w, $start, $limit)
    {
        /*
         * Populate the webbler list with users who have opened the message
         */
        $w->setElementHeading($this->i18n->get('subscriber'));
        $rows = $this->model->fetchMessageOpens($this->isOpened, $start, $limit);

        foreach ($rows as $row) {
            $key = $row['email'];
            $w->addElement($key, new CommonPlugin_PageURL('user', array('id' => $row['userid'])));

            foreach ($this->model->selectedAttrs as $attr) {
                $w->addColumn($key, $this->model->attributes[$attr]['name'], $row["attr{$attr}"]);
            }
            $w->addColumn($key, $this->i18n->get('first view'), formatDateTime($row['viewed']));
            $url = new CommonPlugin_PageURL(
                null, ['type' => 'userviews', 'userid' => $row['userid'], 'msgid' => $this->model->msgid]
            );
            $w->addColumn($key, $this->i18n->get('total views'), $row['total_views'], $url);
            $url = new CommonPlugin_PageURL(
                'userclicks', ['userid' => $row['userid'], 'msgid' => $this->model->msgid]
            );
            $w->addColumn($key, $this->i18n->get('links clicked'), $row['links_clicked'] ?: '', $url);
            $w->addColumn($key, $this->i18n->get('clicks_total'), $row['total_clicks']);
        }
    }

    public function total()
    {
        /*
         * Returns the total number of records to be displayed
         */
        return $this->model->totalMessageOpens($this->isOpened);
    }
}
