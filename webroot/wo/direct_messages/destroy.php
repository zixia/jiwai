<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined();

$current_user_id=JWLogin::GetCurrentUserId();

$param = $_REQUEST['pathParam'];

if ( preg_match('/^\/(\d+)$/',$param,$match) )
{
	$message_id = $match[1];
	$message_row = JWMessage::GetDbRowById( $message_id );
	if( empty($message_row) ||
		false == ( $message_row['idUserSender'] == $current_user_id
			|| $message_row['idUserReceiver'] == $current_user_id 
		)
	){
		JWTemplate::RedirectTo404NotFound();
	}
	
	$flag = true;
	if( $flag && $message_row['idUserSender'] == $current_user_id ) 
	{
		$flag &= JWMessage::SetMessageStatus($message_id, JWMessage::OUTBOX, JWMessage::MESSAGE_DELETE);
	}
	if( $flag && $message_row['idUserReceiver'] == $current_user_id ) 
	{
		$flag &= JWMessage::SetMessageStatus($message_id, JWMessage::INBOX, JWMessage::MESSAGE_DELETE);
	}

	if ( $flag ) 
	{
		JWSession::SetInfo('notice', '悄悄话已经被删除啦！');
	}
	else
	{
		JWSession::SetInfo('error', '哎呀！由于系统故障，删除悄悄话失败了……');
	}
}

JWTemplate::RedirectBackToLastUrl();
?>
