<?php
define('NO_SESSION', true);
$pathParam = null;
extract($_REQUEST,EXTR_IF_EXISTS);
require_once("../../../jiwai.inc.php");

if ( preg_match('#^(?P<idUser>\d+)\.css$#', $pathParam, $matches) ) {
	$user_id = $matches['idUser'];
	$design = new JWDesign($user_id);
	if ( $design->mIsDesigned ) {
		$last_mod_time = $design->GetLastModifiedTime();
		$format = 'D, d M Y H:i:s T';
		$last_mod_gmt = gmdate($format, $last_mod_time);
		$exp_gmt = gmdate($format, $last_mod_time+365*86400);
		if(@$_SERVER['HTTP_IF_MODIFIED_SINCE'] == $last_mod_gmt){
			header('HTTP/1.1 304 Not Modified');
			exit;
		}
		header("Last-Modified: $last_mod_gmt");
		header("Pragma: public");
		header("Expires: $exp_gmt");
		header('Content-Type: text/css; charset=utf-8;');
		JWDB::Close();
		die($design->GetStyleSheet());
	}
}
JWDB::Close();
exit(0);
?>
