<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined();

//die(var_dump($_REQUEST));

$logined_user_id=JWLogin::GetCurrentUserId();

$param = $_REQUEST['pathParam'];

if ( preg_match('/^\/(\d+)$/',$param,$match) )
{
	$message_id = $match[1];

    //var_dump($_REQUEST);
    $UserOwnMessage = JWMessage::IsUserOwnMessage($logined_user_id, $message_id);
    //var_dump($UserOwnMessage);
	if ( ! $UserOwnMessage )
	{
		JWTemplate::RedirectTo404NotFound();
		exit(0);
	}

	//if ( JWMessage::Destroy($message_id) )
	if ( JWMessage::SetMessageStatus($message_id, $UserOwnMessage, JWMessage::MESSAGE_DELETE) ) 
	{
		$notice_html = <<<_HTML_
悄悄话已经被删除啦！
_HTML_;
	}
	else
	{
		$error_html = <<<_HTML_
哎呀！由于系统故障，删除悄悄话失败了……
请稍后再试。
_HTML_;
	}
}

if ( !empty($error_html) )
	JWSession::SetInfo('error',$error_html);

if ( !empty($notice_html) )
	JWSession::SetInfo('notice',$notice_html);


JWTemplate::RedirectBackToLastUrl();
exit(0);
?>
