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
 * @version   SVN: $Id: Messages.php 1235 2013-03-17 15:45:44Z Duncan $
 * @link      http://forums.phplist.com/viewtopic.php?f=7&t=35427
 */

/**
 * Sub-class that provides the populator and exportable functions 
 * for messages
 * 
 * @category  phplist
 * @package   MessageStatisticsPlugin
 */

class MessageStatisticsPlugin_Controller_Messages 
    extends MessageStatisticsPlugin_Controller
    implements CommonPlugin_IPopulator, CommonPlugin_IExportable
{
    const IMAGE_HEIGHT = 300;
    const EXCLUDE_REGEX = 'p=preferences|p=unsubscribe|phplist.com';

    private $messageResults;
    protected $itemsPerPage = array(array(10, 25), 10);

    /*
     * Private methods
     */
    private function messageStats(array $row)
    {
        $sent = $row['sent'];
        $opens = $row['openUsers'];
        $delivered = $row['sent'] - $row['bouncecount'];

        return array(
            'id' => $row['id'],
            'subject' => $row['subject'],
            'from' => $row['from'],
            'datesent' => $row['end'],
            'datestart' => $row['start'],
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
        $listNames = $this->model->listid ? $this->model->listNames[0] : implode(', ', $this->model->listsForMessage());
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

        $w = new CommonPlugin_HtmlToPdf();
        $fileName = preg_replace('/[^\w]+/', '_', $fields['subject']) . '.pdf';
        $options = array(
            'bin' => $wkhtmltopdfOptions['bin'],
            'tmp' => isset($wkhtmltopdfOptions['tmp']) ? $wkhtmltopdfOptions['tmp'] : $tmpdir,
            'enableEscaping' => isset($wkhtmltopdfOptions['enableEscaping']) ? $wkhtmltopdfOptions['enableEscaping'] : false,
            'header-spacing' => 5,
            'footer-spacing' => 2,
            'margin-top' => 30,
        );

        if (isset($wkhtmltopdfOptions['options'])) {
            $options = $wkhtmltopdfOptions['options'] + $options;
        }

        $w->setOptions($options);
        $w->headerHtml($this->render(dirname(__FILE__) . '/../printheader.tpl.php', array()));
        $w->footerHtml($this->render(dirname(__FILE__) . '/../printfooter.tpl.php', array()));
        $w->addPage($this->render(dirname(__FILE__) . '/../print.tpl.php', $params));

        $content = ob_get_clean();

        if ((isset($wkhtmltopdfOptions['mode']) && $wkhtmltopdfOptions['mode'] == 'E') ? $w->send() : $w->send($fileName)) {
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
        $chart = new Chart('ComboChart');
        $data = array();
        
        foreach ($this->messageResults as $row) {
            $data[] = array(
                'ID' => $row['id'],
                'Sent' => (int) $row['sent'],
                'Opened' => (int) $row['openUsers'],
                'Clicked' => (int) $row['clickUsers'],
                'Bounced' => (int) $row['bouncecount']
            );
        };

        $chart->load($data, 'array');
        $options = array(
            'height' => self::IMAGE_HEIGHT,
            'axisTitlesPosition' => 'out',
            'vAxis' => array('title' => 'Subscribers', 'gridlines' => array('count' => 10), 'logScale' => true, 'format' => '#'),
            'hAxis' => array('title' => 'Campaign'),
            'seriesType' => 'line',
            'series' => array(0 => array('type' => 'bars')),
            'legend' => array('position' => 'bottom')
        );
        $result = $chart->draw($chartDiv, $options);
        return $result;
    }

    /*
     * Implementation of CommonPlugin_IExportable
     */
    public function exportFieldNames()
    {
        return $this->i18n->get(array(
            'ID', 'subject', 'date', 'sent', 'opened', 'opened %',
            'clicked', 'clicked %', 'clicks_total', 'click_open', 'bounced', 'bounced %', 'total views', 'avg views'
        ));
    }

    public function exportRows()
    {
        return $this->model->fetchMessages(true);
    }

    public function exportValues(array $row)
    {
        $row = $this->messageStats($row);
        return array(
            $row['id'], $row['subject'], $row['datesent'], $row['sent'], $row['opens'], $row['openrate'],
            $row['clickUsers'], $row['clickrate'], $row['totalClicks'], $row['clickopenrate'],
            $row['bouncecount'], $row['bouncerate'], $row['viewed'], $row['avgviews']
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
            $w->addElement($key,  new CommonPlugin_PageURL(null, $query));
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
