=== BotAlert/BotBlock ===
Contributors: pramana
Donate link: http://www.pramana.com
Tags: comments, registration, botalert, botblock, antispam, mailhide, captcha, wpmu
Requires at least: 2.1
Tested up to: 3.0.0
Stable tag: 1.0.1

Integrates BotAlert/BotBlock anti-spam methods with WordPress including comment, registration, and email spam protection. WPMU Compatible.

== Description ==

= What is BotAlert/BotBlock =

[BotAlert/BotBlock](http://pramana.com/ "BotAlert/BotBlock") is an invisible CAPTCHA replacement/alternative solution by Pramana Inc.

This plugin is [WordPress MU](http://mu.wordpress.org/) compatible.

For more information please view the [plugin page](http://code.google.com/p/botalert-botblock/ "wp-botalert") or go the Pramana's website: (http://pramana.com/)

== Installation ==

To install in regular WordPress:

1. Make sure you have cURL and php-cURL installed. On some platforms, php comes with cURL compiled in already. 
    * To test, you can use this PHP snippet:  `<?php if(function_exists("curl_init")) { echo "curl present"; } else { echo "curl NOT installed"; } ?>`
1. Upload the `botalertbotblock` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the `Plugins` menu in WordPress
1. Get your BotAlert/BotBlock keys (aka. CustID and AuthToken) by creating an account at [pramana.com/account/register](https://pramana.com/account/register "Sign up for a BotAlert/BotBlock account"), then add your domain and click on &ldquo;Service URLs&rdquo; link for your domain on the My Account page.

To install in WordPress MU (Optional Activation by Users):

1. Follow the instructions for regular WordPress above

To install in WordPress MU (Forced Activation/Site-Wide):

1. Upload the `botalertbotblock` folder to the `/wp-content/mu-plugins` directory
1. **Move** the `wp-botalert.php` file out of the `botalertbotblock` folder so that it is in `/wp-content/mu-plugins`
1. Now you should have `/wp-content/mu-plugins/wp-botalert.php` and `/wp-content/mu-plugins/botalertbotblock/`
1. Go to the administrator menu and then go to **Site Admin > BotAlert/BotBlock**
1. Get your BotAlert/BotBlock keys (aka. CustID and AuthToken) by creating an account at [pramana.com/account/register](https://www.pramana.com/account/register "Sign up for a BotAlert/BotBlock account"), then add your domain and click on &ldquo;Service URLs&rdquo; link for your domain on the My Account page.

== Upgrade Notice ==

From 1.0.0 on, you will need to have cURL and php-cURL installed. 
Also, since version 1.0.0 there is no more manual or autowiring needed. Its now all done from within the plugin. This gets rid of the Javascript issues sometimes associated with both auto- and manual-wiring because now the validations are triggered from the plugin and not from the user's browser anymore. 

== Requirements ==

* You need BotAlert/BotBlock keys (aka. CustID and AuthToken) [here](http://pramana.com/account "Sign up for a BotAlert/BotBlock account").
* cURL and php-cURL must be installed on your server. On some platforms, php comes with cURL compiled in already. To test, you can use this PHP snippet:
    `<?php
       if(function_exists("curl_init")) { echo "curl present"; } 
       else { echo "curl NOT installed"; }
     ?>`
   Or just go to the BotAlert/BotBlock Options page under Settings and run the BotAlert/BotBlock Support Test.


== ChangeLog ==

= Version 1.0.1 =
* dropped one round-trip to BotAlert/BotBlock servers, thus making the plugin a bit faster.
* added support for non-javascript browsers
* several configuration improvements, incl. a requirements test for cURL
= Version 1.0.0 =
* changed data flow to go through plugin: BotAlert/BotBlock Javascript passes through plugin when loaded and results are submitted through plugin for validation. Before, both were done through the client's browser directly and thus not always reliable.
= Version 0.9.6 =
* first version on wordpress.org
* cleaned up
* added auto-wiring
= Version 0.9.5 =
* first public version


== Frequently Asked Questions ==

= What is the difference between BotAlert and BotBlock? =

BotBlock provides real-time results, so it can be used as a CAPTCHA replacement. BotAlert on the other side does not give you real-time results but allows you to measure the amount of Bot (automated process) traffic on your site.

BotBlock, on the other hand, provides real-time results and thus allow you to prevent spam and abuse when it happens.

Both services will provide you with daily reports via email.

= If using BotAlert, the FREE service, how will I know the results, do I get a report? =

Yes, you will receive a daily report at the email you used for creating an account at pramana.com. This is applicable for both BotAlert and BotBlock users.

= How can I get support =

Please go to our support forum: http://forums.pramana.com/

== Screenshots ==

1. The BotAlert/BotBlock Settings
2. Daily Report Example
