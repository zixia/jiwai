<?php
require_once( '../../../jiwai.inc.php' );
JWLogin::MustLogined(false);

$current_user_id = JWLogin::GetCurrentUserId();

if($_POST)
{
	$list_user_id = $_POST['list_user_id'];
	$operate = $_POST['operate'];
	$list_user_id = JWDB::CheckInt($list_user_id);
	$action_rows = JWSns::GetUserActions($current_user_id,$list_user_id);
	$action_row = $action_rows[$list_user_id];
	$list_user_info = JWUser::GetUserInfo( $list_user_id );

	if(!empty($list_user_info))
		switch( $operate)
		{   
			case 1:
				JWSns::ExecWeb($current_user_id, "on ${list_user_info['nameScreen']}", '打开此人更新通知');
				break;
			case 2:
				if(true==$action_row['follow'])
					JWSns::ExecWeb($current_user_id, "follow ${list_user_info['nameScreen']}", '只通过网页接收此人通知');
				else
					JWSns::ExecWeb($current_user_id, "off ${list_user_info['nameScreen']}", '关闭此人更新通知');
				break;
			case 3:
			default:
				JWSns::ExecWeb($current_user_id, "leave ${list_user_info['nameScreen']}", '不接收此人更新通知');
				break;
		}
	$notice = JWSession::GetInfo('notice');
	echo $notice;
}
?>
