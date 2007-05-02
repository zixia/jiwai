<?php
require_once("../../../jiwai.inc.php");

define ('GADGET_THEME_ROOT'		, JW_ROOT.'domain/asset/gadget/theme/');
define ('GADGET_THEME_DEFAULT'	, GADGET_THEME_ROOT.'iChat/');

//echo '<pre>'; die(var_dump($_REQUEST));

/*
<script type="text/javascript" 
		src="http://api.jiwai.de/gadget/statuses/1.js
			?count=20
			&selector={self|friends|friends_newest}
			&theme=iChat" >
</script>
*/
#
# User URL Param
#
$selector	= @$_REQUEST['selector'];
$count		= @$_REQUEST['count'];
$theme		= @$_REQUEST['theme'];
$thumb		= @$_REQUEST['thumb'];


// rewrite param, may incluce the file ext name and user id/name
$pathParam	= $_REQUEST['pathParam'];


switch ($pathParam[0])
{
	case '/':
		// http://api.jiwai.de/gadget/timeline/1.js
		// output user_timeline

		# /1.js
		//if ( preg_match('/^\/(\d+)\.?([^/]*)$/',$pathParam,$matches) )
		if ( preg_match('/^\/(\d+)\.?([^\/]*)$/',$pathParam,$matches) )
		{
			$idUser		= $matches[1];
			$fileExt	= $matches[2];

			gadget($idUser, $selector, $theme, $count, $thumb);
			
		}
		else
		{
			// FIXME
			die ("UNSUPPORTED1");
		}

		break;

	case '.':
		// fall to default
	default:
		// http://api.jiwai.de/gadget/timeline.js
		// output public_timeline
		break;
}


exit(0);


function gadget($idUser, $statusSelector, $themeName, $countMax, $thumbSize)
{
	header ("Content-Type: text/javascript");

	switch (strtolower($statusSelector))
	{
		case 'friends':
			break;
		case 'friends_newest':
			break;
		case 'self':
			// fall to default.
		default:
			$statusType = 'self';
			break;
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

jiwai_de_cb();

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

	document.write("<h1>[$idUser] [$statusType] [$themeName] [$countMax]</h1>");
}

_JS_;

}
?>
