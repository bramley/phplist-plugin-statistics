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
 * Template for the message statistics page.
 *
 * @category  phplist
 */

/**
 * Available fields
 * - $toolbar: toolbar
 * - $tabs: WebblerTabs
 * - $exception: exception text
 * - $caption: text
 * - $chart: chart
 * - $form: attribute search/select form
 * - $listing: HTML result of CommonPlugin_Listing.
 */
?>
<div id='top'>
    <hr />
<?php if (isset($toolbar)) {
    echo $toolbar;
} ?>
    <div style='padding-top: 10px;' >
<?php if (isset($tabs)) {
    echo $tabs;
} ?>
    </div>
    <div style='padding-top: 10px;' >
<?php if (isset($exception)) {
    echo $exception;
} ?>
<?php if (isset($caption)) {
    echo nl2br(htmlspecialchars($caption));
} ?>
    </div>
    <div style='padding-top: 10px;'>
<?php if (isset($chart)): ?>
    <?php echo $chart; ?>
        <div id="<?php echo $chart_div; ?>" 
            style="width: 100%; height: <?php echo MessageStatisticsPlugin_Controller_Messages::IMAGE_HEIGHT; ?>px;">
        </div>
<?php endif; ?>
<?php if (isset($form)) {
    echo $form;
} ?>
    </div>
<?php if (isset($listing)): ?>
    <div style='padding-top: 10px;'>
    <?php echo $listing; ?>
        <p><a href='#top'>[<?php echo $this->i18n->get('top'); ?>]</a></p>
    </div>
<?php endif; ?>
</div>
