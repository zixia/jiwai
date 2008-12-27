<?php
require_once ('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$note = null;
extract( $_GET, EXTR_IF_EXISTS );

$current_user_id = JWLogin::GetCurrentUserId();

if ( $current_user_id )
{
	$param = $_REQUEST['pathParam'];
	if ( preg_match('/^\/(\d+)$/',$param,$match) ){
		$friend_user_id = intval($match[1]);
		$friend_row = JWUser::GetUserInfo( $friend_user_id );
		$user_row = JWUser::GetUserInfo( $current_user_id );
                JWSns::ExecWeb($current_user_id, "follow {$friend_row['nameScreen']}", '打开关注');
		if( $note ) {
			if( $exist_id = JWFollowerRequest::IsExist( $friend_row['id'], $current_user_id ) ) {
				$update_array = array( 'note' => $note, );
				JWDB::UpdateTableRow( 'FollowerRequest', $exist_id, $update_array );
			}
		}
	}
	else // no pathParam?
	{
		JWSession::SetInfo('error','哎呀！系统路径好像不太正确');
	}
}

JWTemplate::RedirectBackToLastUrl('/');
exit;
?>
