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
 * for clicked messages.
 *
 * @category  phplist
 */
class MessageStatisticsPlugin_Controller_Forwarded extends MessageStatisticsPlugin_Controller implements phpList\plugin\Common\IPopulator, phpList\plugin\Common\IExportable
{
    /*
     * Implementation of phpList\plugin\Common\IExportable
     */
    public function exportRows()
    {
        return $this->model->fetchMessageForwards();
    }

    public function exportFieldNames()
    {
        $fields = array($this->i18n->get('subscriber'));

        foreach ($this->model->selectedAttrs as $attr) {
            $fields[] = $this->model->attributes[$attr]['name'];
        }

        $fields[] = $this->i18n->get('count');

        return $fields;
    }

    public function exportValues(array $row)
    {
        $values = array($row['email']);

        foreach ($this->model->selectedAttrs as $attr) {
            $values[] = $row["attr{$attr}"];
        }

        $values[] = $row['count'];

        return $values;
    }

    /*
     * Implementation of phpList\plugin\Common\IPopulator
     */
    public function populate(WebblerListing $w, $start, $limit)
    {
        /*
         * Populate the webbler list with users who have forwarded the message
         */
        $w->setElementHeading($this->i18n->get('subscriber'));
        $resultSet = $this->model->fetchMessageForwards($start, $limit);

        foreach ($resultSet as $row) {
            $key = $row['email'];
            $w->addElement($key, new phpList\plugin\Common\PageURL('user', array('id' => $row['id'])));

            foreach ($this->model->selectedAttrs as $attr) {
                $w->addColumn($key, $this->model->attributes[$attr]['name'], $row["attr{$attr}"]);
            }
            $w->addColumn($key, $this->i18n->get('count'), $row['count'], null, 'left');
        }
    }

    public function total()
    {
        /*
         * Returns the total number of records to be displayed
         */
        return $this->model->totalMessageForwards();
    }
}
