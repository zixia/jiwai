<?php
require_once('../../../jiwai.inc.php');
JWDebug::init();

JWLogin::MustLogined(true);
$update_user_id = JWLogin::GetPossibleUserId();

if ( array_key_exists('jw_status', $_REQUEST) ){
	if ( $status = trim($_REQUEST['jw_status']) )
	{
		/*
		 *	为了 /help/ 留言板的更新都自动加上 @help
		 */
		$help_user_id	= JWUser::GetUserInfo('help', 'idUser');

		if ( preg_match('#\.(de|vm)/help/$#i', $_SERVER['HTTP_REFERER'])
				&& $update_user_id != $help_user_id
				&& !preg_match('/^@help /',$status) )
		{
			$status = '@help ' . $status;
		}

        
        /*

		if ( !JWSns::UpdateStatus($update_user_id, $status) )
			JWLog::Instance()->Log(LOG_ERR, "Create($update_user_id, $status) failed");
        */

		$robotMsg = new JWRobotMsg();
		$robotMsg->Set( $update_user_id , 'web', $status, 'web@jiwai.de' );

		if ( JWUser::IsAnonymous($update_user_id) )
		{
			$replyMsg = JWRobotLogic::ProcessMoStatus( $robotMsg );
		}
		else
		{
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

JWTemplate::RedirectBackToLastUrl('/');
?>
