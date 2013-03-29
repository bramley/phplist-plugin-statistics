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
	implements CommonPlugin_IPopulator, CommonPlugin_IExportable, CommonPlugin_IChartable
{
	const IMAGE_DIR = 'images/';

	const IMAGE_WIDTH = 600;
	const IMAGE_HEIGHT = 300;
	const LEFT_MARGIN = 50;
	const RIGHT_MARGIN = 20;
	const MAX_BAR_WIDTH = 40;
	const BAR_GAP = 2;
	const EXCLUDE_REGEX = 'p=preferences|p=unsubscribe|phplist.com';

    private $messageResults;
	protected $itemsPerPage = array(array(10, 25), 10);

	/*
	 * Private methods
	 */
	private static function image($image, $alt)
	{
		return CHtml::image(self::IMAGE_DIR . $image, $alt, array('title' => $alt));
	}

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
	/*
	 * Implementation of CommonPlugin_IChartable
	 */
	public function chartParameters()
	{
		$chartStatistics = array();

		foreach ($this->messageResults as $row) {
			$fields = $this->messageStats($row);
			$chartStatistics['ID'][] = $fields['id'];
			$chartStatistics['sent'][] = $fields['sent'];
			$chartStatistics['opened'][] = $fields['opens'];
			$chartStatistics['clicked'][] = $fields['clickUsers'];
			$chartStatistics['bounced'][] = $fields['bouncecount'];
		}
		$barWidth = min(
			self::MAX_BAR_WIDTH,
			intval((self::IMAGE_WIDTH - self::LEFT_MARGIN - self::RIGHT_MARGIN) / count($chartStatistics['ID'])) - self::BAR_GAP
		);

		return array(
			'cht' => 'bvg',
			'chs' => sprintf('%dx%d', self::IMAGE_WIDTH, self::IMAGE_HEIGHT),
			'chbh' => sprintf('%d,%d,%d', $barWidth, self::BAR_GAP, self::BAR_GAP),
			'chd' => sprintf(
				't1:%s|%s|%s|%s',
				implode(',', $chartStatistics['sent']), implode(',', $chartStatistics['opened']),
				implode(',', $chartStatistics['clicked']), implode(',', $chartStatistics['bounced'])
			),
			'chds' => 'a',
			'chdl' => implode('|', $this->i18n->getUtf8(array('Sent', 'Opened', 'Clicked', 'Bounced'))),
			'chdlp' => 'b|l',
			'chf' => 'bg,s,EFEFEF',
			'chm' => 'D,00FF00,1,0,2|D,0000FF,2,0,2|D,B22222,3,0,2',
			'chma' => sprintf('%d,%d,20,20', self::LEFT_MARGIN, self::RIGHT_MARGIN),
			'chco' => '00FFFF,00FF00,0000FF,B22222',
			'chxt' => 'x,x,y,y',
			'chxl' => sprintf(
				'0:|%s|1:|%s|3:|%s',
				 implode('|', $chartStatistics['ID']), $this->i18n->getUtf8('Message ID'), $this->i18n->getUtf8('Users')
			),
			'chxp' => sprintf('1,50|3,%d', max($chartStatistics['sent']) / 2),
			'chxtc' => sprintf('2,%d', -self::IMAGE_WIDTH),
		);
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

		$w->setTitle(sprintf('%s | %s', $this->i18n->get('ID'), $this->i18n->get('Subject')));

		$rows = iterator_to_array($this->model->fetchMessages(false, $start, $limit));
        $this->messageResults = array_reverse($rows);
		$query = array('listid' => $this->model->listid, 'type' => 'opened');

		foreach ($rows as $row) {
			$fields = $this->messageStats($row);
			$query['msgid'] = $fields['id'];
			$key = "$fields[id] | $fields[subject]";
			$w->addElement($key,  CommonPlugin_PageURL::create(null, $query));
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
                $w->addColumnHtml($key, $this->i18n->get('print'), self::image('doc_pdf.png', $this->i18n->get('print to PDF')),
                    CommonPlugin_PageURL::create(null, array('listid' => $this->model->listid, 'action' => 'print', 'msgid' => $fields['id']))
                );
		}
	}
	}

	public function total()
	{
		return $this->model->totalMessages();
	}
}
