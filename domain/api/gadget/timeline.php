<?php
require_once("../../../jiwai.inc.php");

define ('GADGET_THEME_ROOT'		, JW_ROOT.'domain/asset/gadget/theme/');
define ('GADGET_THEME_DEFAULT'	, GADGET_THEME_ROOT.'iChat/');

//echo '<pre>'; die(var_dump($_REQUEST));

/*	1、被嵌入 gadget 的 http://JiWai.de/wo/badge/ 页面进行参数设置后供用户调用。
 * 	2、所使用的数据源 API 为：
<script type="text/javascript" 
		src="http://api.jiwai.de/gadget/statuses/1.js
			?count=20
			&selector={user|friends|public}
			&theme=iChat" 
			&gadget_div=JiWai_de_gadget_timeline_1" 
>
</script>
*/

#
# User URL Param
#
$selector	= @$_REQUEST['selector'];
$count		= @$_REQUEST['count'];
$theme		= @$_REQUEST['theme'];
if ( !empty($_REQUEST['thumb']) ){
	$thumb = $_REQUEST['thumb'];
}else{
	$thumb = 24;
}
$gadget_div	= @$_REQUEST['gadget_div'];


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

			gadget($idUser, $selector, $theme, $count, $thumb, $gadget_div);
			
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


function gadget($idUser, $statusSelector, $themeName, $countMax, $thumbSize, $gadgetDivId)
{
	if ( empty($themeName) )
		$themeName = 'iChat';

	$statusSelector = strtolower($statusSelector);
	switch ($statusSelector)
	{
		case 'user':
			break;
		case 'friends':
			break;
		case 'friends_newest':
			break;
		case 'public':
			// fall to default.
		default:
			$statusSelector = 'public';
			break;
	}
	
	$user = JWUser::GetUserInfo($idUser);

	$gadget_script_url	= "http://api.jiwai.de/statuses/${statusSelector}_timeline"
							. (("public"===$statusSelector) ? ".json" : "/$idUser.json")
							. "?count=$countMax"
							. "&thumb=$thumbSize"
							. "&callback=jiwai_de_callback"
						;


	$theme_dir		= GADGET_THEME_ROOT . $themeName . '/';

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
	else if (48!==$thumbSize) $thumbSize=24;

	if ( empty($gadgetDivId) )
		$gadgetDivId = 'JiWai_de_gadget_timeline';


	$owner_content_template			= rawurlencode(file_get_contents("$theme_dir/Outgoing/Content.html"));
	$owner_next_content_template	= rawurlencode(file_get_contents("$theme_dir/Outgoing/NextContent.html"));

	$other_content_template			= rawurlencode(file_get_contents("$theme_dir/Incoming/Content.html"));
	$other_next_content_template	= rawurlencode(file_get_contents("$theme_dir/Incoming/NextContent.html"));


	$css_template		= file_get_contents("$theme_dir/main.css");
	

	$replace_array		= array ( 	 
									// 增加 css selector 限定作用域
									 "/^(.+\s*{)/m"							=> "#$gadgetDivId $1"
									// 去掉 url 中的单引号 '
									,"/(:\s*url\s*\(\s*)'([^']+)'(\s*\))/i"	=> "$1$2$3"
								);

	//$css_template		= preg_replace("/^(.+\s*{)/m", "#$gadgetDivId $1", $css_template);
	$css_template		= preg_replace(array_keys($replace_array), array_values($replace_array),$css_template);

	$asset_number = 1;
	$asset_number_max = 6;
	$count = 0;
	do
	{

        $css_template   = preg_replace("/(:\s*)url\s*\((?!http)/i"
                                            , ("$1url($2http://asset" . $asset_number%$asset_number_max . ".JiWai.de/gadget/theme/$themeName/")
                                            , $css_template, 1, $count
                                    );
		$asset_number++;
	} while (0<$count);


	$css_template = rawurlencode($css_template);

	$js_output = <<<_JS_

var jiwai_de_html_head = document.getElementsByTagName('head')[0];

/***************************** Load Template  ***************************************/
var css_template				= unescape('$css_template');

var owner_content_template 		= unescape('$owner_content_template');
var owner_next_content_template	= unescape('$owner_next_content_template');

var other_content_template 		= unescape('$other_content_template');
var other_next_content_template	= unescape('$other_next_content_template');


var gadget_css = document.createElement("style");
gadget_css.setAttribute("type", "text/css");

if(gadget_css.styleSheet){// IE
	gadget_css.styleSheet.cssText = css_template;
} else {// w3c
	gadget_css.innerHTML = css_template;
}

jiwai_de_html_head.appendChild(gadget_css);


/***************************** Template Loaded ***************************************/

jiwai_de_gadget 				= document.getElementById("$gadgetDivId")

function relative_time(time_value) 
{
    var values = time_value.split(" ");
    time_value = values[1] + " " + values[2] + ", " + values[5] + " " + values[3];

	var parsed_date = Date.parse(time_value);

	var relative_to = (arguments.length > 1) ? arguments[1] : new Date();
	var delta = parseInt((relative_to.getTime() - parsed_date) / 1000);

	if(delta < 60) {
		return '就在刚才'
	} else if(delta < (60*60)) {
		return (parseInt(delta / 60)).toString() + ' 分钟前';
	} else if(delta < (24*60*60)) {
		return (parseInt(delta / 3600)).toString() + ' 小时前';
	}

	return (parseInt(delta / 86400)).toString() + ' 天前';
}

function jiwai_de_get_message_html(status)
{
	var status_html = status.text.replace(/(http:\/\/)([^\/ ]+)([^\s]*)/, "<a href='$1$2$3' target='_blank'>$1$2/...</a>");
	return status_html
			+ " <a href='http://jiwai.de/" + status.user.screen_name + "/statuses/" + status.id + "' target='_blank'><small>" 
			+ relative_time(status.created_at)
			+ "</small></a>";
}

function jiwai_de_get_picture_html(status)
{
	return "<a href='http://JiWai.de/" + status.user.screen_name + "/' target='_blank'>"
			+ "<img class='icon' border='0' alt='" + status.user.name + "' src='" + status.user.profile_image_url  + "' />";
			+ "</a>";
}
  

function jiwai_de_get_user_html(status)
{
	return "<a href='http://JiWai.de/" + status.user.screen_name + "/' target='_blank'>"
			+ status.user.name
			+ "</a>";
}


function jiwai_de_callback(statuses)
{

	if ( 0>=statuses.length )
		return;

	var statuses_html = "\\n";

	for ( n=0; n<statuses.length; n++ )
	{
		var status_html; // 每个 status
		var status_next_html; // 同一个用户的 next status
		var message_html // 所有 status 的 html;

		// 这条更新是用户自己的
		if ( $idUser==statuses[n].user.id )
		{
			status_html		= owner_content_template.replace(/%sender%/i	, jiwai_de_get_user_html(statuses[n]));
			status_html		= status_html.replace(/%userIconPath%/i		, jiwai_de_get_picture_html(statuses[n]));
			status_html		= status_html.replace(/%message%/i			, jiwai_de_get_message_html(statuses[n]));

			// 检查下一个(n+1) statuses 中的用户，是不是和当前用户(n)是同一人。如果是，则合并。
			while ( (n+1)<statuses.length && statuses[n+1].user.id==statuses[n].user.id )
			{
				status_next_html	= owner_next_content_template.replace(/%message%/i	,jiwai_de_get_message_html(statuses[n+1]));
				status_next_html	= status_next_html.replace(/%sender%/i				,jiwai_de_get_user_html(statuses[n+1]));
				status_html			= status_html.replace(/<div id=['"]insert['"]><\\/div>/i, status_next_html);
				n++;
			}

			statuses_html 	+= status_html;
		}
		// 这条更新是用户的好友的
		else
		{
			status_html	= other_content_template.replace(/%sender%/i, jiwai_de_get_user_html(statuses[n]));
			status_html	= status_html.replace(/%userIconPath%/i 	, jiwai_de_get_picture_html(statuses[n]));
			status_html	= status_html.replace(/%message%/i			, jiwai_de_get_message_html(statuses[n]));

			// 检查下一个(n+1) statuses 中的用户，是不是和当前用户(n)是同一人。如果是，则合并。
			while ( (n+1)<statuses.length && statuses[n+1].user.id==statuses[n].user.id )
			{
				status_next_html	= other_next_content_template.replace(/%message%/i, jiwai_de_get_message_html(statuses[n+1]));
				status_next_html	= status_next_html.replace(/%sender%/i				,jiwai_de_get_user_html(statuses[n+1]));
				status_html			= status_html.replace(/<div id=['"]insert['"]><\\/div>/i, status_next_html);
				n++;
			}

			statuses_html += status_html;
		}
	}


	statuses_div 			= document.createElement('div');
	statuses_div.innerHTML 	= statuses_html;
	
	jiwai_de_gadget.appendChild(statuses_div);

}


// 加载 JSON 格式的用户数据，并传入回调，耶！
gadget_data_js 		= document.createElement("script");
gadget_data_js.src	= '$gadget_script_url';

jiwai_de_html_head.appendChild(gadget_data_js);

_JS_;

	header ("Content-Type: text/javascript; Charset: UTF-8");
	die ($js_output);
}
?>
