<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined(false);

//die(var_dump($_REQUEST));

$idLoginedUser=JWLogin::GetCurrentUserId();

if ( is_int($idLoginedUser) )
{
	$param = $_REQUEST['pathParam'];

	if ( preg_match('/^\/(\d+)$/',$param,$match) ){
		$idStatus = $match[1];

		if ( JWFavourite::Destroy($idLoginedUser, $idStatus) )
		{
			$notice_html = <<<_HTML_
你已经不再收藏这条更新了。
_HTML_;
		}
		else
		{
			$error_html = <<<_HTML_
哎呀！由于系统故障，取消关注失败了……
请稍后再试。
_HTML_;
		}
	}

}

// is request ajax?
if ( ('XMLHttpRequest'==$_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_AJAX'] )
{
	// AJAX here
	$favourite_html	= JWTemplate::FavouriteAction($idStatus, false);

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
