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
 * @copyright 2011-2017 Duncan Cameron
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, Version 3
 */

/*
 * This file contains the Russian translations
 * Important - this file must be saved in UTF-8 encoding
 */

$lan = array(
//    Controller.php
    'tab_settings' => 'Настройка',
    'tab_lists' => 'Рассылки',
    'tab_messages' => 'Кампании',
    'tab_opened' => 'Открыты',
    'tab_unopened' => 'Не открыты',
    'tab_clicked' => 'Переходы',
    'tab_bounced' => 'Возвраты',
    'tab_forwarded' => 'Переадресовано',
    'tab_domains' => 'Домены',
    'tab_links' => 'Ссылки',
    'tab_linkclicks' => 'Переходы по ссылкам',
    'User email' => 'email',
    'message %s sent to %s' => 'Кампания %s отправлена %s',
//    Opened.php
    'first view' => 'Первое открытие',
//    Unopened.php
//    Bounced.php
    'Bounce ID' => 'ID возврата',
    'email' => 'email',
//    Clicked.php
    'count' => 'Всего',
    'links clicked' => 'Клики по ссылкам',
    'clicks_total' => 'Всего кликов',
    'user_not_exist' => 'Пользователь сейчас не существует.',
//    Domain.php
    'Domains to which the campaign was sent' => 'Домены, на которые была отправлена Кампания',
    'Domain' => 'Домен',
    'sent' => 'Отправлено',
    'opened' => 'Открыто',
    'opened %' => 'Открыто %',
    'clicked' => 'Кликов',
    'clicked %' => 'Кликов %',
//    Forwarded.php
//    Lists.php
    'Lists' => 'Список рассылки',
    'All lists' => 'Все списки',
    'active' => 'Активен',
    'total sent' => 'Всего отправлено',
    'latest' => 'Последний',
//    Messages.php
    'Messages sent to %s' => 'Кампания отправлена %s',
    'All sent messages' => 'Все отправленные Кампании',
    'id' => 'id',
    'Subject' => 'Тема',
    'date' => 'Дата',
    'bounced' => 'Возвраты',
    'views' => 'Просмотров',
    'print' => 'Печать',
    'print to PDF' => 'печать в PDF',
    'Message ID' => 'Message ID',
    'Users' => 'Пользователь',
    'Campaigns' => 'Кампании',
//    Links
    'Links in the campaign' => 'Ссылки в Кампании',
    'Link' => 'Ссылка',
    'total clicks' => 'Всего кликов',
    'subscribers' => 'Подписчики',
    'subscribers %' => 'Подписчики %',
    'firstclick' => 'Первый',
    'latestclick' => 'Последний',
//    LinkClicks
    'Link "%s"' => 'Ссылка "%s"',
//    Settings
    'caption' => 'Выберите любой из атрибутов Пользователя, которые будут отображаться в виде отдельных столбцов в дополнение к электронной почте Пользователя',
    'no_attrs' => 'Пользовательских атрибутов нет.',
//    exceptions
    'You are not authorised to view message %d' => 'У вас нет прав на просмотр Кампании %d.',
    'Message %d does not exist' => 'Кампания %d не существует.',
    'no_messages' => 'Для текущего пользователя Кампании не найдены.',
    'no_access' => 'У вас нет доступа к этой странице.',
//    view.tpl.php
    'plugin_title' => 'Модуль расширенной статистики',
);
