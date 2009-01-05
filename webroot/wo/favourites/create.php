<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined(false);
$current_user_id = JWLogin::GetCurrentUserId();

$is_fav = $succ = false;
if ( $current_user_id )
{
	$param = $_REQUEST['pathParam'];
	if ( preg_match('/^\/(\d+)$/',$param,$match) )
	{
		$status_id = intval($match[1]);

		$is_fav = JWFavourite::IsFavourite($current_user_id, $status_id);
		if($is_fav) {
			$is_succ = JWFavourite::Destroy($current_user_id, $status_id);
		} else {
			$is_succ = JWFavourite::Create($current_user_id, $status_id);
		}
		$succ = $is_fav ? "取消收藏" : "收藏";
		$ajax = $is_fav ? "create" : "delete";
		if ($is_succ ) {
			$notice_html = "${succ}成功！";
		} else {
			$error_html = "哎呀！由于系统故障，没能够${succ}成功…… 请稍后再尝试吧。 ";
		} 
	}
	else // no pathParam?
	{
		$error_html = "哎呀！系统路径好像不太正确……";
	}

}

if (false==empty($_POST)) {
	die($ajax);
} else {
	JWSession::SetInfo(($notice_html | $error_html));
}

JWTemplate::RedirectBackToLastUrl('/');
?>
