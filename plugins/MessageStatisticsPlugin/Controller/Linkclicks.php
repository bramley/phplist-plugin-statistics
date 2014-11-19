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
 * @version   SVN: $Id: Linkclicks.php 1232 2013-03-16 10:17:11Z Duncan $
 * @link      http://forums.phplist.com/viewtopic.php?f=7&t=35427
 */

/**
 * Sub-class that provides the populator and exportable functions 
 * for link clicks
 * 
 * @category  phplist
 * @package   MessageStatisticsPlugin
 */

class MessageStatisticsPlugin_Controller_Linkclicks
    extends MessageStatisticsPlugin_Controller
    implements CommonPlugin_IPopulator, CommonPlugin_IExportable
{
    protected function caption()
    {
        $caption = parent::caption();
        $caption .= "\n" . $this->i18n->get('Link "%s"', $this->model->linkUrl());
        return $caption;
    }

    protected function prevNext()
    {
        list($prev, $next) = $this->model->prevNextForwardId();
        return array('forwardid', $prev, $next);
    }
    /*
     * Implementation of CommonPlugin_IExportable
     */
    public function exportFileName()
    {
        return parent::exportFileName() . '_' .  preg_replace(
            array('|^http://|i', '/[^\w]/'),
            array('', '_'),
            $this->model->linkUrl()
        );
    }

    public function exportFieldNames()
    {
        $fields = array();
        $fields[] = $this->i18n->get('User email');

        foreach ($this->model->selectedAttrs as $attr) {
            $fields[] = $this->model->attributes[$attr]['name'];
        }
        $fields[] = $this->i18n->get('clicks');
        $fields[] = $this->i18n->get('firstclick');
        $fields[] = $this->i18n->get('latestclick');
        return $fields;
    }

    public function exportRows()
    {
        return $this->model->linkClicks();
    }

    public function exportValues(array $row)
    {
        $values = array();
        $values[] = $row['email'];

        foreach ($this->model->selectedAttrs as $attr) {
            $values[] = $row["attr{$attr}"];
        }
        $values[] = $row['clicked'];
        $values[] = $row['firstclick'];
        $values[] = $row['clicked'] > 1 ? $row['latestclick'] : '';
        return $values;
    }

    /*
     * Implementation of CommonPlugin_IPopulator
     */
    public function populate(WebblerListing $w, $start, $limit)
    {
        /*
         * Populates the webbler list with link click details
         */
        $w->setTitle($this->i18n->get('User email'));
        $resultSet = $this->model->linkClicks($start, $limit);

        foreach ($resultSet as $row) {
            $key = $row['email'];
            $w->addElement($key,  new CommonPlugin_PageURL('userhistory', array('id' => $row['id'])));

            foreach ($this->model->selectedAttrs as $attr) {
                $w->addColumn($key, $this->model->attributes[$attr]['name'], $row["attr{$attr}"]);
            }
            $w->addColumn($key,    $this->i18n->get('clicks'), $row['clicked']);
            $w->addColumn($key, $this->i18n->get('firstclick'), $row['firstclick']);
            $w->addColumn($key, $this->i18n->get('latestclick'),
                $row['clicked'] > 1 ? $row['latestclick'] : ''
            );
        }
    }

    public function total()
    {
        return $this->model->totalLinkClicks();
    }
}
