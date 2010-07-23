<?php
require_once(  dirname(__FILE__) . '/../../../wp-load.php' );
$wpmu = 0;
if (basename(dirname(__FILE__)) == "mu-plugins") $wpmu = 1;
if ($wpmu == 1)  $botalert_opt = get_site_option('botalert');
else $botalert_opt = get_option('botalert');

require_once(dirname(__FILE__) . '/php-common/botalertlib.php');

botalert_VALInoscript($botalert_opt['custid'], $botalert_opt['authtoken']);
?>
