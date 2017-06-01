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
 * Registers the plugin with phplist.
 *
 * @category  phplist
 */
class MessageStatisticsPlugin extends phplistPlugin
{
    const VERSION_FILE = 'version.txt';

    /*
     *  Inherited variables
     */
    public $name = 'Campaign Statistics';
    public $enabled = true;
    public $authors = 'Duncan Cameron';
    public $description = 'Provides statistics on opens, clicks, bounces, forwards, and links of sent campaigns.';
    public $topMenuLinks = array(
        'main' => array('category' => 'statistics'),
    );
    public $pageTitles = array(
        'main' => 'Advanced Statistics',
    );
    public $documentationUrl = 'https://resources.phplist.com/plugin/campaignstatistics';

    public function adminmenu()
    {
        return array(
            'main' => 'Advanced Statistics',
        );
    }

    public function __construct()
    {
        $this->coderoot = dirname(__FILE__) . '/MessageStatisticsPlugin/';
        $this->version = (is_file($f = $this->coderoot . self::VERSION_FILE))
            ? file_get_contents($f)
            : '';
        $this->settings = array(
            'statistics_export_all_messages' => array(
                'value' => true,
                'description' => s("On the Campaigns tab, whether to export all campaigns. If 'No' then only those currently listed will be exported."),
                'type' => 'boolean',
                'allowempty' => true,
                'category' => 'Campaign Statistics',
            ),
        );
        parent::__construct();
    }

    public function dependencyCheck()
    {
        global $plugins;

        return array(
            'Common Plugin v3.6.3 or later installed' => phpListPlugin::isEnabled('CommonPlugin')
                    && preg_match('/\d+\.\d+\.\d+/', $plugins['CommonPlugin']->version, $matches)
                    && version_compare($matches[0], '3.6.3') >= 0,
            'PHP version 5.4.0 or greater' => version_compare(PHP_VERSION, '5.4') > 0,
        );
    }
}
