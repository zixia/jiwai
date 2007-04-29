<?php
define ('GADGET_THEME_ROOT'		, JW_ROOT.'domain/asset/gadget/theme/');
define ('GADGET_THEME_DEFAULT'	, GADGET_THEME_ROOT.'iChat/');

function gadget($nameScreen, $statusType, $themeName, $numMax)
{
	$id_user 	= JWUser::GetUserInfoByName($nameScreen, 'id');

	switch (strtolower($statusType))
	{
		case 'self':
		case 'net':
		case 'net_dist':
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
	$theme_mtime	= filemtime($theme_dir);

	$num_max	= intval($numMax);
	if ($numMax<=0) $numMax=7;
	if ($numMax>40) $numMax=40;


	$self_status_template	= rawurlencode(file_get_contents("$theme_dir/Outgoing/Content.html"));
	$other_status_template	= rawurlencode(file_get_contents("$theme_dir/Incoming/Content.html"));

	$css_template			= rawurlencode(file_get_contents("$theme_dir/main.css"));

	echo <<<_JS_
var css_template			= unescape('$css_template');
var self_status_template 	= unescape('$self_status_template');
var other_status_template 	= unescape('$other_status_template');

re = / url\('/ig;
css_template	= css_template.replace(re, " url('$theme_url");
document.write('<style type="text/css">' + css_template + "</style>");

var one_status;

one_status	= self_status_template.replace(/%message%/i, 'Hello, Girl!');
one_status	= one_status.replace(/%sender%/i, 'zixia');
one_status	= one_status.replace(/%userIconPath%/i, 'http://beta.jiwai.de/zixia/picture/thumb24');
document.write(one_status);

one_status	= other_status_template.replace(/%sender%/i, 'daodao');
one_status	= one_status.replace(/%message%/i, 'Hello, Boy!');
one_status	= one_status.replace(/%userIconPath%/i, 'http://beta.jiwai.de/daodao/picture/thumb24');
document.write(one_status);

document.write("<h1>[$nameScreen] [$statusType] [$themeName] [$numMax]</h1>");

_JS_;
}
?>
