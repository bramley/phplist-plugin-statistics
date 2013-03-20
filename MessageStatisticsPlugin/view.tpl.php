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
 * @version   SVN: $Id: view.tpl.php 1235 2013-03-17 15:45:44Z Duncan $
 * @link      http://forums.phplist.com/viewtopic.php?f=7&t=35427
 */


/**
 * Template for the message statistics page
 * 
 * @category  phplist
 * @package   MessageStatisticsPlugin
 */

/**
 *
 * Available fields
 * - $tabs: WebblerTabs
 * - $exception: exception text
 * - $help: help link
 * - $caption: text
 * - $download: download link
 * - $chartURL: URL for displaying a chart
 * - $chartMessage: error message to replace a chart
 * - $form: attribute search/select form
 * - $listing: HTML result of CommonPlugin_Listing
 */
?>
<div>
	<h4 id='top'><?php echo $this->i18n->get('plugin_title'); ?></h4>
<style type="text/css">
/*
 * Fix to make tabs wrap-around neatly
 */
#webblertabs li {
	height: 21px;
}
</style>
<div>
	<h3 id='top'><?php echo $this->i18n->get('plugin_title'); ?></h3>
	<hr />
<?php if (isset($toolbar)) echo $toolbar; ?>
	<div style='padding-top: 10px;' >
<?php if (isset($tabs)) echo $tabs; ?>
    </div>
    <div style='padding-top: 10px;' >
<?php if (isset($exception)) echo $exception; ?>
<?php if (isset($caption)) echo nl2br(htmlspecialchars($caption)); ?>
    </div>
    <div style='padding-top: 10px;'>
<?php if (isset($chartURL)): ?>
		<img src='<?php echo $chartURL; ?>' width='600'  height='300' />
<?php endif; ?>
<?php if (isset($chartMessage)): ?>
		<p><?php echo $chartMessage; ?></p>
<?php endif; ?>
<?php if (isset($form)) echo $form; ?>
<?php if (isset($listing)): ?>
	<?php echo $listing; ?>
		<p><a href='#top'>[<?php echo $this->i18n->get('top'); ?>]</a></p>
<?php endif; ?>
    </div>
</div>
