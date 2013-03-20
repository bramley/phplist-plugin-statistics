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
 * @version   SVN: $Id: Domain.php 574 2012-02-02 14:01:11Z Duncan $
 * @link      http://forums.phplist.com/viewtopic.php?f=7&t=35427
 */

/**
 * Sub-class that provides the populator and exportable functions 
 * for domains to which the message was sent
 * 
 * @category  phplist
 * @package   MessageStatisticsPlugin
 */
 
class MessageStatisticsPlugin_Controller_Domain 
	extends MessageStatisticsPlugin_Controller
	implements CommonPlugin_IPopulator, CommonPlugin_IExportable
{
	/*
	 * Implementation of CommonPlugin_IExportable
	 */
	public function exportRows()
	{
		return $this->model->messageByDomain();
	}

	public function exportFieldNames()
	{
		$fields = $this->i18n->get(array('domain', 'sent', 'opened', 'clicked'));
		return $fields;
	}

	public function exportValues(array $row)
	{
		$values = array();
		$values[] = $row['domain'];
		$values[] = $row['sent'];
		$values[] = $row['opened'];
		$values[] = $row['clicked'];

		return $values;
	}

	/*
	 * Implementation of CommonPlugin_IPopulator
	 */
	public function populate(WebblerListing $w, $start, $limit)
	{
		/*
		 * Populate the webbler list with domains
		 */
		$w->setTitle($this->i18n->get('Domain'));
		$resultSet = $this->model->messageByDomain($start, $limit);

		foreach ($resultSet as $row) {
			$key = $row['domain'];
			$w->addElement($key, null);
			$w->addColumn($key, $this->i18n->get('sent'), $row['sent']);
			$w->addColumn($key, $this->i18n->get('opened'), $row['opened']);
			$w->addColumn($key, $this->i18n->get('clicked'), $row['clicked']);
		}
	}

	public function total()
	{
		/*
		 * Returns the total number of records to be displayed
		 */
		return $this->model->totalMessageByDomain();
	}
}
