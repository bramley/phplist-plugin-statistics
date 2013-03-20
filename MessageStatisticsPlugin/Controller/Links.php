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
 * @version   SVN: $Id: Links.php 1232 2013-03-16 10:17:11Z Duncan $
 * @link      http://forums.phplist.com/viewtopic.php?f=7&t=35427
 */

/**
 * Sub-class that provides the populator and exportable functions 
 * for links
 * 
 * @category  phplist
 * @package   MessageStatisticsPlugin
 */

class MessageStatisticsPlugin_Controller_Links 
	extends MessageStatisticsPlugin_Controller
	implements CommonPlugin_IPopulator, CommonPlugin_IExportable
{
	protected $itemsPerPage = array(array(15, 25), 15);
	/*
	 * Implementation of CommonPlugin_IExportable
	 */
	public function exportFieldNames()
	{
		return $this->i18n->get(array(
			'Link URL', 'clicks', 'users', 'users%', 'firstclick', 'latestclick'
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
			$row['numclicks'],
			$row['usersclicked'],
			sprintf('%0.2f', $row['usersclicked'] / $row['totalsent'] * 100),
			$row['firstclick'],
			$row['numclicks'] > 1 ? $row['latestclick'] : '',
		);
	}

	/*
	 * Implementation of CommonPlugin_IPopulator
	 */
	public function populate(WebblerListing $w, $start, $limit)
	{
		/*
		 * Populates the webbler list with link details
		 */
		$w->setTitle($this->i18n->get('Link URL'));

		$resultSet = $this->model->links($start, $limit);
		$query = array(
			'listid' => $this->model->listid,
			'msgid' => $this->model->msgid,
			'type' => 'linkclicks',
		);

		foreach ($resultSet as $row) {
            $key = preg_replace('%^(http|https)://%', '', $row['url']);

            if (strlen($key) > 39) {
                $key = substr_replace($key, '&nbsp;...&nbsp;', 22, strlen($key) - (22 + 12));
            }

			$query['forwardid'] = $row['forwardid'];
			$w->addElement($key, CommonPlugin_PageURL::create(null, $query));
			$w->addColumn($key, $this->i18n->get('clicks'), $row['numclicks']);
			$w->addColumn($key, $this->i18n->get('users'),
				$row['usersclicked'] > 0 
					? sprintf('%d (%0.2f%%)', $row['usersclicked'], $row['usersclicked'] / $row['totalsent'] * 100) 
					: ''
			);
			$w->addColumn($key, $this->i18n->get('firstclick'), $row['firstclick']);
			$w->addColumn($key, $this->i18n->get('latestclick'), 
				$row['numclicks'] > 1 ? $row['latestclick'] : ''
			);
		}
	}

	public function total()
	{
		return $this->model->totalLinks();
	}
}
