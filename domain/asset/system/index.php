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
}
exit(0);
?>
