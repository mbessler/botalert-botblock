<?php
function botblock_VERDict($custid, $authtoken, $hpmxRequestId, $neutral_is_acceptable=1)
{
        $url = "http://" . $custid . ".botalert.com/VERD?custid=" . $custid . "&auth=" . $authtoken . "&reqid=" . $hpmxRequestId; 
        $hpmxResult = file_get_contents( $url );
        # http://pramana.com/resources/validation-results/
        if( $hpmxResult == -1 || $hpmxResult == -3 ) // -1 = Bot, -3 = Backdoor
                return false;
        if( $hpmxResult == 0 && $neutral_is_acceptable != 1 ) // 0 = Neutral
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
	$botalert_snippet = file_get_contents( $url ) 
	return $botalert_snippet;
}
?>
