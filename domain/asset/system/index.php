<?php
define('NO_SESSION', true);
$pathParam = null;
extract($_REQUEST,EXTR_IF_EXISTS);
require_once("../../../jiwai.inc.php");

if ( preg_match('#^user/profile_image/(?P<id_or_name>\w+)/(?P<pic_id>\w+)/(?P<pic_size>\w+)/#',$pathParam,$matches) )
{
	$id_or_name = $matches['id_or_name'];
	$pic_id 	= $matches['pic_id'];
	$pic_size 	= $matches['pic_size'];

	JWPicture::Show($pic_id, $pic_size);

} elseif ( preg_match('#^emote/themes/(?P<theme>\w+)\.js$#',$pathParam,$matches) ) {
	$theme = $matches['theme'];
	$dir = dirname(__FILE__).'/../img/emote/';
	$file = $dir.$theme.'/theme';
	JWEmote::RenderJS($theme, file_exists($file) ? $file : $dir.'default/theme');
} elseif ( preg_match('#^(?P<idUser>\d+)\.css$#', $pathParam, $matches) ) {
	$user_id = $matches['idUser'];
	$design = new JWDesign($user_id);
	if ( $design->mIsDesigned ) {
		/*
		$last_mod_time = $design->GetLastModifiedTime();
		$format = 'D, d M Y H:i:s T';
		$last_mod_gmt = gmdate($format, $last_mod_time);
		if(@$_SERVER['HTTP_IF_MODIFIED_SINCE'] == $last_mod_gmt){
			header('HTTP/1.1 304 Not Modified');
			exit;
		}
		header("Last-Modified: $last_mod_gmt");
		header("Pragma: public");
		*/
		header('Content-Type: text/css; charset=utf-8;');
		die($design->GetStyleSheet());
	}
}
exit(0);
?>
