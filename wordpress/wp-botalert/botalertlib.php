<?php
function botblock_VERDict($custid, $authtoken, $hpmxRequestId, $neutral_is_acceptable=1)
{
	$url = "http://" . $custid . ".botalert.com/VERD?custid=" . $custid . "&auth=" . $authtoken . "&reqid=" . $hpmxRequestId; 
	$hpmxResult = file_get_contents( $url ); 
	if( $hpmxResult == 1 ) // Human
		return true; 
	elseif( $hpmxResult == 0 && $neutral_is_acceptable == 1 ) 
		return true; // Neutral, but acceptable 
	return false;
}

function botalert_AUTHenticate($custid, $authtoken)
{
	$use_ssl = false;
	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on")
		$use_ssl = true;
	$url = "http://" . $custid . ".botalert.com/AUTH?custid=" . $custid . "&auth=" . $authtoken;
	if( $use_ssl ) $url .= "&secure=1";
	$botalert_snippet = 
	'<div class="botalert">
		<script type="text/javascript">
			function triggerPramana(form, refId){
				if(typeof SGAVY_HPMX != undefined && typeof SGAVY_HPMX.sendHPMXDataDirect != undefined) {
					SGAVY_HPMX.sendHPMXDataDirect(form, refId);
					setTimeout(\'document.getElementById("\' + frm.id + \'")\' +\'.submit()\',50);
				}
			}
		</script>' .
		file_get_contents( $url ) .'
	</div>';
	return $botalert_snippet;
}
?>