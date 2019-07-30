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
 * for messages.
 *
 * @category  phplist
 */
class MessageStatisticsPlugin_Controller_Messages extends MessageStatisticsPlugin_Controller implements CommonPlugin_IPopulator, CommonPlugin_IExportable
{
    const IMAGE_HEIGHT = 300;
    const EXCLUDE_REGEX = 'p=preferences|p=unsubscribe|phplist.com';

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
            'datesent' => $this->formatShortDate($row['end']),
            'datestart' => $this->formatShortDate($row['start']),
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

    /*
     * Protected methods
     */
    protected function actionPrint()
    {
        global $wkhtmltopdfOptions;
        global $tmpdir;

        $this->model->validateProperties();
        $listNames = implode(', ', $this->model->listNames);
        $regex = isset($wkhtmltopdfOptions['exclude'])
            ? ($wkhtmltopdfOptions['exclude']
                ? $wkhtmltopdfOptions['exclude']
                : uniqid())
            : self::EXCLUDE_REGEX;
        $fields = $this->messageStats($this->model->fetchMessage($regex));
        $params = array(
            'from' => $fields['from'],
            'list' => $listNames,
            'subject' => $fields['subject'],
            'start' => $fields['datestart'],
            'end' => $fields['datesent'],
            'sent' => number_format($fields['sent']),
            'delivered' => number_format($fields['delivered']),
            'deliveredrate' => $fields['deliveredrate'] . '%',
            'bounced' => number_format($fields['bouncecount']),
            'bouncerate' => $fields['bouncerate'] . '%',
            'opened' => number_format($fields['opens']),
            'openrate' => $fields['openrate'] . '%',
            'unopened' => number_format($fields['unopens']),
            'unopenrate' => $fields['unopenrate'] . '%',
            'clicked' => number_format($fields['clickUsers']),
            'clickopenrate' => $fields['clickopenrate'] . '%',
            'totalClicks' => $fields['totalClicks'],
            'forwarded' => number_format($fields['forwardcount']),
            'forwardedrate' => $fields['forwardrate'] . '%',
        );

        if ($fields['campaigntitle'] != $fields['subject']) {
            $params['campaigntitle'] = $fields['campaigntitle'];
        }
        $w = new CommonPlugin_HtmlToPdf();
        $fileName = preg_replace('/[^\w]+/', '_', $fields['subject']) . '.pdf';

        $defaultOptions = array(
            'tmp' => $tmpdir,
            'enableEscaping' => false,
            'header-spacing' => 5,
            'footer-spacing' => 2,
            'margin-top' => 30,
            'encoding' => 'utf-8',
        );

        $options = $wkhtmltopdfOptions + $defaultOptions;
        unset($options['exclude']);
        $w->setOptions($options);

        $logoPath = getConfig('statistics_logo_path');
        $imageSrc = sprintf('data:image/png;base64,%s', file_get_contents($logoPath));
        $w->headerHtml($this->render(dirname(__FILE__) . '/../printheader.tpl.php', array('imageSrc' => $imageSrc)));
        $w->footerHtml($this->render(dirname(__FILE__) . '/../printfooter.tpl.php', array()));
        $w->addPage($this->render(dirname(__FILE__) . '/../print.tpl.php', $params));

        $content = ob_get_clean();

        if ($w->send($fileName)) {
            exit;
        }
        echo $content;
        echo nl2br($w->getError());
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
        global $wkhtmltopdfOptions;

        $w->setTitle($this->i18n->get('Campaigns'));

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

            if (isset($wkhtmltopdfOptions) && is_executable($wkhtmltopdfOptions['bin'])) {
                $w->addColumnHtml($key, $this->i18n->get('print'), new CommonPlugin_ImageTag('doc_pdf.png', $this->i18n->get('print to PDF')),
                    new CommonPlugin_PageURL(null, array('listid' => $this->model->listid, 'action' => 'print', 'msgid' => $fields['id']))
                );
            }
        }
    }

    public function total()
    {
        return $this->model->totalMessages();
    }
}
