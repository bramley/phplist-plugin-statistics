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
 * @copyright 2011-2021 Duncan Cameron
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, Version 3
 */

/**
 * Sub-class that provides the populator and exportable functions
 * for messages.
 *
 * @category  phplist
 */
class MessageStatisticsPlugin_Controller_Messages extends MessageStatisticsPlugin_Controller implements CommonPlugin_IPopulator, CommonPlugin_IExportable
{
    const IMAGE_HEIGHT = 300;

    private $messageResults;
    protected $itemsPerPage = array(array(5, 10, 25), 5);

    /*
     * Format a short date allowing the month to be translated.
     * html decode the result because some translations can already be html encoded.
     *
     * @param string $date date in yyyy/mm/dd format
     *
     * @return string
     */
    private function formatShortDate($date)
    {
        return html_entity_decode(formatDate($date, true));
    }

    private function messageStats(array $row)
    {
        $sent = $row['sent'];
        $opens = $row['openUsers'];
        $delivered = $row['sent'] - $row['bouncecount'];

        return array(
            'id' => $row['id'],
            'subject' => MessageStatisticsPlugin_Model::useSubject($row),
            'campaigntitle' => $row['campaigntitle'],
            'from' => $row['from'],
            'datesent' => $row['end'] != '' ? $this->formatShortDate($row['end']) : '',
            'datestart' => $row['start'] != '' ? $this->formatShortDate($row['start']) : '',
            'sent' => $sent,
            'delivered' => $delivered,
            'deliveredrate' => $sent > 0 ? sprintf('%1.1f', $delivered / $sent * 100) : 0,
            'opens' => $opens,
            'openrate' => $delivered > 0 ? sprintf('%1.1f', $opens / $delivered * 100) : 0,
            'unopens' => $delivered - $opens,
            'unopenrate' => $delivered > 0 ? sprintf('%1.1f', ($delivered - $opens) / $delivered * 100) : 0,
            'clickUsers' => $row['clickUsers'],
            'clickrate' => $sent > 0 ? sprintf('%1.1f', $row['clickUsers'] / $sent * 100) : 0,
            'totalClicks' => $row['totalClicks'],
            'clickopenrate' => $opens > 0 ? sprintf('%1.1f', $row['clickUsers'] / $opens * 100) : 0,
            'bouncecount' => $row['bouncecount'],
            'bouncerate' => $sent > 0 ? sprintf('%1.1f', $row['bouncecount'] / $sent * 100) : 0,
            'forwardcount' => $row['forwardcount'],
            'forwardrate' => $opens > 0 ? sprintf('%1.1f', $row['forwardcount'] / $opens * 100) : 0,
            'viewed' => $row['viewed'],
            'avgviews' => $opens > 0 ? sprintf('%1.1f', $row['viewed'] / $opens) : 0,
        );
    }

    protected function actionPrint()
    {
        $this->model->validateProperties();
        $regex = getConfig('statistics_exclude_regex');
        $fields = $this->messageStats($this->model->fetchMessage($regex));

        $report = new MessageStatisticsPlugin_CampaignReport($this->i18n);
        $report->create($fields);
    }

    protected function caption()
    {
        return $this->model->listNames
            ? sprintf($this->i18n->get('Messages sent to %s'), "\"{$this->model->listNames[0]}\"")
            : $this->i18n->get('All sent messages');
    }

    protected function prevNext()
    {
        return null;
    }

    protected function createChart($chartDiv)
    {
        if (count($this->messageResults) == 0) {
            return '';
        }
        $chart = new Chart('ComboChart');
        $data = array();

        foreach ($this->messageResults as $row) {
            $data[] = array(
                'ID' => $row['id'],
                $this->i18n->get('Sent') => (int) $row['sent'],
                $this->i18n->get('Opened') => (int) $row['openUsers'],
                $this->i18n->get('Clicked') => (int) $row['clickUsers'],
                $this->i18n->get('Bounced') => (int) $row['bouncecount'],
            );
        }

        $chart->load($data, 'array');
        $options = array(
            'height' => self::IMAGE_HEIGHT,
            'axisTitlesPosition' => 'out',
            'vAxis' => array('title' => $this->i18n->get('Subscribers'), 'gridlines' => array('count' => 10), 'logScale' => false, 'format' => '#'),
            'hAxis' => array('title' => $this->i18n->get('Campaigns')),
            'seriesType' => 'line',
            'series' => array(0 => array('type' => 'bars')),
            'legend' => array('position' => 'bottom'),
            'colors' => array('blue', 'green', 'yellow', 'red'),
        );
        $baseUrl = new CommonPlugin_PageURL(null, ['listid' => $this->model->listid]);
        $clickLocationFormat = <<<'END'
function(data, selectedItem) {
    row = selectedItem.row;
    column = selectedItem.column;
    type = column == 3 ? 'clicked' : (column == 4 ? 'bounced' : 'opened');

    return '%s' + '&type=' + type + '&msgid=' + data.getValue(row, 0);
}
END;
        $clickLocation = sprintf($clickLocationFormat, $baseUrl);
        $result = $chart->draw($chartDiv, $options, $clickLocation);

        return $result;
    }

    /**
     * Construct the form to allow selection of campaigns by date range and list.
     *
     * @return string
     */
    protected function campaignSelectForm()
    {
        $fromDatePicker = CHtml::textField(
            'fromdate',
            $this->model->fromdate,
            ['class' => 'flatpickr']
        );
        $fromCaption = $this->i18n->get('From');

        $toDatePicker = CHtml::textField(
            'todate',
            $this->model->todate,
            ['class' => 'flatpickr']
        );
        $toCaption = $this->i18n->get('To');

        $lists = iterator_to_array($this->model->listsForOwner());
        $listsDropDown = CHtml::dropDownList(
            'listid',
            $this->model->listid,
            array_column($lists, 'name', 'id')
        );
        $listsCaption = $this->i18n->get('List');
        $action = $_SERVER['REQUEST_URI'];
        $form = <<<END
<form action="$action" method="POST">
    <label>$listsCaption</label> $listsDropDown
    <label>$fromCaption</label> $fromDatePicker
    <label>$toCaption</label> $toDatePicker
    <input type="submit" name="date_submit" value="{$this->i18n->get('Submit')}" />
</form>
END;
        $panel = new UIPanel('', $form);

        return $panel->display();
    }

    /**
     * Display a webbler listing containing one line of summary totals.
     *
     * @param string $listName list name
     * @param string $from     from date
     * @param string $to       to date
     *
     * @return string
     */
    protected function summary($listName, $from, $to)
    {
        $totalCampaigns = 0;
        $totalSent = 0;
        $totalOpened = 0;
        $totalClicked = 0;
        $totalBounced = 0;
        $totalViews = 0;

        foreach ($this->messageResults as $row) {
            ++$totalCampaigns;
            $totalSent += $row['sent'];
            $totalOpened += $row['openUsers'];
            $totalClicked += $row['clickUsers'];
            $totalBounced += $row['bouncecount'];
            $totalViews += $row['viewed'];
        }
        $delivered = $totalSent - $totalBounced;

        $w = new phpList\plugin\Common\WebblerListing();
        $w->title = $this->i18n->get('Summary of %s list from %s to %s', $listName, $from, $to);
        $w->setElementHeading($this->i18n->get('# campaigns'));
        $key = $totalCampaigns;
        $w->addElement($key);
        $w->addColumn($key, $this->i18n->get('sent'), number_format($totalSent));
        $openRate = $delivered > 0 ? $totalOpened * 100 / $delivered : 0;
        $w->addColumn($key, $this->i18n->get('opened'), sprintf('(%1.1f%%) %s', $openRate, number_format($totalOpened)));
        $clickRate = $totalSent > 0 ? $totalClicked * 100 / $totalSent : 0;
        $w->addColumn($key, $this->i18n->get('clicked'), sprintf('(%1.1f%%) %s', $clickRate, number_format($totalClicked)));
        $bounceRate = $totalSent > 0 ? $totalBounced * 100 / $totalSent : 0;
        $w->addColumn($key, $this->i18n->get('bounced'), sprintf('(%1.1f%%) %s', $bounceRate, number_format($totalBounced)));
        $w->addColumn($key, $this->i18n->get('views'), number_format($totalViews));

        return $w->display();
    }

    /*
     * Implementation of CommonPlugin_IExportable
     */
    public function exportFieldNames()
    {
        return $this->i18n->get(array(
            'id', 'subject', 'date', 'sent', 'opened', 'opened %',
            'clicked', 'clicked %', 'clicks_total', 'click_open', 'bounced', 'bounced %', 'total views', 'avg views',
        ));
    }

    /**
     * Return the rows to be exported
     * If the page url includes the start parameter then only the current page is to be exported
     * Otherwise all rows are to be exported.
     *
     * @return Iterator
     */
    public function exportRows()
    {
        if (isset($_GET['start'])) {
            $start = $_GET['start'];
            $limit = $_GET['limit'];
            $asc = false;
        } else {
            $start = $limit = null;
            $asc = true;
        }

        return $this->model->fetchMessages($asc, $start, $limit);
    }

    public function exportValues(array $row)
    {
        $row = $this->messageStats($row);

        return array(
            $row['id'], $row['subject'], $row['datesent'], $row['sent'], $row['opens'], $row['openrate'],
            $row['clickUsers'], $row['clickrate'], $row['totalClicks'], $row['clickopenrate'],
            $row['bouncecount'], $row['bouncerate'], $row['viewed'], $row['avgviews'],
        );
    }

    /*
     * Implementation of CommonPlugin_IPopulator
     */
    public function populate(WebblerListing $w, $start, $limit)
    {
        /*
         * Populates the webbler list with message details
         */
        $w->setElementHeading($this->i18n->get('Campaign'));

        $rows = iterator_to_array($this->model->fetchMessages(false, $start, $limit));
        $this->messageResults = array_reverse($rows);
        $query = array('listid' => $this->model->listid, 'type' => 'opened');

        foreach ($rows as $row) {
            $fields = $this->messageStats($row);
            $query['msgid'] = $fields['id'];
            $key = "$fields[id] | $fields[subject]";
            $w->addElement($key, new CommonPlugin_PageURL(null, $query));
            $w->addColumn($key, $this->i18n->get('date'), $fields['datesent']);
            $w->addColumn($key, $this->i18n->get('sent'), $fields['sent'], '');
            $w->addColumn($key, $this->i18n->get('opened'), "{$fields['openrate']}% ({$fields['opens']})");
            $w->addColumnHtml($key, $this->i18n->get('clicked'),
                $fields['clickUsers'] > 0 ? "{$fields['clickrate']}%&nbsp;({$fields['clickUsers']}) |&nbsp;{$fields['totalClicks']} |&nbsp;{$fields['clickopenrate']}%" : '0'
            );
            $w->addColumn($key, $this->i18n->get('bounced'),
                $fields['bouncecount'] > 0 ? "{$fields['bouncerate']}% ({$fields['bouncecount']})" : '0'
            );
            $w->addColumn($key, $this->i18n->get('views'), "{$fields['viewed']} ({$fields['avgviews']})");
            $w->addColumnHtml(
                $key,
                $this->i18n->get('print'),
                new CommonPlugin_ImageTag('doc_pdf.png', $this->i18n->get('print to PDF')),
                new CommonPlugin_PageURL(null, array('listid' => $this->model->listid, 'action' => 'print', 'msgid' => $fields['id']))
            );
        }
    }

    public function total()
    {
        return $this->model->totalMessages();
    }
}
