<?php
require_once ('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$current_user_id = JWLogin::GetCurrentUserId();

if ( $current_user_id )
{
	$param = $_REQUEST['pathParam'];
	if ( preg_match('/^\/(\d+)$/', $param, $match) ){
		$tag_id = intval($match[1]);
		$tag_row = JWDB_Cache_Tag::GetDbRowById( $tag_id ); 
		$user_row = JWUser::GetUserInfo( $current_user_id );
                JWSns::ExecWeb($current_user_id, "on #{$tag_row['name']}", '接收此#更新通知');
	}
	else // no pathParam?
	{
		JWSession::SetInfo('error','哎呀！系统路径好像不太正确');
	}
}

JWTemplate::RedirectBackToLastUrl('/');
exit;
?>
