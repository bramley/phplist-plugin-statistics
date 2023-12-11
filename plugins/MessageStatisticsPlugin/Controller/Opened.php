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
use phpList\plugin\Common\ImageTag;

class MessageStatisticsPlugin_Controller_Opened extends MessageStatisticsPlugin_Controller implements phpList\plugin\Common\IPopulator, phpList\plugin\Common\IExportable
{
    /*
     * Implementation of phpList\plugin\Common\IExportable
     */
    public function exportRows()
    {
        return $this->model->fetchMessageOpens();
    }

    public function exportFieldNames()
    {
        $fields = array($this->i18n->get('subscriber'));

        foreach ($this->model->selectedAttrs as $attr) {
            $fields[] = $this->model->attributes[$attr]['name'];
        }
        $fields[] = $this->i18n->get('status');
        $fields[] = $this->i18n->get('latest view');
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

        if ($row['blacklisted']) {
            $status = $this->i18n->get('blacklisted');
        } elseif (!$row['confirmed']) {
            $status = $this->i18n->get('unconfirmed');
        } else {
            $status = '';
        }
        $values[] = $status;
        $values[] = $row['latest_view'];
        $values[] = $row['viewed'];
        $values[] = $row['total_views'];
        $values[] = $row['links_clicked'];
        $values[] = $row['total_clicks'];

        return $values;
    }

    /*
     * Implementation of phpList\plugin\Common\IPopulator
     */

    public function populate(WebblerListing $w, $start, $limit)
    {
        /*
         * Populate the webbler list with users who have opened the message
         */
        $w->setElementHeading($this->i18n->get('subscriber'));
        $rows = $this->model->fetchMessageOpens($start, $limit);

        foreach ($rows as $row) {
            $key = $row['email'];
            $w->addElement($key, new phpList\plugin\Common\PageURL('user', array('id' => $row['userid'])));

            foreach ($this->model->selectedAttrs as $attr) {
                $w->addColumn($key, $this->model->attributes[$attr]['name'], $row["attr{$attr}"]);
            }
            if ($row['blacklisted']) {
                $status = new ImageTag('user.png', $this->i18n->get('blacklisted'));
            } elseif (!$row['confirmed']) {
                $status = new ImageTag('no.png', $this->i18n->get('unconfirmed'));
            } else {
                $status = '';
            }
            $w->addColumnHtml($key, $this->i18n->get('status'), $status);
            $w->addColumn($key, $this->i18n->get('latest view'), $row['latest_view'] ? formatDateTime($row['latest_view']) : '');
            $w->addColumn($key, $this->i18n->get('first view'), formatDateTime($row['viewed']));
            $url = new phpList\plugin\Common\PageURL(
                null, ['type' => 'userviews', 'userid' => $row['userid'], 'msgid' => $this->model->msgid]
            );
            $w->addColumn($key, $this->i18n->get('total views'), $row['total_views'], $url);
            $url = new phpList\plugin\Common\PageURL(
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
        return $this->model->totalMessageOpens();
    }

    protected function filterForm()
    {
        $minimumViews = CHtml::textField('SearchForm[minViews]', $this->model->minViews);
        $minimumClicks = CHtml::textField('SearchForm[minClicks]', $this->model->minClicks);
        $minimumViewsCaption = $this->i18n->get('Minimum views');
        $minimumClicksCaption = $this->i18n->get('Minimum links clicked');

        $action = $_SERVER['REQUEST_URI'];
        $form = <<<END
<form action="$action" method="POST">
    <label>$minimumViewsCaption</label> $minimumViews
    <label>$minimumClicksCaption</label> $minimumClicks
    <input type="submit" name="SearchForm[submit]" value="{$this->i18n->get('Submit')}" />
</form>
END;
        $panel = new UIPanel('', $form);

        return $panel->display();
    }
}
