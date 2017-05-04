# phpBB - Private Categories

A phpBB extension that allows to create private categories where only selected users can read topics.

## Features
* Topics in private categories are only visible to selected users
* Permissions (all permissions are forum permissions)
    * see all - allows a user to see all private content
    * invite/remove all - allows a user to add/remove user permissions for all private topics
    * invite/remove own - allows a user to add/remove user permissions for his own private topics
* ACP module where you can select which category or forum should be private
    * Inheritance can be enabled
* Forum obfuscation shows correct counts and last post so that no private data leaks
* Filter search results so that private topics or posts won't be shown

## Supported styles
* prosilver
* [Mobbern3.1](http://www.masivotech.com/product/mobbern-phpbb3-phpbb31-responsive-theme/ "Mobbern phpBB responsive theme website")

## Supported languages
* Deutsch
* English

## How to install
To install the extension you have to do the following steps:

1. Copy the /pcgf/ folder into your phpBB extension folder /ext/
2. Navigate to the ACP to Customize &rarr; Manage extensions
3. Search the extension under the disabled extensions category and click it's enable link

To enable the functionality of the extension you have to set which categories should be private. You can do this
in the ACP under _Forums &rarr; Manage forums &rarr; Private categories_.

After you have set the categories that should be private you have to set forum permissions for users or groups.
By default anybody is allowed to do anything.

## Requirements
* php 5.3.3 or newer
* phpBB 3.1.* or newer

## External links
[phpBB extension database](https://www.phpbb.com/customise/db/extension/privatecategories/ "Show extension entry on phpBB.com")

## Donate
If you like the extension feel free to [Donate with PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SY9JFM9XL9CWQ).

[![Donate with PayPal](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SY9JFM9XL9CWQ)