<?php
global $ba_api, $ba_api_ver;
$ba_api="baphp";
$ba_api_ver="1.0";

function botblock_VERDict($custid, $authtoken, $hpmxRequestId, $neutral_is_acceptable=true)
{
        $url = "http://" . $custid . ".botalert.com/VERD?custid=" . $custid . "&auth=" . $authtoken . "&reqid=" . $hpmxRequestId; 
        if(function_exists("curl_init"))
            $hpmxResult = _botalert_get_http( $url, false );
        else
            $hpmxResult = file_get_contents( $url );
error_log("hpmxResult=$hpmxResult");
        # http://pramana.com/resources/validation-results/
        if( $hpmxResult == -1 || $hpmxResult == -3 ) // -1 = Bot, -3 = Backdoor
                return false;
        if( $hpmxResult == 0 && ! $neutral_is_acceptable ) // 0 = Neutral
                return false; // Neutral, but not acceptable
        return true; // 1 = Human, others are errors, but we let them through, so we can fail-open.
}

function botalert_AUTHenticateAUTOWIRE($custid, $authtoken, $frm, $submit, $fld)
{
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
        return $botalert_snippet;
}

function botalert_AUTHenticate($custid, $authtoken)
{
	$use_ssl = false;
	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on")
		$use_ssl = true;
	$url = "http://" . $custid . ".botalert.com/AUTH?custid=" . $custid . "&auth=" . $authtoken;
	if( $use_ssl ) $url .= "&secure=1";
        if(function_exists("curl_init"))
            $botalert_snippet = _botalert_get_http( $url, false );
        else
            $botalert_snippet = file_get_contents( $url );
	return $botalert_snippet;
}

function botalert_ACODE($custid, $authtoken)
{
        global $ba_api, $ba_api_ver;
	$use_ssl = false;
	$url = "http://" . $custid . ".botalert.com/ACOD?custid=" . $custid . "&auth=" . $authtoken;
        $url .= "&ip=" . $_SERVER['REMOTE_ADDR'];
        $url .= "&api=" . $ba_api . "-" . $ba_api_ver;
        $url .= "&override=vali_triggers:001,tran_mech:4";
        $botalert_snippet = '<script type="text/javascript">';
	$botalert_snippet .= _botalert_get_http( $url );
        $botalert_snippet .= '</script>';
	return $botalert_snippet;
}

function botalert_VALIdate($custid, $authtoken, $hpmxRequestId, $refid)
{
        global $ba_api, $ba_api_ver;
        $url = "http://" . $custid . ".botalert.com/VALI?custid=" . $custid;
        $url .= "&ip=" . $_SERVER['REMOTE_ADDR'];
        $url .= "&api=" . $ba_api . "-" . $ba_api_ver;
        $url .= "&reqid=" . $hpmxRequestId;
        $url .= "&ref_id_1=" . urlencode($refid);
        $url .= "&" . $_POST["hpmxData"];
        $hpmxResult = _botalert_get_http( $url );
        return true; // 1 = Human, others are errors, but we let them through, so we can fail-open.
}

function botalert_VALIandVERD($custid, $authtoken, $hpmxRequestId, $refid, $have_botblock, $neutral_is_acceptable=true)
{
        botalert_VALIdate($custid, $authtoken, $hpmxRequestId, $refid);
        if( ! $have_botblock )
            return true;
        return botblock_VERDict($custid, $authtoken, $hpmxRequestId, $neutral_is_acceptable);
}

function _botalert_get_http($url, $append_headers=true)
{
    if(!function_exists("curl_init"))
    {
        error_log("CURL not AVAILABLE. Please install PHP-cURL");
        return "var botalert_error='curl not availble. please install';";
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
        return "-2";
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
    return $headers;
}

?>
