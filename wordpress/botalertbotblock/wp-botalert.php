<?php
/*
Plugin Name: BotAlertBotBlock
Plugin URI: http://code.google.com/p/botalert-botblock
Description: Integrates BotAlert/BotBlock anti-spam solutions with WordPress. BotAlert/BotBlock is an invisible CAPTCHA replacement/alternative. <a href="options-general.php?page=botalertbotblock/wp-botalert.php">Settings</a>
Version: 1.0.1
Author: Pramana Inc.
Email: websupport@pramana.com
Author URI: http://www.pramana.com
*/

// WORDPRESS MU DETECTION

// WordPress MU settings - DON'T EDIT
//    0 - Regular WordPress installation
//    1 - WordPress MU Forced Activated
//    2 - WordPress MU Optional Activation

$wpmu = 0;

if (basename(dirname(__FILE__)) == "mu-plugins") // forced activated
   $wpmu = 1;
else if (basename(dirname(__FILE__)) == "plugins" && function_exists('is_site_admin')) // optionally activated
   $wpmu = 2;

if ($wpmu == 1)
   $botalert_opt = get_site_option('botalert'); // get the options from the database
else
   $botalert_opt = get_option('botalert'); // get the options from the database

// END WORDPRESS MU DETECTION

if ($wpmu == 1)
   require_once(dirname(__FILE__) . '/botalertbotblock/php-common/botalertlib.php');
else
   require_once(dirname(__FILE__) . '/php-common/botalertlib.php');

// If the plugin is deactivated, delete the preferences
function delete_preferences() {
   global $wpmu;

   if ($wpmu != 1)
		delete_option('botalert');
}

register_deactivation_hook(__FILE__, 'delete_preferences');

/* =============================================================================
   BotAlert/BotBlock on Registration Form 
   ============================================================================= */
   
// Embed the BotAlert code into the registration form
function embed_botalert($errors) {
    global $botalert_opt, $wpmu;
   
    if ($botalert_opt['ba_registration']) {
		
        if ($wpmu == 1) {
            $error = $errors->get_error_message('botalert'); ?>
            <label for="verification">Verification:</label>
            <?php echo($error ? '<p class="error">'.$error.'</p>' : '') ?>
            <?php echo($_GET['rerror']? '<p class="error">RERROR2:'.$_GET['rerror'].'</p>' : '') ?>
            <?php 
        }
	else {
            echo ($_GET['rerror']? '<p class="error">RERROR3' . $_GET['rerror'] . '</p>': '');
        }
        echo botalert_ACODE($botalert_opt['custid'], $botalert_opt['authtoken']);
    }
}

// Hook the embed_botalert function into WordPress
if ($wpmu != 1)
   add_action('register_form', 'embed_botalert');
else
   add_action('signup_extra_fields', 'embed_botalert');

// Check the BotBlock score
function check_botalert_score() {
	global $botalert_opt, $errors;
	
   if (empty($_POST['hpmxRequestId']))
		$errors['blank_captcha'] = $botalert_opt['botalert_error'];
   
   else {
	$botalert_result = botalert_VALI($_POST['user_login']);
	if ( ! $botalert_result )
		$errors['captcha_wrong'] = $botalert_opt['botalert_isbot'];
   }
}

// Check the BotBlock score
function check_botalert_score_new($errors) {
	global $botalert_opt;
	
   if (empty($_POST['hpmxRequestId']) ) {
		$errors->add('blank_captcha', $botalert_opt['botalert_error']);
		return $errors;
   }
   
	$botalert_result = botalert_VALI($_POST['user_login']);
	if (! $botalert_result )
		$errors->add('captcha_wrong', $botalert_opt['botalert_isbot']);
   
   return $errors;
}

// Check Botalert/BotBlock on WordPress MU
function check_botalert_score_wpmu($result) {
   global $_POST, $botalert_opt;
   
   // must make a check here, otherwise the wp-admin/user-new.php script will keep trying to call
   // this function despite not having called do_action('signup_extra_fields'), so the recaptcha
   // field was never shown. this way it won't validate if it's called in the admin interface
   if (!is_admin()) {
         // It's blogname in 2.6, blog_id prior to that
      if (isset($_POST['blog_id']) || isset($_POST['blogname']))
      	return $result;

      // no text entered
      if (empty($_POST['hpmxRequestId']) ) {
      	$result['errors']->add('blank_captcha', $botalert_opt['botalert_error']);
      	return $result;
      }

	$botalert_result = botalert_VALI($_POST['user_login']);
	if ( ! $botalert_result ) {
      		$result['errors']->add('captcha_wrong', $botalert_opt['botalert_isbot']);
            echo "<div class=\"error\">". $botalert_opt['botalert_isbot'] . "</div>";
      	}
   }
   
   return $result;
}

if ($botalert_opt['ba_registration']) {
   if ($wpmu == 1)
		add_filter('wpmu_validate_user_signup', 'check_botalert_score_wpmu');
   
   else if ($wpmu == 0) {
		// Hook the check_botalert_score function into WordPress
		if (version_compare(get_bloginfo('version'), '2.5' ) >= 0)
         add_filter('registration_errors', 'check_botalert_score_new');
		else
         add_filter('registration_errors', 'check_botalert_score');
   }
}
/* =============================================================================
   End BotAlert/BotBlock on Registration Form
   ============================================================================= */

/* =============================================================================
   BotAlert/BotBlock Plugin Default Options
   ============================================================================= */

$option_defaults = array (
   'custid'	=> '', // the custid for BotAlert/BotBlock. Sign up at http://pramana.com 
   'authtoken'	=> '', // the authtoken for BotAlert/BotBlock. Sign up at http://pramana.com
   'have_botblock' => '', // which product? BotAlert or BotBlock
   'treat_neutral_as_bad' => '0', // whether to treat a neutral score as Bot or Human
   'ba_comments' => '1', // whether or not to use BotAlert/BotBlock on the comment post
   'ba_registration' => '1', // whether or not to use BotAlert/BotBlock on the registration page
   'botalert_error' => '<strong>ERROR</strong>: Please try again.', // timeout/hpmx error (eg. hpmxRequestId missing)
   'botalert_isbot' => '<strong>ERROR</strong>: Your behavior looks suspicious. If you are a human, please try again.', // the message to display when the user is classified as Bot by BotBlock
);

// install the defaults
if ($wpmu != 1)
   add_option('botalert', $option_defaults, 'BotAlert/BotBlock Default Options', 'yes');

/* =============================================================================
   End BotAlert/BotBlock Plugin Default Options
   ============================================================================= */

/* =============================================================================
   BotAlert/BotBlock - The BotAlert/BotBlock comment spam protection section
   ============================================================================= */
function botalert_wp_hash_comment($id)
{
	global $botalert_opt;
   
	if (function_exists('wp_hash'))
		return wp_hash(BOTALERT_WP_HASH_COMMENT . $id);
	else
		return md5(BOTALERT_WP_HASH_COMMENT . $botalert_opt['authtoken'] . $id);
}


/**
 *  Embeds the BotAlert/BotBlock JS into the comment form.
 * 
 */	
function botalert_comment_form() {
   global $user_ID, $botalert_opt;

	// Did the user's behavior look suspiciously like a bot? If so, let them know
	if ($_GET['rerror'] == 'botalert_isbot')
		echo "<p style=\"font-size: 1.3em; padding-bottom: 8px;\">" . $botalert_opt['botalert_isbot'] . "</p>";
 
	if( !$botalert_opt['ba_comments'] ) // skip if disabled
		return;

	//modify the comment form for the BotAlert/BotBlock JS
	$comment_string = <<<COMMENT_FORM
		<div id="botalert-submit-btn-area"><br /></div> 
		<script type='text/javascript'>
			var sub = document.getElementById('submit');
			sub.parentNode.removeChild(sub);
			document.getElementById('botalert-submit-btn-area').appendChild (sub);
			document.getElementById('submit').tabIndex = 6;
			if ( typeof _botalert_wordpress_savedcomment != 'undefined') {
				document.getElementById('comment').value = _botalert_wordpress_savedcomment;
			}
		</script>
		<noscript>
			<style type='text/css'>#submit {display:none;}</style>
			<input name="submit" type="submit" id="submit-alt" tabindex="6" value="Submit Comment"/> 
		</noscript>
COMMENT_FORM;

        $ba_snippet = botalert_ACODE($botalert_opt['custid'], $botalert_opt['authtoken']);
	echo $ba_snippet . $comment_string;
}

add_action('comment_form', 'botalert_comment_form');

function botalert_wp_get_for_comment() {
   global $user_ID;
   return true;
}

$botalert_saved_error = '';

/**
 * Checks if the BotAlert/BotBlock score was positive (Human) and sets an error session variable if not
 * @param array $comment_data
 * @return array $comment_data
 */
function botalert_wp_check_comment($comment_data) {
	global $user_ID, $botalert_opt;
	global $botalert_saved_error;
	
	// skip the filtering if the minimum capability is met
	if (!$botalert_opt['ba_comments'])
		return $comment_data;

	if (botalert_wp_get_for_comment()) {
		if ( $comment_data['comment_type'] == '' ) { // Do not check trackbacks/pingbacks

			$botalert_result = botalert_VALI($_POST['email']);
			if ($botalert_result)
				return $comment_data;
			else {
				$botalert_saved_error = 'botalert_isbot';
				add_filter('pre_comment_approved', create_function('$a', 'return \'spam\';'));
				return $comment_data;
			}
		}
	}
	return $comment_data;
}

function botalert_VALI($refid) {
	global $botalert_opt;
        return botalert_VALIandVERD($botalert_opt['custid'], $botalert_opt['authtoken'], $_POST['hpmxRequestId'], $refid, $botalert_opt['have_botblock'], ! $botalert_opt['treat_neutral_as_bad']);
}


/*
 * If the BotBlock score indicated Bot from botalert_wp_check_comment, then redirect back to the comment form 
 * @param string $location
 * @param OBJECT $comment
 * @return string $location
 */
function botalert_wp_relative_redirect($location, $comment) {
	global $botalert_saved_error;
	if($botalert_saved_error != '') { 
		//replace the '#comment-' chars on the end of $location with '#commentform'.

		$location = substr($location, 0,strrpos($location, '#')) .
			((strrpos($location, "?") === false) ? "?" : "&") .
			'rcommentid=' . $comment->comment_ID . 
			'&rerror=' . $botalert_saved_error .
			'&rchash=' . botalert_wp_hash_comment ($comment->comment_ID) . 
			'#commentform';
	}
	return $location;
}

/*
 * If the BotBlock score indicated Bot, from botalert_wp_check_comment, then insert their saved comment text
 * back in the comment form. 
 * @param boolean $approved
 * @return boolean $approved
 */
function botalert_wp_saved_comment() {
   if (!is_single() && !is_page())
      return;

   if ($_GET['rcommentid'] && $_GET['rchash'] == botalert_wp_hash_comment ($_GET['rcommentid'])) {
      $comment = get_comment($_GET['rcommentid']);

      $com = preg_replace('/([\\/\(\)\+\;\'\"])/e','\'%\'.dechex(ord(\'$1\'))', $comment->comment_content);
      $com = preg_replace('/\\r\\n/m', '\\\n', $com);

      echo "
      <script type='text/javascript'>
      var _botalert_wordpress_savedcomment =  '" . $com  ."';

      _botalert_wordpress_savedcomment = unescape(_botalert_wordpress_savedcomment);
      </script>
      ";

      wp_delete_comment($comment->comment_ID);
   }
}

add_filter('wp_head', 'botalert_wp_saved_comment',0);
add_filter('preprocess_comment', 'botalert_wp_check_comment',0);
add_filter('comment_post_redirect', 'botalert_wp_relative_redirect',0,2);

function botalert_wp_add_options_to_admin() {
   global $wpmu;

   if ($wpmu == 1 && is_site_admin()) {
		add_submenu_page('wpmu-admin.php', 'BotAlert/BotBlock', 'BotAlert/BotBlock', 'manage_options', __FILE__, 'botalert_wp_options_subpanel');
		add_options_page('BotAlert/BotBlock', 'BotAlert/BotBlock', 'manage_options', __FILE__, 'botalert_wp_options_subpanel');
   }
   else if ($wpmu != 1) {
		add_options_page('BotAlert/BotBlock', 'BotAlert/BotBlock', 'manage_options', __FILE__, 'botalert_wp_options_subpanel');
   }
}

function botalert_wp_options_subpanel() {
   global $wpmu;
	// Default values for the options array
	$optionarray_def = array(
		'custid'	=> '',
		'authtoken' 	=> '',
		'have_botblock' => '1',
		'treat_neutral_as_bad' => '',
		'ba_comments' => '1',
		'ba_registration' => '1',
      'botalert_error' => '<strong>ERROR</strong>: Please try again.',
      'botalert_isbot' => '<strong>ERROR</strong>: Your behavior looks suspicious. If you are a human, please try again.',
		);

	if ($wpmu != 1)
		add_option('botalert', $optionarray_def, 'BotAlert/BotBlock Options');

	/* Check form submission and update options if no error occurred */
	if (isset($_POST['submit'])) {
		$optionarray_update = array (
		'custid'	=> trim($_POST['botalert_opt_custid']),
		'authtoken'	=> trim($_POST['botalert_opt_authtoken']),
   		'have_botblock' => $_POST['have_botblock'],
		'treat_neutral_as_bad' => $_POST['treat_neutral_as_bad'],
		'ba_comments' => $_POST['ba_comments'],
		'ba_registration' => $_POST['ba_registration'],
      'botalert_error' => $_POST['botalert_error'],
      'botalert_isbot' => $_POST['botalert_isbot'],
		);
	// save updated options
	if ($wpmu == 1)
		update_site_option('botalert', $optionarray_update);
	else
		update_option('botalert', $optionarray_update);
}

	/* Get options */
	if ($wpmu == 1)
		$optionarray_def = get_site_option('botalert');
   else
		$optionarray_def = get_option('botalert');

/* =============================================================================
   BotAlert/BotBlock Admin Page and Functions
   ============================================================================= */
   
/*
 * Display an HTML <select> listing the capability options for disabling security 
 * for registered users. 
 * @param string $select_name slug to use in <select> id and name
 * @param string $checked_value selected value for dropdown, slug form.
 * @return NULL
 */
 
function botalert_dropdown_capabilities($select_name, $checked_value="") {
	// define choices: Display text => permission slug
	$capability_choices = array (
	 	'All registered users' => 'read',
	 	'Edit posts' => 'edit_posts',
	 	'Publish Posts' => 'publish_posts',
	 	'Moderate Comments' => 'moderate_comments',
	 	'Administer site' => 'level_10'
	 	);
	// print the <select> and loop through <options>
	echo '<select name="' . $select_name . '" id="' . $select_name . '">' . "\n";
	foreach ($capability_choices as $text => $capability) :
		if ($capability == $checked_value) $checked = ' selected="selected" ';
		echo '\t <option value="' . $capability . '"' . $checked . ">$text</option> \n";
		$checked = NULL;
	endforeach;
	echo "</select> \n";
 } // end botalert_dropdown_capabilities()
   
?>

<!-- ############################## BEGIN: ADMIN OPTIONS ################### -->
<div class="wrap">
	<h2>BotAlert/BotBlock Options</h2>
	<h3>About BotAlert/BotBlock</h3>
	<p>BotAlert and BotBlock are CAPTCHA replacement services. However, as opposed to all CAPTCHA solutions, BotAlert/BotBlock are invisible and do not require filling out an extra field. Learn more at <A HREF="http://pramana.com/botblock">http://pramana.com/botalert</A> and <A HREF="http://pramana.com/botblock">http://pramana.com/botblock</A>.</p>
	
   <p><strong>NOTE</strong>: If you are using some form of Cache plugin you will probably need to flush/clear your cache for changes to take effect.</p>
   
	<form name="form1" method="post" action="<?php echo $_SERVER['REDIRECT_SCRIPT_URI'] . '?page=' . plugin_basename(__FILE__); ?>&updated=true">
		<div class="submit">
			<input type="submit" name="submit" value="<?php _e('Update Options') ?> &raquo;" />
		</div>
	
	<!-- ****************** Operands ****************** -->
   <table class="form-table">
   <tr valign="top">
		<th scope="row">BotAlert/BotBlock Support Test</th>
		<td>
                    <a href="<?php echo WP_PLUGIN_URL . "/botalertbotblock/test-requirements.php"; ?>" target="_new">Test if your PHP installation will support BotAlert/BotBlock</a>
	        </td>
   </tr>
   <tr valign="top">
		<th scope="row">BotAlert/BotBlock CustID and AuthToken</th>
		<td>
                        BotAlert/BotBlock requires a CustID and an AuthToken. You can get these by creating an account at <a href="https://pramana.com/account/register" target="_blank">pramana.com/account/register</a>, then add your domain and click on &ldquo;Service URLs&rdquo; link for your domain on the My Account page.
			<br />
			<p class="re-keys">
				<!-- BotAlert custid -->
				<label style="font-weight: bold;" for="botalert_opt_custid">CustID:</label>
				<input name="botalert_opt_custid" id="botalert_opt_custid" size="40" value="<?php  echo $optionarray_def['custid']; ?>" />
				<br />
				<!-- BotAlert authtoken -->
				<label style="font-weight: bold;" for="botalert_opt_authtoken">AuthToken:</label>
				<input name="botalert_opt_authtoken" id="botalert_opt_authtoken" size="40" value="<?php  echo $optionarray_def['authtoken']; ?>" />
			</p>
	    </td>
    </tr>
  	<tr valign="top">
		<th scope="row">Comment Options</th>
		<td>
			<!-- Use BotAlert/BotBlock on the comment post -->
			<big><input type="checkbox" name="ba_comments" id="ba_comments" value="1" <?php if($optionarray_def['ba_comments'] == true){echo 'checked="checked"';} ?> /> <label for="ba_comments">Enable BotAlert/BotBlock for comments.</label></big>
			<br />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">Registration Options</th>
		<td>
			<!-- Use BotAlert/BotBlock on the registration page -->
			<big><input type="checkbox" name="ba_registration" id="ba_registration" value="1" <?php if($optionarray_def['ba_registration'] == true){echo 'checked="checked"';} ?> /> <label for="ba_registration">Enable BotAlert/BotBlock on registration form.</label></big>
			<br />
		</td>
	</tr>
   <tr valign="top">
      <th scope="row">Error Messages</th>
         <td>
            <p>The following are the messages to display when the BotBlock score comes back as negative (Bot detected) or there was a timeout or missing a POST parameter (hpmxRequestId).</p>
            <!-- Error Messages -->
            <p class="re-keys">
      			<!-- Incorrect -->
      			<label style="font-weight: bold;" for="botalert_isbot">Score indicates Bot:</label><br>
      			<input name="botalert_isbot" id="botalert_isbot" size="80" value="<?php echo $optionarray_def['botalert_isbot']; ?>" /><br><br>
	               <!-- Blank -->
      			<label style="font-weight: bold;" for="botalert_error">BotBlock timeout or missing POST parameter (hpmxRequestId):</label><br>
      			<input name="botalert_error" id="botalert_error" size="80" value="<?php echo $optionarray_def['botalert_error']; ?>" />
      			<br />
      		</p>
         </td>
      </th>
   </tr>
	 <tr valign="top">
			<th scope="row">General Settings</th>
			<td>
				<!-- select product type -->
				<div style="vertical-align: middle !important;">
					<label for="have_botblock">BotAlert or BotBlock:</label>
					<select name="have_botblock" id="have_botblock">
						<option value="1" <?php if($optionarray_def['have_botblock'] == '1'){echo 'selected="selected"';} ?>>BotBlock (real-time results) (recommended)</option>
						<option value="0" <?php if($optionarray_def['have_botblock'] == '0'){echo 'selected="selected"';} ?>>BotAlert</option>
					</select>
			    	</div>
				<br />
		    	<!-- Whether or not to be XHTML 1.0 Strict compliant -->
				<input type="checkbox" name="treat_neutral_as_bad" id="treat_neutral_as_bad" value="1" <?php if($optionarray_def['treat_neutral_as_bad'] == true){echo 'checked="checked"';} ?> /> <label for="treat_neutral_as_bad">Treat neutral scores as Bot?<strong><BR>Note</strong>: If checked, then any neutral score will be treated as a Bot. (Makes the scoring more strict)</label>
				<br />
			</td>
		</tr>
	</table>
	
	<div class="submit">
		<input type="submit" name="submit" value="<?php _e('Update Options') ?> &raquo;" />
	</div>

	</form>
   <p style="text-align: center; font-size: .85em;">&copy; Copyright 2010&nbsp;&nbsp;<a href="http://pramana.com">Pramana Inc.</a></p>
</div> <!-- [wrap] -->
<!-- ############################## END: ADMIN OPTIONS ##################### -->

<?php
}

/* =============================================================================
   Apply the admin menu
============================================================================= */

add_action('admin_menu', 'botalert_wp_add_options_to_admin');

$ba_warnings=array();

if( !function_exists("curl_init") ) array_push($ba_warnings, 'curl');

if( !isset($_POST['submit']) && (!isset($botalert_opt['custid']) || strlen($botalert_opt['custid']) != 12) )  array_push($ba_warnings, 'custid');
if( !isset($_POST['submit']) && (!isset($botalert_opt['authtoken']) || strlen($botalert_opt['authtoken']) != 36) ) array_push($ba_warnings, 'authtoken');

if( isset($_POST['submit']) && (!isset($_POST['botalert_opt_custid']) || strlen($_POST['botalert_opt_custid']) != 12) ) array_push($ba_warnings, 'custid2');
if( isset($_POST['submit']) && (!isset($_POST['botalert_opt_authtoken']) || strlen($_POST['botalert_opt_authtoken']) != 36) ) array_push($ba_warnings, 'authtoken2');

if( count($ba_warnings) > 0 ) {
   function botalert_settings_warning() {
		global $ba_warnings;
		
		$path = plugin_basename(__FILE__);
		$top = 0;
		if ($wp_version <= 2.5)
		$top = 12.7;
		else
		$top = 7;
                if( in_array('curl', $ba_warnings)) 
		    echo "<div id='botalert-warning' class='error fade-ff0000'><p><strong>BotAlert/BotBlock is not active</strong> You must install <A HREF='http://php.net/manual/en/book.curl.php'>php-cURL</A> for it to work</p></div>";
                if( in_array('custid', $ba_warnings)) 
		    echo "<div id='botalert-warning' class='updated fade-ff0000'><p><strong>BotAlert/BotBlock is not active</strong> Your BotAlert/BotBlock CustID is emtpy or invalid. Check the <a href='options-general.php?page=" . $path . "'>Plugin Settings</a></p></div>";
                if( in_array('authtoken', $ba_warnings)) 
		    echo "<div id='botalert-warning' class='updated fade-ff0000'><p><strong>BotAlert/BotBlock is not active</strong> Your BotAlert/BotBlock AuthToken is emtpy or invalid. Check the <a href='options-general.php?page=" . $path . "'>Plugin Settings</a></p></div>";

                if( in_array('custid2', $ba_warnings)) 
		    echo "<div id='botalert-warning' class='error fade-ff0000'><p><strong>BotAlert/BotBlock is not active</strong> Your BotAlert/BotBlock CustID is emtpy or invalid.</p></div>";
                if( in_array('authtoken2', $ba_warnings)) 
		    echo "<div id='botalert-warning' class='error fade-ff0000'><p><strong>BotAlert/BotBlock is not active</strong> Your BotAlert/BotBlock AuthToken is emtpy or invalid.</p></div>";
		echo "
		<style type='text/css'>
		#adminmenu { margin-bottom: 5em; }
		</style>
		";
   }
   
   if (($wpmu == 1 && is_site_admin()) || $wpmu != 1)
		add_action('admin_footer', 'botalert_settings_warning');
   return; 
}



/* =============================================================================
   End Apply the admin menu
============================================================================= */
?>
