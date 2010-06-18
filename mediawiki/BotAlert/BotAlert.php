<?php
if (!defined('MEDIAWIKI')) die();

global $wgExtensionFunctions;

$wgExtensionFunctions[] = 'baSetup';
$wgExtensionCredits['other'][] = array(
	'name' => 'BotAlert-MediaWiki',
	'description' => 'MediaWiki extension for Pramana\'s CAPTCHA replacement products BotAlert and BotBlock',
	'url' => 'http://www.mediawiki.org/wiki/Extension:BotAlert',
	'author' => 'Pramana Inc.'
);

#global $wgBotAlertConfigCustID, $wgBotAlertConfigAuthToken, $wgBotAlertConfigHaveBotBlock, $wgBotAlertConfigNeutralScoreIsAcceptable;

#$wgBotAlertConfigCustID = "please_replace_wgBotAlertConfigCustID";
#$wgBotAlertConfigAuthToken = "please_replace_wgBotAlertConfigAuthToken";
#$wgBotAlertConfigHaveBotBlock = false;
#$wgBotAlertConfigNeutralScoreIsAcceptable = true;

require_once( 'BotAlert.i18n.php' );

global $wgBotAlert, $wgBotAlertClass, $wgBotAlertTriggers;
global $wgBotAlertConfigHaveBotBlock;
$wgBotAlert = null;
if( $wgBotAlertConfigHaveBotBlock ) {
    $wgBotAlertClass = 'BotBlock';
} else {
    $wgBotAlertClass = 'BotAlert';
}


$wgBotAlertTriggers = array();
$wgBotAlertTriggers['edit']          = false; // Would check on every edit
$wgBotAlertTriggers['createaccount'] = false;  // Special:Userlogin&type=signup
$wgBotAlertTriggers['login']      = false;  // Special:Userlogin


global $wgSpecialPages;
$wgSpecialPages['BotAlert'] = array( 'SpecialPage', 'BotAlert', '', false, false, false );

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


        if( $wgBotAlertConfigHaveBotBlock ) {
		$wgHooks['AbortNewAccount'][] = array( &$wgBotAlert, 'confirmUserCreate' );
		$wgHooks['AbortLogin'][] = array( &$wgBotAlert, 'confirmUserLogin' );
		$wgHooks['EditPage::attemptSave'][] = array( &$wgBotAlert, 'confirmEdit' );
		$wgHooks['BeforePageDisplay'][] = array( &$wgBotAlert, 'addButtonHandler' );
	}
}


function wfSpecialBotAlert( $par = null ) {
        global $wgBotAlert;
        return $wgBotAlert->showHelp();
}

class BotBlock extends BotAlert {
	function BotBlock() {
		$this->BotAlert();
	}
        function confirmUserCreate( $u, &$message ) {
                global $wgBotAlertTriggers;
                if( $wgBotAlertTriggers['createaccount'] ) {
                        if( !$this->verdict() ) {
                                $message = wfMsg( 'botblock-validate-fail' );
                                return false;
                        }
                }
                return true;
        }
	function confirmUserLogin( $u, $pass, &$retval ) {
		if( !$this->verdict() ) {
			$message = wfMsg( 'botblock-validate-fail' );
			$retval = LoginForm::WRONG_PASS;
			return false;
		}
		return true;
	}
	function confirmEdit( $editpage ) {
		if( !$this->verdict() ) {
			$message = wfMsg( 'botblock-validate-fail' );
			#$editpage->showEditForm( );
			$editpage->spamPage( );
			return false;
		}
		return true;
	}
        function verdict() {
		if( $this->VERD() ) {
                        $this->log( "human" );
                        return true;
                } else {
                        $this->log( "bot" );
                        return false;
                }
        }
 	function VERD() {
		global $wgBotAlertConfigCustID, $wgBotAlertConfigAuthToken, $wgBotAlertConfigNeutralScoreIsAcceptable;
		global $wgRequest;
		$hpmxResult = file_get_contents("http://$wgBotAlertConfigCustID.botalert.com/VERD?custid=$wgBotAlertConfigCustID&auth=$wgBotAlertConfigAuthToken&reqid=".$_REQUEST['hpmxRequestId']);
		#$this->log( "VERD result: $hpmxResult ");
		if( $hpmxResult == 1 ) return true;
		if( $wgBotAlertConfigNeutralScoreIsAcceptable and $hpmxResult == 0 ) return true; 
		return false;
	}
	
}
class BotAlert {
        static $hasRun = false;
        static $injectHere = false;

        function BotAlert() {
		$this->buttonname = "nobutton";
		$this->formname = "noform";
		$this->refid = "norefid";
        }

	function injectJS( &$parser, &$text ) {
		if (self::$hasRun or ! self::$injectHere ) return true;
		self::$hasRun = true;

		global $wgOut, $wgBotAlertConfigCustID, $wgBotAlertConfigAuthToken;
		$text .="<div class='botalert'>" .
		           "<script type=\"text/javascript\">
                                 function triggerPramana(form, refId){
                                     if(typeof SGAVY_HPMX != undefined && typeof SGAVY_HPMX.sendHPMXDataDirect != undefined) {
                                         SGAVY_HPMX.sendHPMXDataDirect(form, refId);
                                         if(form.id)
                                             setTimeout('document.getElementById(\"' + form.id + '\")' +'.submit()',250);
                                         else
                                             setTimeout('document.getElementsByName(\"' + form.name + '\")[0]' +'.submit()',250);
                                     }
                                 }
                            </script>" .
                            file_get_contents("http://$wgBotAlertConfigCustID.botalert.com/AUTH?custid=$wgBotAlertConfigCustID&auth=$wgBotAlertConfigAuthToken") .
                        "</div>\n";
		return true;
	}
	function addButtonHandler( &$out ) {
		if (! self::$injectHere ) return true;
		$text =& $out->mBodytext;
		$repl = 'onclick="triggerPramana(document.'.$this->formname.', '.$this->refid.'); return false;"';
		$text = str_replace( 'name="'.$this->buttonname.'"', 'name="'.$this->buttonname.'" '. $repl, $text );
		return true;
	}
	function injectUserLogin( &$template ) {
		global $wgBotAlertTriggers;
		if( $wgBotAlertTriggers['login'] ) {
			self::$injectHere = true;
 			$this->buttonname = "wpLoginattempt";
			$this->formname = "userlogin";
			$this->refid = "document.userlogin.wpName.value";
		}
		return true;
	}
        function injectUserCreate( &$template ) {
                global $wgBotAlertTriggers;
                if( $wgBotAlertTriggers['createaccount'] ) {
			self::$injectHere = true;
 			$this->buttonname = "wpCreateaccount";
			$this->formname = "userlogin2";
			$this->refid = "document.userlogin2.wpName.value";
                }
                return true;
        }
         function injectEditPage( &$editpage, &$out ) {
                global $wgBotAlertTriggers;
                if( $wgBotAlertTriggers['edit'] ) {
			self::$injectHere = true;
 			$this->buttonname = "wpSave";
			$this->formname = "editform";
			$this->refid = "wgUserName";
               }
                return true;
        }
        function log( $message ) {
                wfDebugLog( 'BotAlert', 'BotAlert: ' . $message );
        }
	function showHelp() {
		global $wgOut;
		$wgOut->setPageTitle( wfMsg( 'botalerthelp-title' ) );
		$wgOut->addWikiText( wfMsg( 'botalerthelp-text' ) );
	}
}

