<?php
require_once('../../../jiwai.inc.php');
JWDebug::init();

JWLogin::MustLogined();

if ( array_key_exists('status', $_REQUEST) ){
	if ( $status = $_REQUEST['status'] )
	{
		$idUser = JWUser::GetCurrentUserInfo('id');


		/*
		 *	为了 /help/ 留言板的更新都自动加上 @help
		 */
		$help_user_id	= JWUser::GetUserInfo('help', 'idUser');

		if ( preg_match('#\.(de|vm)/help/$#i', $_SERVER['HTTP_REFERER'])
				&& $idUser != $help_user_id
				&& !preg_match('/^@help /',$status) )
		{
			$status = '@help ' . $status;
		}

        
        /*

		if ( !JWSns::UpdateStatus($idUser, $status) )
			JWLog::Instance()->Log(LOG_ERR, "Create($idUser, $status) failed");
        */

        $robotMsg = new JWRobotMsg();
        $robotMsg->Set( $idUser , 'web', $status, 'web' );
        $replyMsg = JWRobotLogic::ProcessMo( $robotMsg );
        if( $replyMsg === false ) {
			JWLog::Instance()->Log(LOG_ERR, "Create($idUser, $status) failed");
        }
        if( false == empty( $replyMsg ) ){
            JWSession::SetInfo('notice', $replyMsg->GetBody() );
        }
	}
}

JWTemplate::RedirectBackToLastUrl("Location: /wo/");
?>
