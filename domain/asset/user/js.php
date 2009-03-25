<?php
define('NO_SESSION', true);
require_once("../../../jiwai.inc.php");

$js = JWFarrago::GetInitJs(false);
$last_mod_time = $js['time'];
$format = 'D, d M Y H:i:s T';
$last_mod_gmt = gmdate($format, $last_mod_time);
$exp_gmt = gmdate($format, $last_mod_time+30*86400);
if(@$_SERVER['HTTP_IF_MODIFIED_SINCE'] == $last_mod_gmt){
		header('HTTP/1.1 304 Not Modified');
		exit;
}
header("Last-Modified: $last_mod_gmt");
header("Pragma: public");
header("Expires: $exp_gmt");
header('Content-Type: text/javascript; charset=utf-8;');
die($js['content']);
?>
