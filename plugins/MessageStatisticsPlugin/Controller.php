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
 * @version   SVN: $Id: Controller.php 1232 2013-03-16 10:17:11Z Duncan $
 * @link      http://forums.phplist.com/viewtopic.php?f=7&t=35427
 */

/**
 * This class is the controller for message statistics.
 * It is a base class providing common processing.
 * Sub-classes provide the populator and exportable functions for each type
 * 
 * @category  phplist
 * @package   MessageStatisticsPlugin
 */
 
 abstract class MessageStatisticsPlugin_Controller
    extends CommonPlugin_Controller
{
    /*
     *    Protected attributes
     *     Read by sub-classes
     */    
    protected $model;
    /*
     *     Written by sub-classes
     */    
    protected $showAttributeForm = false;
    protected $itemsPerPage = null;
    /*
     *    Private methods
     */
    private function navigation()
    {
        $types = array(
            'settings' => $this->i18n->get('tab_settings'),
            'lists' => $this->i18n->get('tab_lists'),
            'messages' => $this->i18n->get('tab_messages'),
            'opened' => $this->i18n->get('tab_opened'),
            'unopened' => $this->i18n->get('tab_unopened'),
            'clicked' => $this->i18n->get('tab_clicked'),
            'bounced' => $this->i18n->get('tab_bounced'),
            'forwarded' => $this->i18n->get('tab_forwarded'),
            'domain' => $this->i18n->get('tab_domains'),
            'links' => $this->i18n->get('tab_links'),
            'linkclicks' => $this->i18n->get('tab_linkclicks'),
        );
        $tabs = new CommonPlugin_Tabs();
        /*
         * Settings tab
         */
        $tabs->addTab($types['settings'],  new CommonPlugin_PageURL(null, array('type' => 'settings')));
        /*
         * Lists tab
         */
        $tabs->addTab($types['lists'],  new CommonPlugin_PageURL(null, array('type' => 'lists')));
        /*
         * Messages tab
         */
        $query = array();
        $query['listid'] = $this->model->listid;
        $query['type'] = 'messages';
        $tabs->addTab($types['messages'],  new CommonPlugin_PageURL(null, $query));
        /*
         * Opened -> Links tabs
         */
        $query['msgid'] = $this->model->msgid;
        foreach (array_slice($types, 3, 7) as $type => $title) {
            $query['type'] = $type;
            $tabs->addTab($title, new CommonPlugin_PageURL(null, $query));
        }
        /*
         * Link clicks tab
         */
        if ($this->model->type == 'linkclicks') {
            $query['forwardid'] = $this->model->forwardid;
            $query['type'] = 'linkclicks';
            $tabs->addTab($types['linkclicks'],  new CommonPlugin_PageURL(null, $query));
        }
        $tabs->setCurrent($types[$this->model->type]);
        return $tabs;
    }
    /*
     *    Protected methods
     */
     /**
     * Generate default caption
     * Intended to be overridden by subclasses
     * @return string 
     * @access protected
     */
    protected function caption()
    {
        return $this->i18n->get(
            'message %s sent to %s', 
            $this->model->msgid  . ' ' . '"' . $this->model->msgSubject . '"', 
            '"' . implode('", "', $this->model->listNames) . '"'
        );
    }

     /**
     * Create prev and next message values
     * Intended to be overridden by subclasses
     * @return array [0] parameter, [1] previous item, [2] next item
     * @access protected
     */
    protected function prevNext()
    {
        list($prev, $next) = $this->model->prevNextMessage();
        return array('msgid', $prev, $next);
    }

    protected function actionDefault()
    {
        global $google_chart_direct;
        
        try {
            if ($this->model->access == 'none') 
                throw new MessageStatisticsPlugin_NoAccessException();

            $this->model->validateProperties();
            $query = array(
                'listid' => $this->model->listid,
                'msgid' => $this->model->msgid,
                'type' => $this->model->type,
            );

            if (isset($_POST['SearchForm'])) {
                $this->model->setProperties($_POST['SearchForm'], true);
                $redirect = new CommonPlugin_PageURL(null, $query);
                header("Location: $redirect");
                exit;
            }

            $params = array();
            $toolbar = new CommonPlugin_Toolbar($this);

            if ($this instanceof CommonPlugin_IExportable) {
                $toolbar->addExportButton($query);
            }
            $toolbar->addHelpButton($this->model->type);
            $params['toolbar'] = $toolbar->display();
            $params['tabs'] = $this->navigation()->display();
            $params['caption'] = $this->caption();

            if ($this instanceof CommonPlugin_IPopulator) {
            
                $listing = new CommonPlugin_Listing($this, $this);

                if ($this->itemsPerPage) {
                    $listing->pager->setItemsPerPage($this->itemsPerPage[0], $this->itemsPerPage[1]);
                }

                if ($r = $this->prevNext()) {
                    $listing->pager->setPrevNext($r[0], $r[1], $r[2]);
                }
                $params['listing'] = $listing->display();
            }

            if ($this->showAttributeForm && count($this->model->attributes) > 0) {
                $params['form'] = CommonPlugin_Widget::attributeForm($this, $this->model, false, true);
            }

            if ($this instanceof MessageStatisticsPlugin_Controller_Messages) {
                $params['chart_div'] = 'chart_div';
                $params['chart'] = $this->createChart($params['chart_div']);
            }
        } catch (Exception $e) {
            $params['exception'] = $e->getMessage();
        }
        print $this->render(dirname(__FILE__) . '/view.tpl.php', $params);
    }
    /*
     *    Public methods
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new MessageStatisticsPlugin_Model(new CommonPlugin_DB());
        $this->model->setProperties($_REQUEST);
    }

    public function exportFileName()
    {
        $msgid = $this->model->msgid;
        return isset($msgid)
            ? "message_{$msgid}_{$this->model->type}"
            : $this->model->type;
    }
}
