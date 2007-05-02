<?php
define ('GADGET_THEME_ROOT'		, JW_ROOT.'domain/asset/gadget/theme/');
define ('GADGET_THEME_DEFAULT'	, GADGET_THEME_ROOT.'iChat/');

function gadget($idUser, $statusType, $themeName, $countMax, $thumbSize)
{
	$id_user 	= JWUser::GetUserInfoByName($nameScreen, 'id');

	switch (strtolower($statusType))
	{
		case 'self':
		case 'friends':
		case 'friends_single':
		default:
			$statusType = 'net_dist';
	}


	$theme_dir		= GADGET_THEME_ROOT . $themeName . '/';
	$theme_url		= 'http://asset.jiwai.de/gadget/theme/' . $themeName . '/';
	if ( !file_exists($theme_dir) )
	{
		error_log ("gadget can't find theme [$themeName] @ [$theme_dir]");
		$theme_dir 	= GADGET_THEME_DEFAULT;
	}
	//$theme_mtime	= filemtime($theme_dir);


	$countMax		= intval($countMax);
	if ($countMax<=0 || $countMax>40) 
		$countMax=JWStatus::DEFAULT_STATUS_NUM;


	$thumbSize	= intval($thumbSize);
	if ($thumbSize<=0) $thumbSize=24;
	else if (24!==$thumbSize) $thumbSize=48;



	$self_content_template			= rawurlencode(file_get_contents("$theme_dir/Outgoing/Content.html"));
	$self_next_content_template		= rawurlencode(file_get_contents("$theme_dir/Outgoing/NextContent.html"));

	$other_content_template			= rawurlencode(file_get_contents("$theme_dir/Incoming/Content.html"));
	$other_next_content_template	= rawurlencode(file_get_contents("$theme_dir/Incoming/NextContent.html"));

	$css_template			= rawurlencode(file_get_contents("$theme_dir/main.css"));

	echo <<<_JS_
var css_template				= unescape('$css_template');

var self_content_template 		= unescape('$self_content_template');
var self_next_content_template 	= unescape('$self_next_content_template');

var other_content_template 		= unescape('$other_content_template');
var other_next_content_template	= unescape('$other_next_content_template');


re 		= / url\('/ig;
img_url	=" url('$theme_url";
css_template	= css_template.replace(re, img_url);

document.write('<style type="text/css">' + css_template + "</style>");

var one_status;

function jiwai_de_cb(status_list)
{
	one_status	= self_status_template.replace(/%message%/i, 'Hello, Girl! <a href="jiwai.de"><small>一小时前</small></a>');
	one_status	= one_status.replace(/%sender%/i, 'zixia');
	one_status	= one_status.replace(/%userIconPath%/i, 'http://beta.jiwai.de/zixia/picture/thumb24');
	document.write(one_status);
	document.write(one_status);
	document.write(one_status);

	one_status	= other_status_template.replace(/%sender%/i, 'daodao');
	one_status	= one_status.replace(/%message%/i, 'Hello, Boy! <a href="jjww.com"><small>5分钟前</small></a>');
	one_status	= one_status.replace(/%userIconPath%/i, 'http://beta.jiwai.de/daodao/picture/thumb24');
	document.write(one_status);

	document.write("<h1>[$nameScreen] [$statusType] [$themeName] [$countMax]</h1>");
}

_JS_;

}
?>
