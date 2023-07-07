# Campaign Statistics Plugin #

## Description ##

This plugin provides comprehensive statistics on campaigns.
It provides a multi-tabbed page that shows opens, clicks, bounces and forwards for each campaign.
## Installation ##

### Dependencies ###

This plugin is for phplist 3.

Requires php version 5.5 or later.

Requires the Common Plugin to be installed. You should install or upgrade to the latest version. See <https://github.com/bramley/phplist-plugin-common>

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

## Usage ##

For guidance on using the plugin see the plugin's page within the phplist documentation site <https://resources.phplist.com/plugin/messagestatistics>

## Support ##

Please raise any questions or problems in the user forum <https://discuss.phplist.org/>.

## Donation ##

This plugin is free but if you install and find it useful then a donation to support further development is greatly appreciated.

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=W5GLX53WDM7T4)

## Version history ##

    version     Description
    2.4.0+20230707  Show latest view, number of views and link clicks totals on the Opened tab
                    Filter Opened tab on the number of views and number of links clicked
    2.3.6+20211231  Allow captions in the campaign pdf report to be translated
    2.3.5+20211212  On the Campaigns report improve the display of the subject field
    2.3.4+20210926  Only format message dates that have a value to avoid php 8 error
    2.3.3+20210220  Make the display of tabs consistent by revising the column headings
    2.3.2+20210219  Include the campaign title on the Campaign report
    2.3.1+20210218  Revise Spanish translations
    2.3.0+20210216  Add alternative selection of campaigns for the Campaigns tab
    2.2.2+20210210  On the Campaigns report improve the display of the subject field
    2.2.1+20210208  On the Campaigns report convert the subject to ISO-8859-1
    2.2.0+20210208  Use FPDF to create the campaign PDF report
    2.1.29+20200416 Display dates using the date format configuration
    2.1.28+20200306 Rework handling of attribute DAO
    2.1.27+20190730 Reduce the number of tabs displayed
                    Clicking the chart will open the appropriate tab
    2.1.26+20190301 Display 5 campaigns initially on the campaigns tab
    2.1.25+20181210 Improve display of month names
    2.1.24+20181203 Add Russian translations
    2.1.23+20181203 Include campaign title in pdf report
    2.1.22+20181022 Internal rework
    2.1.21+20180916 On the Lists tab highlight the currently selected list
    2.1.20+20180829 On several tabs, link to user page instead of userhistory
    2.1.19+20180810 Added configuration setting to display campaign subject or title
    2.1.18+20180512 Correct clicked total on Domain tab
    2.1.17+20180308 Internal code refactoring
    2.1.16+20180226 Add base64 encoded image to appear on pdf report
    2.1.15+20180129 Include % on Domains and Links pages
    2.1.14+20170703 Improve order of sorting on campaigns tab
    2.1.13+20170601 Internal code changes
    2.1.12+20170518 Correct display of campaign subject
    2.1.11+20170510 On Opened tab use correct caption for first vew
    2.1.10+20170418 Ensure date is always displayed on Campaigns tab
    2.1.9+20170304  Use core phplist help dialog
    2.1.8+20160604  Revise translations
    2.1.7+20160603  Avoid Excel problem with exporting campaigns
    2.1.6+20160527  Add class map for autoloading
    2.1.5+20160424  Fix another problem with not listing all campaigns
    2.1.4+20160424  Fix for bug of not listing all campaigns
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
