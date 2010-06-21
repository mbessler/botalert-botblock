=== BotAlert/BotBlock ===
Contributors: pramana
Donate link: http://www.pramana.com
Tags: comments, registration, botalert, botblock, antispam, mailhide, captcha, wpmu
Requires at least: 2.1
Tested up to: 2.9.2
Stable tag: 0.9

Integrates BotAlert/BotBlock anti-spam methods with WordPress including comment, registration, and email spam protection. WPMU Compatible.

== Description ==

= What is BotAlert/BotBlock =

[BotAlert/BotBlock](http://pramana.com/ "BotAlert/BotBlock") is an invisible CAPTCHA replacement solution by Pramana Inc.

This plugin is [WordPress MU](http://mu.wordpress.org/) compatible.

For more information please view the [plugin page](http://code.google.com/p/botalert-botblock/ "wp-botalert") or go the Pramana's website: (http://pramana.com/)

== Installation ==

To install in regular WordPress:

1. Upload the `wp-botalert` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the `Plugins` menu in WordPress
1. Get your BotAlert/BotBlock keys (aka. CustID and AuthToken) [here](http://pramana.com/account "Sign up for a BotAlert/BotBlock account").
1. Select whether you want to use auto-wiring for forms, or to manually add 'onclick=' handlers to trigger validation at form submission:
   1. With auto-wiring you don't need to modify any themes or any other files, just select "Enable Form Autowiring" in the configuration.
   1. Without auto-wiring, connect the Submit buttons to the `triggerPramana()` handler in your theme(s):

      for the default theme:

       * for new user registration in `wp-login.php`, add `<?php global $botalert_opt; echo ($botalert_opt['ba_registration'])? 'onclick="triggerPramana(this.form,this.form.user_email.value);"':'' ?>` to the `<input type="submit ...>` button like this:

            `<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="<?php esc_attr_e('Register'); ?>" <?php global $botalert_opt; echo ($botalert_opt['ba_registration'])? 'onclick="triggerPramana(this.form,this.form.user_email.value);"':'' ?>  tabindex="100" /></p>`

       * for comment posting in `wp-content/themes/default/comments.php`, add `<?php global $botalert_opt; echo ($botalert_opt['ba_comments']==1)? 'onclick="triggerPramana(this.form,this.form.email.value);"':'' ?>` like this:

             <input name="submit" type="submit" id="submit" tabindex="5" <?php global $botalert_opt; echo ($botalert_opt['ba_comments']==1)? 'onclick="triggerPramana(this.form,this.form.email.value);"':'' ?> value="Submit Comment" />

To install in WordPress MU (Optional Activation by Users):

1. Follow the instructions for regular WordPress above

To install in WordPress MU (Forced Activation/Site-Wide):

1. Upload the `wp-botalert` folder to the `/wp-content/mu-plugins` directory
1. **Move** the `wp-botalert.php` file out of the `wp-botalert` folder so that it is in `/wp-content/mu-plugins`
1. Now you should have `/wp-content/mu-plugins/wp-botalert.php` and `/wp-content/mu-plugins/wp-botalert/`
1. Go to the administrator menu and then go to **Site Admin > BotAlert/BotBlock**
1. Get your BotAlert/BotBlock keys (aka. CustID and AuthToken) [here](http://pramana.com/account "Sign up for a BotAlert/BotBlock account").

== Upgrade Notice ==
no upgrades yet

== Requirements ==

* You need BotAlert/BotBlock keys (aka. CustID and AuthToken) [here](http://pramana.com/account "Sign up for a BotAlert/BotBlock account").
* Your theme must have a `do_action('comment_form', $post->ID);` call right before the end of your form (*Right before the closing form tag*). Most themes do.

== ChangeLog ==

= Version 0.9.5 =
* first version


== Frequently Asked Questions ==

= What is the difference between BotAlert and BotBlock? =

BotAlert is a free service, however it does not provide real-time results. As such, it cannot prevent abuse, but it lets you measure the amount of abuse by automated processes (aka. bots). 
BotBlock, on the other hand, provides real-time results and thus allow you to prevent spam and abuse when it happens.

== Screenshots ==

1. The BotAlert/BotBlock Settings
