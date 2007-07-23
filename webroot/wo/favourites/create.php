<?php
require_once ('../../../jiwai.inc.php');

//JWLogin::MustLogined();

//die(var_dump($_SERVER));
//die(var_dump($_REQUEST));

	//"<span id='status_actions_51444762'>

$idLoginedUser=JWLogin::GetCurrentUserId();

if ( $idLoginedUser )
{
	$param = $_REQUEST['pathParam'];
	if ( preg_match('/^\/(\d+)$/',$param,$match) )
	{
		$idStatus = intval($match[1]);

		$is_succ = JWFavourite::Create($idLoginedUser, $idStatus);

		if ($is_succ )
		{
			$notice_html = <<<_HTML_
收藏成功，耶！
_HTML_;
		}
		else
		{
			$error_html = <<<_HTML_
哎呀！由于系统故障，没能够收藏成功……
请稍后再尝试吧。 
_HTML_;
		} 
	}
	else // no pathParam?
	{
		$error_html = <<<_HTML_
哎呀！系统路径好像不太正确……
_HTML_;
	}

}

// is request ajax?
if ( ('XMLHttpRequest'==$_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_AJAX'] )
{
	// AJAX here
	$favourite_html	= JWTemplate::FavouriteAction($idStatus, true);

	if ( JWStatus::IsUserOwnStatus($idLoginedUser,$idStatus) )
		$favourite_html	.= JWTemplate::TrashAction($idStatus);

	$favourite_html	= preg_replace('/"/', '\\"',$favourite_html);

	$js_str = <<<_JS_
$("status_actions_$idStatus").setHTML("$favourite_html");
JiWai.ApplyFav($("status_actions_$idStatus"));
_JS_;

	//die($js_str);
	header ( "Content-Type: text/javascript" );
	echo $js_str;
}
else // NOT ajax
{
	if ( !empty($error_html) )
		JWSession::SetInfo('error',$error_html);

	if ( !empty($notice_html) )
		JWSession::SetInfo('notice',$notice_html);


	if ( array_key_exists('HTTP_REFERER',$_SERVER) )
		$redirect_url = $_SERVER['HTTP_REFERER'];
	else
		$redirect_url = '/';

	header ("Location: $redirect_url");
}

exit(0);
?>
