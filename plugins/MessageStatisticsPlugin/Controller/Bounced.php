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
 * @version   SVN: $Id: Bounced.php 1235 2013-03-17 15:45:44Z Duncan $
 * @link      http://forums.phplist.com/viewtopic.php?f=7&t=35427
 */


/**
 * Sub-class that provides the populator and exportable functions
 * for bounced messages
 * 
 * @category  phplist
 * @package   MessageStatisticsPlugin
 */
 
class MessageStatisticsPlugin_Controller_Bounced 
    extends MessageStatisticsPlugin_Controller
    implements CommonPlugin_IPopulator, CommonPlugin_IExportable
{
    /*
     * Implementation of CommonPlugin_IExportable
     */
    public function exportRows()
    {
        return $this->model->fetchMessageBounces();
    }

    public function exportFieldNames()
    {
        $fields = array();
        $fields[] = $this->i18n->get('Bounce ID');
        $fields[] = $this->i18n->get('email');

        foreach ($this->model->selectedAttrs as $attr) {
            $fields[] = $this->model->attributes[$attr]['name'];
        }
        return $fields;
    }

    public function exportValues(array $row)
    {
        $values = array();
        $values[] = $row['bounce'];
        $values[] = $row['email'];

        foreach ($this->model->selectedAttrs as $attr) {
            $values[] = $row["attr{$attr}"];
        }
        return $values;
    }
    /*
     * Implementation of CommonPlugin_IPopulator
     */
    public function populate(WebblerListing $w, $start, $limit)
    {
        /*
         * Populate the webbler list with users who bounced
         */
        $w->setTitle($this->i18n->get('Bounce ID'));
        $resultIterator = $this->model->fetchMessageBounces($start, $limit);

        foreach ($resultIterator as $row) {
            $key = $row['bounce'];
            $w->addElement($key,  new CommonPlugin_PageURL('bounce', array('s' => 0, 'id' => $row['bounce'])));
            $w->addColumn($key, 'email', $row['email'],
                 new CommonPlugin_PageURL('userhistory', array('id' => $row['user']), 'left')
            );

            foreach ($this->model->selectedAttrs as $attr) {
                $w->addColumn($key, $this->model->attributes[$attr]['name'], $row["attr{$attr}"]);
            }
        }
    }

    public function total()
    {
        /*
         * Returns the total number of records to be displayed
         */
        return $this->model->totalMessageBounces();
    }
}
