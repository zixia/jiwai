<?php
require_once('../../../jiwai.inc.php');

JWLogin::MustLogined(false);

$current_user_info = JWUser::GetCurrentUserInfo();
$current_user_id = JWLogin::GetCurrentUserId();

if ( isset($_POST['invite_email_x'] ) ) 
{
	$email_addresses = $_POST['email_addresses'];
	$subject = $_POST['subject'];
	$email_addresses = preg_replace('/[，,；;\r\n\t]/', ' ', $email_addresses);
	$email_addresses = preg_split('/\s+/', trim($email_addresses), 0, PREG_SPLIT_NO_EMPTY );

	if ( do_invite_friend_with_email( $email_addresses, $current_user_info, $subject ) )
	{
		JWSession::SetInfo('notice', '已经帮你向你的朋友们发送了邮件邀请。');
	}
	else
	{
		JWSession::SetInfo('notice', '对不起，暂时无法用邮件邀请你的朋友。');
	}

	JWTemplate::RedirectBackToLastUrl( '/' );
}

if ( isset($_POST['invite_sms_x'] ) ) 
{ 
	$smss = $_POST['sms_addresses'];
	$nick = $_POST['sms_nickname'];

	$body = "我是${nick}，我在叽歪网建立了我的碎碎念平台，你可以回复任何想说的话，开始你的碎碎念，回复 F 关注我（可以随时停止关注）";

	$count = 0;
	foreach ( $smss as $sms ) 
	{ 
		if( false == JWDevice::IsValid( $sms, 'sms' ) ) 
			continue;

		if( JWSns::SmsInvite( $current_user_id, $sms, $body ) ) 
			$count++;
	}

	if ( $count ) 
	{ 
		JWSession::SetInfo('notice', '已经通过短信邀请你的朋友们了，他们注册后会自动与你互相关注！');
	}
	else
	{
		JWSession::SetInfo('notice', '对不起，填写的的手机号码不合法，无法帮你邀请你的的朋友！');
	}   

	JWTemplate::RedirectBackToLastUrl( '/' );
}

if ( isset($_POST['invite_not_follow'] ) )
{
	$friends_ids = $_POST['friends_ids'];
	$count = 0;

	if ( 0==count($friends_ids) )
	{
		JWSession::SetInfo('notice', '对不起，你没有选择任何需要关注的朋友。');
	}
	else
	{
		foreach ( $friends_ids as $friend_id )
		{
			$friend_info = JWUser::GetUserInfo( $friend_id );
			JWSns::ExecWeb($current_user_id, "on $friend_info[nameScreen]", '接收更新通知');
			$count++;
		}
		if ( 0>=$count )
		{
			JWSession::SetInfo('notice', '对不起，关注他们失败！');
			JWTemplate::RedirectBackToLastUrl('/');
		}
		else
		{
			JWSession::GetInfo('notice', '已经帮你关注了你在叽歪上的朋友。');
		}
	}

	JWTemplate::RedirectToUrl(JW_SRVNAME . "/wo/invitations/invite_not_reg" );
}

if ( isset($_POST['invite_not_reg'] ) ) 
{
	$friends_emails = $_POST['friends_emails'];

	if ( 0==count($friends_emails) )
	{
		JWSession::SetInfo('notice', '对不起，你没有选中任何待邀请的朋友。');
	}
	else
	{
		if ( do_invite_friend_with_email( $friends_emails, $current_user_info ) )
		{
			JWSession::SetInfo('notice', '已经帮你向你的朋友们发送了邮件邀请。');
		}
		else
		{
			JWSession::SetInfo('notice', '对不起，暂时无法用邮件邀请你的朋友。');
		}
	}

	JWTemplate::RedirectToUrl( '/wo/invitations/invite_finished' );
}

if ( $_FILES && isset($_FILES['friends_lists']) ) 
{
	$file_info = @$_FILES['friends_lists'];
	$emails = array();

	if ( 0===$file_info['error'] 
		&& 'text/plain' == $file_info['type'] )
	{
		$file_content = file_get_contents( $file_info['tmp_name'] );
		$contact_emails = preg_split( '/[,\s\r\n]/', $file_content, 0, PREG_SPLIT_NO_EMPTY );

		if ( do_invite_friend_with_email( $contact_emails, $current_user_info ) )
		{
			JWSession::SetInfo('notice', '已经帮你向你的朋友们发送了邮件邀请。');
		}
		else
		{
			JWSession::SetInfo('notice', '对不起，暂时无法用邮件邀请你的朋友。');
		}

	}
	else if ( $file_info['error'] != 4 ) 
	{
		switch ( $file_info['error'] )
		{
			case UPLOAD_ERR_INI_SIZE:
				JWSession::SetInfo('notice', '文件尺寸太大了，请将文件缩小后重新上传。');
				break;
			default:
				$error_html = '上传通讯录文件失败，请检查文件是否损坏，或可尝试另选文件进行上传。';
				JWSession::SetInfo('notice',$error_html);
				break;
		}
	}

	JWTemplate::RedirectBackToLastUrl( '/' );
}

function do_invite_friend_with_email( $emails, $user, $subject=null )
{
	$options = array(
		'content_type' => 'text/html',
		'template_file' => 'html/Invitation.html',
	);

	$friends_emails = isset($_POST['friends_emails']) ? $_POST['friends_emails'] : array();
	$subject = ($subject==null)
		? "你的朋友 $user[nameScreen]($user[nameFull]) 邀请你加入叽歪"
		: $subject;

	$count = 0;

	if (false==empty($emails))
	{
		foreach ( $emails as $email )
		{
			if( true == JWDevice::IsValid( $email, 'email' ) )
			{
				if ( JWMail::SendMailInvitation( $user, $email, $subject, $options ) )
					$count++;
			}
		}
	}

	return $count > 0;
}
?>
