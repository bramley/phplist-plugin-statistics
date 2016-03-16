# Campaign Statistics Plugin #

## Description ##

This plugin provides comprehensive statistics on campaigns.
It provides a multi-tabbed page that shows opens, clicks, bounces and forwards for each campaign.
## Installation ##

### Dependencies ###

This plugin is for phplist 3.

Requires php version 5.3 or later.

Requires the Common Plugin version 3.0.3 or later to be installed. You should install or upgrade to the latest version. See <https://github.com/bramley/phplist-plugin-common>

### Set the plugin directory ###
The default plugin directory is `plugins` within the admin directory.

You can use a directory outside of the web root by changing the definition of `PLUGIN_ROOTDIR` in config.php.
The benefit of this is that plugins will not be affected when you upgrade phplist.

### Install through phplist ###
The recommended way to install is through the Plugins page (menu Config > Manage Plugins) using the package URL `https://github.com/bramley/phplist-plugin-statistics/archive/master.zip`.

In phplist releases 3.0.5 and earlier there is a bug that can cause a plugin to be incompletely installed on some configurations (<https://mantis.phplist.com/view.php?id=16865>). 
Check that these files are in the plugin directory. If not then you will need to install manually. The bug has been fixed in release 3.0.6.

* the file MessageStatisticsPlugin.php
* the directory MessageStatisticsPlugin

### Install manually ###
Download the plugin zip file from <https://github.com/bramley/phplist-plugin-statistics/archive/master.zip>

Expand the zip file, then copy the contents of the plugins directory to your phplist plugins directory.
This should contain

* the file MessageStatisticsPlugin.php
* the directory MessageStatisticsPlugin

## Known problems ##

### Export fails with "Error, incorrect session token" ###
phplist 3.0.9 had a change that stopped the plugin export working. Instead the message "Error, incorrect session token" is displayed.

To fix this problem upgrade to the latest version of CommonPlugin.

##Usage##

For guidance on using the plugin see the plugin's page within the phplist documentation site <https://resources.phplist.com/plugin/messagestatistics>

## Support ##

Please raise any questions or problems in the user forum <https://discuss.phplist.org/>.

## Donation ##

This plugin is free but if you install and find it useful then a donation to support further development is greatly appreciated.

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=W5GLX53WDM7T4)

## Version history ##

    version     Description
    2.1.3+20160316  Internal changes
    2.1.2+20160316  Ensure campaign subject is displayed instead of campaign title
    2.1.1+20150828  Update dependencies
    2.1.0+20150826  Export either all campaigns or only those currently displayed
    2.0.0+20150815  Update dependencies
    2015-05-23      Alter colours used for overlays
    2015-05-10      Add dependency checks
    2015-03-23      Change to autoload approach
    2015-01-24      On Links tab show whether a URL is personalised
    2014-11-19      Fix error when exporting link clicks
    2014-09-04      Change to ordering on Campaigns tab
    2014-08-01      Fix sql error when viewing tabs for one list
    2014-07-26      Display active campaigns on the Campaigns tab
    2014-07-13      Accumulated minor changes
    2014-04-22      Fix problem of 'no campaigns found' after deleting list
    2014-01-27      Fix for Lists tab not showing correct latest campaign
                    Select messages with sent, inprocess or suspended status 
    2014-01-27      On Messages tab order by sent date
    2014-01-25      Use Google Charts instead of Chart API
    2013-12-10      GitHub issue #3
    2013-08-26      Accumulated minor changes
    2013-04-22      Added README, changes to use the latest Common Plugin 
    2013-03-29      Initial version for phplist 2.11.x releases
