<?php

$pluginsDir = dirname(__DIR__);

return [
    'MessageStatisticsPlugin_CampaignReport' => $pluginsDir . '/MessageStatisticsPlugin/CampaignReport.php',
    'MessageStatisticsPlugin_Controller' => $pluginsDir . '/MessageStatisticsPlugin/Controller.php',
    'MessageStatisticsPlugin_ControllerFactory' => $pluginsDir . '/MessageStatisticsPlugin/ControllerFactory.php',
    'MessageStatisticsPlugin_Controller_Bounced' => $pluginsDir . '/MessageStatisticsPlugin/Controller/Bounced.php',
    'MessageStatisticsPlugin_Controller_Clicked' => $pluginsDir . '/MessageStatisticsPlugin/Controller/Clicked.php',
    'MessageStatisticsPlugin_Controller_Domain' => $pluginsDir . '/MessageStatisticsPlugin/Controller/Domain.php',
    'MessageStatisticsPlugin_Controller_Forwarded' => $pluginsDir . '/MessageStatisticsPlugin/Controller/Forwarded.php',
    'MessageStatisticsPlugin_Controller_Linkclicks' => $pluginsDir . '/MessageStatisticsPlugin/Controller/Linkclicks.php',
    'MessageStatisticsPlugin_Controller_Links' => $pluginsDir . '/MessageStatisticsPlugin/Controller/Links.php',
    'MessageStatisticsPlugin_Controller_Lists' => $pluginsDir . '/MessageStatisticsPlugin/Controller/Lists.php',
    'MessageStatisticsPlugin_Controller_Messages' => $pluginsDir . '/MessageStatisticsPlugin/Controller/Messages.php',
    'MessageStatisticsPlugin_Controller_Opened' => $pluginsDir . '/MessageStatisticsPlugin/Controller/Opened.php',
    'MessageStatisticsPlugin_Controller_Settings' => $pluginsDir . '/MessageStatisticsPlugin/Controller/Settings.php',
    'MessageStatisticsPlugin_Controller_Unopened' => $pluginsDir . '/MessageStatisticsPlugin/Controller/Unopened.php',
    'MessageStatisticsPlugin_Controller_Userviews' => $pluginsDir . '/MessageStatisticsPlugin/Controller/Userviews.php',
    'MessageStatisticsPlugin_DAO_List' => $pluginsDir . '/MessageStatisticsPlugin/DAO/List.php',
    'MessageStatisticsPlugin_DAO_Message' => $pluginsDir . '/MessageStatisticsPlugin/DAO/Message.php',
    'MessageStatisticsPlugin_MessageNotExistException' => $pluginsDir . '/MessageStatisticsPlugin/MessageNotExistException.php',
    'MessageStatisticsPlugin_Model' => $pluginsDir . '/MessageStatisticsPlugin/Model.php',
    'MessageStatisticsPlugin_NoAccessException' => $pluginsDir . '/MessageStatisticsPlugin/NoAccessException.php',
    'MessageStatisticsPlugin_NoMessagesException' => $pluginsDir . '/MessageStatisticsPlugin/NoMessagesException.php',
    'MessageStatisticsPlugin_NotAuthorisedException' => $pluginsDir . '/MessageStatisticsPlugin/NotAuthorisedException.php',
    'MessageStatisticsPlugin_PageactionController' => $pluginsDir . '/MessageStatisticsPlugin/pageaction.php',
];
