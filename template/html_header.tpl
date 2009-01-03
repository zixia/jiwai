<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!--${
	if( $title ) {
		$title = '叽歪 / ' . $title;
	}else if ( $g_page_on && $g_page_user ) {
		$title = '叽歪 / '.$g_page_user['nameScreen'].' / '.$g_page_user['nameFull'];
		$location = JWLocation::GetLocationName($g_page_user['location']);
	} else { 
		$title = '叽歪 / 随时随地记录与分享';
	}

	if (!$description)
	{
		$description = "叽歪网 - 通过手机短信、聊天软件（QQ/MSN/GTalk/Skype）和Web，进行组建好友社区并实时与朋友分享的微博客服务。快来加入我们，踏上唧唧歪歪、叽叽歪歪的路途吧！";
	}
	$keywords ="叽叽歪歪,唧唧歪歪,歪歪,唧唧,叽叽," . htmlspecialchars($keywords);
	if (!$author && $g_page_on)
	{
		$author = $g_page_user['nameScreen'].'('.$g_page_user['nameFull'].') '.$location;
	}
	$author = $author ? $author : htmlspecialchars('叽歪网 <wo@jiwai.de>');
	$is_anonymous = JWUser::IsAnonymous($g_current_user_id);
	$rsslink = JWUtility::GetRssLink();
}-->
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>{$title}</title>
	<meta name="keywords" content="{$keywords}" />
	<meta name="description" content="{$description}" />
	<meta name="author" content="{$author}" />
<!--{if $refresh}-->
	<meta http-equiv="refresh" content="600;url={$_SERVER['SCRIPT_URI']}" />
<!--{/if}-->
<!--{foreach $rsslink AS $rssone=>$rsstitle}-->
	<link rel="alternate"  type="application/rss+xml" title="{$rsstitle} - [RSS]" href="{$rssone}" />
<!--{/foreach}-->
<!--{if $g_page_on}-->
	<link rel="openid2.provider openid.server" href="http://jiwai.de/wo/openid/server" />
	<link rel="openid2.local_id openid.delegate" href="http://jiwai.de/{$g_page_user['nameUrl']}/" />
<!--{/if}-->
	<link rel="shortcut icon" href="${JWTemplate::GetAssetUrl('/img/favicon.ico')}" type="image/icon" />
	<script type="text/javascript">window.ServerTime=${intval(1000*microtime(true))};</script>
	<script type="text/javascript">
		var current_user_id = '{$g_current_user_id}';
		var current_anonymous_user = ${$is_anonymous?'true':'false'};
		var current_in_thread = ${preg_match('#/thread/#', $_SERVER['REQUEST_URI'])?'true':'false'};
	</script>
<!--{if ($design||($g_page_on&&$g_page_user&&$design=new JWDesign($g_page_user_id))||($g_current_user_id&&$design=new JWDesign($g_current_user_id))) && $design->IsDesigned()}-->
	<link href="${$design->GetStyleUrl()}" rel="stylesheet" type="text/css" media="screen, projection" />
<!--{else}-->
	<link href="${JWTemplate::GetAssetUrl('/css/index.css')}" rel="stylesheet" type="text/css" />
<!--{/if}-->
	<link href="${JWTemplate::GetAssetUrl('/css/seekbox.css')}" media="screen, projection" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="${JWTemplate::GetAssetUrl('/lib/mootools/mootools.v1.11.js')}"></script>
	<script type="text/javascript" src="${JWTemplate::GetAssetUrl('/js/onload.js')}"></script>
	<script type="text/javascript" src="${JWTemplate::GetAssetUrl('/js/jiwai.js')}"></script>
	<script type="text/javascript" src="${JWTemplate::GetAssetUrl('/js/buddyIcon.js')}"></script>
	<script type="text/javascript" src="${JWTemplate::GetAssetUrl('/js/location.js')}"></script>
	<script type="text/javascript" src="${JWTemplate::GetAssetUrl('/js/validator.js')}"></script>
	<script type="text/javascript" src="${JWTemplate::GetAssetUrl('/js/seekbox.js')}"></script>
	<script type="text/javascript" src="${JWTemplate::GetAssetUrl('/js/action.js')}"></script>
</head>
<body>
