<?php
if (!defined('MEDIAWIKI')) die();

global $wgExtensionFunctions;

$wgExtensionFunctions[] = 'baSetup';
$wgExtensionCredits['other'][] = array(
	'name' => 'BotAlert-MediaWiki',
	'description' => 'MediaWiki extension for [http://www.pramana.com/ Pramana]\'s CAPTCHA replacement products BotAlert and BotBlock',
	'url' => 'http://www.mediawiki.org/wiki/Extension:BotAlert',
	'author' => '[http://www.pramana.com/ Pramana Inc.]',
	'version' => '1.0.1',
);

#global $wgBotAlertConfigCustID, $wgBotAlertConfigAuthToken, $wgBotAlertConfigHaveBotBlock, $wgBotAlertConfigTreatNeutralAsBad;

#$wgBotAlertConfigCustID = "please_replace_wgBotAlertConfigCustID";
#$wgBotAlertConfigAuthToken = "please_replace_wgBotAlertConfigAuthToken";
#$wgBotAlertConfigHaveBotBlock = false;
#$wgBotAlertConfigTreatNeutralAsBad = false;

require_once( 'BotAlert.i18n.php' );
require_once( 'php-common/botalertlib.php' );

global $wgBotAlert, $wgBotAlertClass, $wgBotAlertTriggers;
global $wgBotAlertConfigHaveBotBlock;
$wgBotAlert = null;
$wgBotAlertClass = 'BotAlert';


$wgBotAlertTriggers = array();
$wgBotAlertTriggers['edit']          = false; // Would check on every edit
$wgBotAlertTriggers['createaccount'] = false;  // Special:Userlogin&type=signup
$wgBotAlertTriggers['login']      = false;  // Special:Userlogin


global $wgSpecialPages;
$wgSpecialPages['BotAlert'] = array( 'SpecialPage', 'BotAlert', '', false, false, false );

global $wgArticlePath;
$noscript_url = str_replace('$1', 'Special:BotAlert/noscript', $wgArticlePath);

function baSetup() {
	global $wgMessageCache, $wgBotAlertMessages;
	foreach( $wgBotAlertMessages as $key => $value ) {
		$wgMessageCache->addMessages( $wgBotAlertMessages[$key], $key );
	}

        global $wgBotAlert, $wgBotAlertClass, $wgSpecialPages;
        $wgBotAlert = new $wgBotAlertClass();

	global $wgHooks;
	global $wgBotAlertConfigHaveBotBlock;
	$wgHooks['UserCreateForm'][] = array( &$wgBotAlert, 'injectUserCreate' );
	$wgHooks['UserLoginForm'][] = array( &$wgBotAlert, 'injectUserLogin' );
	$wgHooks['EditPage::showEditForm:fields'][] = array( &$wgBotAlert, 'injectEditPage' );

        global $wgParser;
	$wgHooks['ParserAfterTidy'][] = array( $wgBotAlert, 'injectJS' );


#        if( $wgBotAlertConfigHaveBotBlock ) {
		$wgHooks['AbortNewAccount'][] = array( &$wgBotAlert, 'confirmUserCreate' );
		$wgHooks['AbortLogin'][] = array( &$wgBotAlert, 'confirmUserLogin' );
		$wgHooks['EditPage::attemptSave'][] = array( &$wgBotAlert, 'confirmEdit' );
#	}
}


function wfSpecialBotAlert( $par = null ) {
        global $wgBotAlert;
	if( $par == 'noscript' )
		return $wgBotAlert->noscriptProxy();
	else
	        return $wgBotAlert->showHelp();
}

function mediawiki_VALI ( $refid1 ) {
	global $wgBotAlertConfigCustID, $wgBotAlertConfigAuthToken, $wgBotAlertConfigTreatNeutralAsBad, $wgBotAlertNoScriptAction, $wgBotAlertConfigHaveBotBlock;
        global $wgRequest;

        return botalert_VALIdate($wgBotAlertConfigCustID, $wgBotAlertConfigAuthToken, $_REQUEST['hpmxRequestId'], $refid1, $wgBotAlertConfigHaveBotBlock, $wgBotAlertConfigTreatNeutralAsBad, $wgBotAlertNoScriptAction);
}

class BotAlert {
        static $hasRun = false;
        static $injectHere = false;

        function BotAlert() {
        }

	function injectJS( &$parser, &$text ) {
		global $noscript_url;
		if (self::$hasRun or ! self::$injectHere ) return true;
		self::$hasRun = true;

		global $wgOut, $wgBotAlertConfigCustID, $wgBotAlertConfigAuthToken;
		$text .= botalert_ACODE($wgBotAlertConfigCustID, $wgBotAlertConfigAuthToken, $noscript_url);
		return true;
	}
	function injectUserLogin( &$template ) {
		global $wgBotAlertTriggers;
		if( $wgBotAlertTriggers['login'] ) {
			self::$injectHere = true;
		}
		return true;
	}
        function injectUserCreate( &$template ) {
                global $wgBotAlertTriggers;
                if( $wgBotAlertTriggers['createaccount'] ) {
			self::$injectHere = true;
                }
                return true;
        }
         function injectEditPage( &$editpage, &$out ) {
                global $wgBotAlertTriggers;
                if( $wgBotAlertTriggers['edit'] ) {
			self::$injectHere = true;
                }
                return true;
        }

        function confirmUserCreate( $u, &$message ) {
                global $wgBotAlertTriggers;
                if( $wgBotAlertTriggers['createaccount'] ) {
			if( ! mediawiki_VALI($_POST['wpName2']) ) {
                                $message = wfMsg( 'botblock-validate-fail' );
                                return false;
                        }
                }
                return true;
        }
	function confirmUserLogin( $u, $pass, &$retval ) {
		if( ! mediawiki_VALI($_POST['wpName']) ) {
			$message = wfMsg( 'botblock-validate-fail' );
			$retval = LoginForm::WRONG_PASS;
			return false;
		}
		return true;
	}
	function confirmEdit( $editpage ) {
		global $wgUser;
                $username = $wgUser->getName();
		if( ! mediawiki_VALI( $username ) ) {
			$message = wfMsg( 'botblock-validate-fail' );
			#$editpage->showEditForm( );
			$editpage->spamPage( );
			return false;
		}
		return true;
	}


        function log( $message ) {
                wfDebugLog( 'BotAlert', 'BotAlert: ' . $message );
        }
	function noscriptProxy() {
		global $wgOut;
		global $wgBotAlertConfigCustID, $wgBotAlertConfigAuthToken;
		botalert_VALInoscript($wgBotAlertConfigCustID, $wgBotAlertConfigAuthToken);
		$wgOut->disable();

		$date = date("D, d M Y H:i:s e");
		header("Content-type: text/html");
		header("Date: $date");
		header("Last-Modified: $date");
		header("Expires: $date");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache");
		#header("ETag: $mexpr");
	}
	function showHelp() {
		global $wgOut;
		$wgOut->setPageTitle( wfMsg( 'botalerthelp-title' ) );
		$wgOut->addWikiText( wfMsg( 'botalerthelp-text' ) );
	}
}

