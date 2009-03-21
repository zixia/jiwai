<?php
require_once('../../../jiwai.inc.php');
JWUtility::MustPost();
JWUtility::CheckCrumb();
JWLogin::MustLogined(true);
$update_user_id = JWLogin::GetPossibleUserId();

if ( array_key_exists('jw_status', $_REQUEST) ){
	if ( $status = trim($_REQUEST['jw_status']) )
	{
		/**
		 *	为了 /help/ 留言板的更新都自动加上 @help
		 */
		$help_user_id = JWUser::GetUserInfo('help', 'idUser');
		if (preg_match('#\.(de|vm)/help/$#i',$_SERVER['HTTP_REFERER'])
				&& $update_user_id != $help_user_id
				&& !preg_match('/^@help /',$status)) {
			$status = '@help ' . $status;
		}
		
		//Intercept for tag|thread reply|update
		tag_or_thread_updater($status);

		$robotMsg = new JWRobotMsg();
		$robotMsg->Set( $update_user_id , 'web', $status );
		$robotMsg->SetHeader( 'serverAddress', 'web@jiwai.de' );
		if (JWUser::IsAnonymous($update_user_id)) {
			$replyMsg = JWRobotLogic::ProcessMoStatus( $robotMsg );
		} else {
			$replyMsg = JWRobotLogic::ProcessMo( $robotMsg );
		}

		if( $replyMsg === false ) {
			JWLog::Instance()->Log(LOG_ERR, "Create($update_user_id, $status) failed");
		}
		if( false == empty( $replyMsg ) ){
			JWSession::SetInfo('notice', $replyMsg->GetBody() );
		}
	}
}


function tag_or_thread_updater($status) 
{
	global $update_user_id;

	$jw_ruid = $jw_rsid = $jw_rtid = null;
	extract( $_POST, EXTR_IF_EXISTS );

	$options_info = array();
	if ( ($jw_ruid && $jw_rsid) ) {
		$status_row = JWDB_Cache_Status::GetDbRowById( $jw_rsid );
		$jw_thid = empty($status_row['idThread']) 
			? $status_row['id'] : $status_row['idThread'];
		$options_info = array(
			'idThread' => $jw_thid,
			'idConference' => $status_row['idConference'],
		);
	} else if ($jw_rtid) {
		$options_info['idTag'] = $jw_rtid;
	} else {
		return false;
	}
	
	$is_succ = JWSns::UpdateStatus($update_user_id, $status, 'web', null, 'web@jiwai.de', $options_info);
	if( false == $is_succ ) {
		JWSession::SetInfo('error', '对不起，叽歪失败。'); 
	} else {
		JWSession::SetInfo('notice', '你的叽歪发送成功。');
	}
	JWTemplate::RedirectBackToLastUrl('/');
}

JWTemplate::RedirectBackToLastUrl('/');
?>
