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
 * for links.
 *
 * @category  phplist
 */
use phpList\plugin\Common\IExportable;

class MessageStatisticsPlugin_Controller_Links extends MessageStatisticsPlugin_Controller implements phpList\plugin\Common\IPopulator, phpList\plugin\Common\IExportable
{
    protected $itemsPerPage = array(array(15, 25), 15);

    private $totalSent;

    /*
     * Implementation of phpList\plugin\Common\IExportable
     */
    public function exportFieldNames()
    {
        return $this->i18n->get(array(
            'URL', 'subscribers', 'subscribers %', 'total clicks', 'firstclick', 'latestclick',
        ));
    }

    public function exportRows()
    {
        return $this->model->links();
    }

    public function exportValues(array $row)
    {
        return array(
            $row['url'],
            $row['usersclicked'],
            $this->calculateRate($row['usersclicked'], $this->totalSent),
            $row['numclicks'],
            $row['firstclick'],
            $row['numclicks'] > 1 ? $row['latestclick'] : '',
        );
    }

    /*
     * Implementation of phpList\plugin\Common\IPopulator
     */
    public function populate(WebblerListing $w, $start, $limit)
    {
        /*
         * Populates the webbler list with link details
         */
        $w->setElementHeading('URL');
        $totalSent = $this->model->messageTotalSent();
        $resultSet = $this->model->links($start, $limit);
        $query = array(
            'listid' => $this->model->listid,
            'msgid' => $this->model->msgid,
            'type' => 'linkclicks',
        );

        foreach ($resultSet as $row) {
            $key = preg_replace('%^(http|https)://%i', '', $row['url']);

            if (strlen($key) > 39) {
                $key = htmlspecialchars(substr($key, 0, 22)) . '&nbsp;...&nbsp;' . htmlspecialchars(substr($key, -12));
            }
            $key = sprintf('<span title="%s">%s</span>', htmlspecialchars($row['url']), $key);
            $query['forwardid'] = $row['forwardid'];
            $w->addElement($key, new phpList\plugin\Common\PageURL(null, $query));
            $destinationLink = CHtml::tag(
                'a',
                ['target' => '_blank', 'href' => $row['url'], 'class' => 'nobutton', 'title' => $row['url']],
                new phpList\plugin\Common\ImageTag('external.png', '')
            );
            $w->addColumnHtml(
                $key,
                $this->i18n->get('Link'),
                $row['personalise']
                    ? new phpList\plugin\Common\ImageTag('user.png', 'URL is personalised')
                    : $destinationLink
            );
            $w->addColumn(
                $key,
                $this->i18n->get('subscribers'),
                $row['usersclicked'] > 0
                    ? sprintf('%d (%s%%)', $row['usersclicked'], $this->calculateRate($row['usersclicked'], $totalSent))
                    : 0
            );
            $w->addColumn($key, $this->i18n->get('total clicks'), $row['numclicks']);
            $w->addColumn($key, $this->i18n->get('firstclick'), formatDateTime($row['firstclick']));
            $w->addColumn($key, $this->i18n->get('latestclick'), $row['numclicks'] > 1 ? formatDateTime($row['latestclick']) : '');
        }
    }

    public function total()
    {
        return $this->model->totalLinks();
    }

    protected function actionExportCSV(IExportable $exportable = null)
    {
        $this->totalSent = $this->model->messageTotalSent();

        parent::actionExportCSV($exportable);
    }
}
