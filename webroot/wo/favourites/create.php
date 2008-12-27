<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined(false);

$idLoginedUser=JWLogin::GetCurrentUserId();

if ( $idLoginedUser )
{
	$param = $_REQUEST['pathParam'];
	if ( preg_match('/^\/(\d+)$/',$param,$match) )
	{
		$idStatus = intval($match[1]);

		$is_fav = JWFavourite::IsFavourite($idLoginedUser, $idStatus);
		if($is_fav)
			$is_succ = JWFavourite::Destroy($idLoginedUser, $idStatus);
		else
			$is_succ = JWFavourite::Create($idLoginedUser, $idStatus);
		$succ = $is_fav ? "取消收藏" : "收藏";

		if ($is_succ )
		{
			echo !$is_fav ? "1" : "0";
			$notice_html = <<<_HTML_
${succ}成功！
_HTML_;
		}
		else
		{
			$error_html = <<<_HTML_
哎呀！由于系统故障，没能够${succ}成功……
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
{/*
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
*/}
else // NOT ajax
{/*
	if ( !empty($error_html) )
		JWSession::SetInfo('error',$error_html);

	if ( !empty($notice_html) )
		JWSession::SetInfo('notice',$notice_html);
*/

	if ( array_key_exists('HTTP_REFERER',$_SERVER) )
		$redirect_url = $_SERVER['HTTP_REFERER'];
	else
		$redirect_url = '/';

	header ("Location: $redirect_url");
}

exit(0);
?>
