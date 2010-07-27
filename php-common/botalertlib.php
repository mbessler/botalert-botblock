<?php
global $ba_api, $ba_api_ver, $ba_api_err;
$ba_api="baphp";
$ba_api_ver="1.1";
$ba_api_err="-100";

function botblock_VERDict($custid, $authtoken, $hpmxRequestId, $neutral_is_acceptable=true, $ba_noscript_action='1')
{
        $url = "http://" . $custid . ".botalert.com/VERD?custid=" . $custid . "&auth=" . $authtoken . "&id=" . $hpmxRequestId; 
        if(function_exists("curl_init"))
            $hpmxResult = _botalert_get_http( $url, false );
        else
            $hpmxResult = file_get_contents( $url );

        # http://pramana.com/resources/validation-results/
        if( $hpmxResult == -1 || $hpmxResult == -3 ) // -1 = Bot, -3 = Backdoor
                return false;
        if( $hpmxResult == 0 && ! $neutral_is_acceptable ) // 0 = Neutral
                return false; // Neutral, but not acceptable
        if( $hpmxResult == -4 && $ba_noscript_action == '1' )
                return false; // reject noscript user, when $ba_noscript_action is on
        return true; // 1 = Human, others are errors, but we let them through, so we can fail-open.
}

function botalert_AUTHenticateAUTOWIRE($custid, $authtoken, $frm, $submit, $fld)
{
	global $ba_api_err;
        $use_ssl = false;
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on")
                $use_ssl = true;
        $url = "http://" . $custid . ".botalert.com/AUTH?custid=" . $custid . "&auth=" . $authtoken;
        if( $use_ssl ) $url .= "&secure=1";
        $url .= "&autowire=$frm|$submit|$fld";
        if(function_exists("curl_init"))
            $botalert_snippet = _botalert_get_http( $url, false );
        else
            $botalert_snippet = file_get_contents( $url );
        if($botalert_snippet == $ba_api_err) $botalert_snippet = "";
        return $botalert_snippet;
}

function botalert_AUTHenticate($custid, $authtoken)
{
	global $ba_api_err;
	$use_ssl = false;
	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on")
		$use_ssl = true;
	$url = "http://" . $custid . ".botalert.com/AUTH?custid=" . $custid . "&auth=" . $authtoken;
	if( $use_ssl ) $url .= "&secure=1";
        if(function_exists("curl_init"))
            $botalert_snippet = _botalert_get_http( $url, false );
        else
            $botalert_snippet = file_get_contents( $url );
        if($botalert_snippet == $ba_api_err) $botalert_snippet = "";
	return $botalert_snippet;
}

function botalert_ACODE($custid, $authtoken)
{
        global $ba_api, $ba_api_ver, $ba_api_err;
	$use_ssl = false;
	$url = "http://" . $custid . ".botalert.com/ACOD?custid=" . $custid . "&auth=" . $authtoken;
        $url .= "&ip=" . $_SERVER['REMOTE_ADDR'];
        $url .= "&api=" . $ba_api . "-" . $ba_api_ver;
        $url .= "&override=vali_triggers:001,tran_mech:4,script:1";
        $botalert_snippet = '';
        //$botalert_snippet .= '<script type="text/javascript">';
	$botalert_snippet .= _botalert_get_http( $url );
        //$botalert_snippet .= '</script>';
        //replace ##PHAJ2BAF## with noscript proxy page
	if($botalert_snippet == $ba_api_err) $botalert_snippet = "";
        $botalert_snippet = str_replace('##PHAJ2BAF##','/wp-content/plugins/botalertbotblock/noscript.php',$botalert_snippet);
	return $botalert_snippet;
}

function botalert_VALIdate($custid, $authtoken, $hpmxRequestId, $refid, $have_botblock, $neutral_is_acceptable=true, $ba_noscript_action='1')
{
        global $ba_api, $ba_api_ver;
        $url = "http://" . $custid . ".botalert.com/VALI?custid=" . $custid . "&auth=" . $authtoken;
        $url .= "&ip=" . $_SERVER['REMOTE_ADDR'];
        $url .= "&api=" . $ba_api . "-" . $ba_api_ver;
        $url .= "&id=" . $hpmxRequestId;
        $url .= "&ref_id_1=" . urlencode($refid);
        if( isset($_POST["hpmxData"]) && strlen($_POST["hpmxData"]) > 0 )
            $url .= "&" . $_POST["hpmxData"];
        else
            $url .= "&_data=direct";
        $hpmxResult = _botalert_get_http( $url );
        if( ! $have_botblock )
            return true;  // BotAlert, but not BotBlock, let through because BotAlert does metrics only, no real-time results.
        # http://pramana.com/resources/validation-results/
        if( $hpmxResult == -1 || $hpmxResult == -3 ) // -1 = Bot, -3 = Backdoor
                return false;
        if( $hpmxResult == 0 && ! $neutral_is_acceptable ) // 0 = Neutral
                return false; // Neutral, but not acceptable
        if( $hpmxResult == -4 && $ba_noscript_action == '1' )
                return false; // reject noscript user, when $ba_noscript_action is on
        return true; // 1 = Human, others are errors, but we let them through, so we can fail-open.
}

function botalert_VALInoscript($custid, $authtoken)
{
        global $ba_api, $ba_api_ver;
        $url = "http://" . $custid . ".botalert.com/VALI?custid=" . $custid . "&auth=" . $authtoken;
        $url .= "&ip=" . $_SERVER['REMOTE_ADDR'];
        $url .= "&api=" . $ba_api . "-" . $ba_api_ver;
        $url .= "&id=" . (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
        $url .= "&ns=" . (isset($_REQUEST['ns']) ? $_REQUEST['ns'] : '1');
        if( isset($_REQUEST['if']) ) $url .= "&if=".$_REQUEST['if'];
        if( isset($_REQUEST['i']) ) $url .= "&i=".$_REQUEST['i'];
        $hpmxResult = _botalert_get_http( $url );
        return true;
}

function botalert_VALIandVERD($custid, $authtoken, $hpmxRequestId, $refid, $have_botblock, $neutral_is_acceptable=true, $ba_noscript_action='1')
{
        botalert_VALIdate($custid, $authtoken, $hpmxRequestId, $refid, $have_botblock, $neutral_is_acceptable, $ba_noscript_action);
        if( ! $have_botblock )
            return true;
        return botblock_VERDict($custid, $authtoken, $hpmxRequestId, $neutral_is_acceptable, $ba_noscript_action);
}

function _botalert_get_http($url, $append_headers=true)
{
    global $ba_api_err;
    if(!function_exists("curl_init"))
    {
        error_log("CURL not AVAILABLE. Please install PHP-cURL");
        return "<div id='botalert'><!-- BotAlert/BotBlock not active: cURL not available -></div>";
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 6000);
    if( $append_headers )
    {
        curl_setopt($ch, CURLOPT_ENCODING, $_SERVER['HTTP_ACCEPT_ENCODING']);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, _botalert_get_headers());
    }
    ob_start();
    $c = curl_exec ($ch);
    $info = curl_getinfo($ch);
    if (curl_errno($ch))
    {
        error_log("cURL failure: " . curl_error($ch));
        curl_close ($ch);
    	ob_end_clean();
        return $ba_api_err;
    }
    curl_close ($ch);
    $string = ob_get_contents();
    ob_end_clean();
    return $string;
}

function _botalert_get_headers() 
{
    $headers = array();
    $no_include = array( 'user-agent', 'host', 'cookie');
    $isRefererAdded = false;
    foreach ($_SERVER as $k => $v)
    {
        if( substr($k, 0, 5) != "HTTP_" )
            continue;
        $k = str_replace('_', '-', substr($k, 5));
        if( ! in_array(strtolower($k),$no_include) ) 
            array_push($headers, $k . ': ' . $v);
    }
    $use_ssl=false;
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") $use_ssl = true;
    $port = (isset($_SERVER["SERVER_PORT"]) && ((!$use_ssl && $_SERVER["SERVER_PORT"] != "80") || ($use_ssl && $_SERVER["SERVER_PORT"] != "443")));
    $port = ($port) ? ':'.$_SERVER["SERVER_PORT"] : '';
    $xurl = "http" . ($use_ssl ? 's' : '') ."://" . $_SERVER["SERVER_NAME"] . $port . $_SERVER["REQUEST_URI"];
    if(strpos($xurl, "?") !== FALSE) $xurl=substr($xurl,0,strpos($xurl, "?"));
    array_push($headers, "x-pr-pageuri: $xurl");
    return $headers;
}

?>
