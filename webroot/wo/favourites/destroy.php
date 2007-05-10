<?php
require_once ('../../../jiwai.inc.php');

JWUser::MustLogined();

//die(var_dump($_REQUEST));

$idLoginedUser=JWUser::GetCurrentUserId();

if ( is_int($idLoginedUser) )
{
	$param = $_REQUEST['pathParam'];

	if ( preg_match('/^\/(\d+)$/',$param,$match) ){
		$idStatus = $match[1];

		if ( JWFavorite::Destroy($idLoginedUser, $idStatus) )
		{
			$notice_html = <<<_HTML_
您已经不再收藏这条更新了。
_HTML_;
		}
		else
		{
			$error_html = <<<_HTML_
哎呀！由于系统故障，好友删除失败了……
请稍后再试。
_HTML_;
		}
	}

}

// is request ajax?
if ( ('XMLHttpRequest'==$_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_AJAX'] )
{
	// AJAX here
	$favorite_html	= JWTemplate::FavoriteAction($idStatus, false);

	if ( JWStatus::IsUserOwnStatus($idStatus, $idLoginedUser) )
		$favorite_html	.= JWTemplate::TrashAction($idStatus);

	$favorite_html	= preg_replace('/"/', '\\"',$favorite_html);

	$js_str = <<<_JS_
$("status_actions_$idStatus").setHTML("$favorite_html");
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
