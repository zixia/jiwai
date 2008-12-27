<?php
require_once('../../jiwai.inc.php');
JWLogin::MustLogined(false);
$current_user_id = JWLogin::GetPossibleUserId();

$jw_thread_id = $jw_status_id = $jw_status = null;
$jw_dm_user_id = $jw_dm_message_id = null;
extract($_POST, EXTR_IF_EXISTS);

//null content;
if ( !($jw_status = trim($jw_status)) )
	JWTemplate::RedirectBackToLastUrl();

//thread reply
if ( $jw_thread_id ) 
{
	$jw_status_id = $jw_status_id ? $jw_status_id : $jw_thread_id;
	$status = JWDB_Cache_Status::GetDbRowById( $jw_status_id );

	$options = array(
			'idThread' => $jw_thread_id,
			'idConference' => $status['idConference'],
			'idStatusReplyTo' => $jw_status_id,
			'idUserReplyTo' => $status['idUser'],
			); 

	if (  preg_match('/^@\s*(\S+)\s+(.+)$/ ',$jw_status, $matches)) {
		$u = JWUser::GetUserInfo( $matches[1] );
		if ( $u && $u['id'] != $stauts['idUser'] ) {
			unset($options['idUserReplyTo']);
			unset($options['idStatusReplyTo']);
		}
	}

	$is_succ = JWSns::UpdateStatus($current_user_id, $jw_status, 'web', null, 'web@jiwai.de', $options);
	if( false == $is_succ )
	{
		JWSession::SetInfo('error', '对不起，回复失败。');
	}
	else
	{
		JWSession::SetInfo('notice', '你的回复发送成功。');
	}
}
//direct message
else if ( $jw_dm_user_id || $jw_dm_message_id )
{
	if ( $jw_dm_message_id )
	{
		$message = JWMessage::GetDbRowById($jw_dm_message_id);
		if ( !$message ) 
		{
			JWTemplate::RedirectTo404NotFound();
		}
		$jw_dm_user_id = $message['idUserSender'];
	}

	$is_succ = JWSns::CreateMessage( $current_user_id, $jw_dm_user_id, $jw_status, 'web', array( 'reply_id' => $jw_dm_message_id, ));
	if( $is_succ )
	{   
		JWSession::SetInfo('notice', '悄悄话发送成功。'); 
		JWTemplate::RedirectToUrl('/wo/direct_messages/sent');
	}
} 
// normal update;
else 
{
	if ( preg_match('#\.(de|vm)/help/$#i', $_SERVER['HTTP_REFERER']))
	{
		$help_user_id  = JWUser::GetUserInfo('help', 'idUser');
		if ( $current_user_id != $help_user_id 
				&& !preg_match('/^@help /',$status) )
		{
			$status = '@help ' . $status;
		}           
	}

	$robot_msg = new JWRobotMsg();
	$robot_msg->Set( $current_user_id , 'web', $jw_status );
	$robot_msg->SetHeader( 'serverAddress', 'web@jiwai.de' );	

	if ( JWUser::IsAnonymous($current_user_id) )
	{
		$reply_msg = JWRobotLogic::ProcessMoStatus( $robot_msg );
	}
	else
	{
		$reply_msg = JWRobotLogic::ProcessMo( $robot_msg );
	}

	if( $reply_msg === false ) {
		JWLog::Instance()->Log(LOG_ERR, "Create($current_user_id, $jw_status) failed");
	}

	if( false == empty( $reply_msg ) ){
		JWSession::SetInfo('notice', $reply_msg->GetBody() );
	}
}

JWTemplate::RedirectBackToLastUrl();
?>
