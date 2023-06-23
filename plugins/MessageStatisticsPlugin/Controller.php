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
 * This class is the controller for message statistics.
 * It is a base class providing common processing.
 * Sub-classes provide the populator and exportable functions for each type.
 *
 * @category  phplist
 */
abstract class MessageStatisticsPlugin_Controller extends CommonPlugin_Controller
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
            'userviews' => $this->i18n->get('tab_userviews'),
        );
        $tabs = new CommonPlugin_Tabs();
        $query = array();
        $query['listid'] = $this->model->listid;
        $query['type'] = 'messages';

        /* Always display Messages tab */
        $tabs->addTab($types['messages'], new CommonPlugin_PageURL(null, $query));

        if (in_array($this->model->type, ['settings', 'lists', 'messages'])) {
            /* Display Lists and Settings tabs when they are current and when Messages is current */

            if (in_array($this->model->type, ['lists', 'messages'])) {
                $tabs->addTab($types['lists'], new CommonPlugin_PageURL(null, array('type' => 'lists')));
            }

            if (in_array($this->model->type, ['settings', 'messages'])) {
                $tabs->addTab($types['settings'], new CommonPlugin_PageURL(null, array('type' => 'settings')));
            }
        } else {
            /*
             * Opened -> Links tabs
             */
            $query['msgid'] = $this->model->msgid;

            foreach (array_slice($types, 3, 7) as $type => $title) {
                $query['type'] = $type;
                $tabs->addTab($title, new CommonPlugin_PageURL(null, $query));
            }

            // Display only when selected
            if ($this->model->type == 'linkclicks') {
                $tabs->addTab($types[$this->model->type], CommonPlugin_PageURL::createFromGet());
            } elseif ($this->model->type == 'userviews') {
                $tabs->addTab($types[$this->model->type], CommonPlugin_PageURL::createFromGet());
                $tabs->insertTabBefore($types['unopened'], $types[$this->model->type]);
            }
        }
        $tabs->setCurrent($types[$this->model->type]);

        return $tabs;
    }

    /**
     * Encapsulate the calculation of a %age rate.
     *
     * @param int $actual
     * @param int $sent
     *
     * @return float
     */
    protected function calculateRate($actual, $sent)
    {
        return round($actual / $sent * 100, 1);
    }

    /**
     * Generate default caption
     * Intended to be overridden by subclasses.
     *
     * @return string
     */
    protected function caption()
    {
        return $this->i18n->get(
            'message %s sent to %s',
            sprintf('%s "%s"', $this->model->msgid, $this->model->msgSubject),
            sprintf('"%s"', implode('", "', $this->model->listNames))
        );
    }

    /**
     * Create prev and next message values
     * Intended to be overridden by subclasses.
     *
     * @return array [0] parameter, [1] previous item, [2] next item
     */
    protected function prevNext()
    {
        list($prev, $next) = $this->model->prevNextMessage();

        return array('msgid', $prev, $next);
    }

    protected function actionDefault()
    {
        try {
            if ($this->model->access == 'none') {
                throw new MessageStatisticsPlugin_NoAccessException();
            }
            $this->model->validateProperties();

            if (isset($_POST['SearchForm'])) {
                $this->model->setProperties($_POST['SearchForm'], true);
                $redirect = new CommonPlugin_PageURL(null, ['type' => 'opened']);
                header("Location: $redirect");
                exit;
            }

            $params = array();
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

                if ($this instanceof MessageStatisticsPlugin_Controller_Messages) {
                    if (getConfig('statistics_date_filter')) {
                        $params['campaign_form'] = $this->campaignSelectForm();
                        $listing->pager->setItemsPerPage([]);
                        $params['listing'] = $listing->display();
                        $listName = $this->model->listNames ? $this->model->listNames[0] : '';
                        $params['summary'] = $this->summary($listName, $this->model->fromdate, $this->model->todate);
                    } else {
                        $this->model->fromdate = null;
                        $this->model->todate = null;
                        $params['listing'] = $listing->display();
                    }
                    $params['chart_div'] = 'chart_div';
                    $params['chart'] = $this->createChart($params['chart_div']);
                } else {
                    $params['listing'] = $listing->display();
                }
            }

            if ($this->showAttributeForm && count($this->model->attributes) > 0) {
                $params['form'] = CommonPlugin_Widget::attributeForm($this, $this->model, false, true);
            }
            $toolbar = new CommonPlugin_Toolbar($this);

            if ($this instanceof CommonPlugin_IExportable) {
                if ($this instanceof MessageStatisticsPlugin_Controller_Messages
                   && !getConfig('statistics_export_all_messages')) {
                    list($start, $limit) = $listing->pager->range();
                } else {
                    $start = $limit = null;
                }
                $query = [
                    'listid' => $this->model->listid,
                    'msgid' => $this->model->msgid,
                    'type' => $this->model->type,
                    'forwardid' => $this->model->forwardid,
                    'fromdate' => $this->model->fromdate,
                    'todate' => $this->model->todate,
                ];
                $toolbar->addExportButton($query + array('start' => $start, 'limit' => $limit));
            }
            $toolbar->addHelpButton($this->model->type);
            $params['toolbar'] = $toolbar->display();
        } catch (Exception $e) {
            $params['exception'] = $e->getMessage();
        }
        echo $this->render(dirname(__FILE__) . '/view.tpl.php', $params);
    }

    /*
     *    Public methods
     */
    public function __construct(MessageStatisticsPlugin_Model $model)
    {
        parent::__construct();
        $this->model = $model;
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
