<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined();

//die(var_dump($_SERVER));
//die(var_dump($_REQUEST));

function do_accept()
{
	$logined_user_id =JWLogin::GetCurrentUserId();

	$param = $_REQUEST['pathParam'];
	if ( ! preg_match('/^\/(\d+)$/',$param,$match) )
	{
		$error_html =<<<_HTML_
哎呀！系统路径好像不太正确……
_HTML_;
		return array('error_html'=>$error_html);
	}

	$friend_id = JWDB::CheckInt($match[1]);

	$friend_user_name	= JWUser::GetUserInfo($friend_id,'nameFull');

	
	$is_succ = JWSns::CreateFriends($logined_user_id, array($friend_id), true);

	if ( ! $is_succ )
	{
		$error_html = <<<_HTML_
哎呀！由于系统故障，好友添加失败了……
请稍后再尝试吧。
_HTML_;
		return array('error_html'=>$error_html);
	} 

	$is_succ = JWFriendRequest::Destroy($friend_id, $logined_user_id);

	if ( ! $is_succ )
	{
		$error_html = <<<_HTML_
哎呀！非常抱歉，虽然您已经将${friend_user_name}加为好友，但由于系统临时故障，添加好友的请求仍将被显示，请稍后再试一下吧。
_HTML_;
		return array('error_html'=>$error_html);
	}

	$notice_html = <<<_HTML_
您已经成功将${friend_user_name}添加为好友，耶！
_HTML_;
	return array('notice_html'=>$notice_html);
}

$info = do_accept();

if ( !empty($info['error_html']) )
	JWSession::SetInfo('error',$info['error_html']);

if ( !empty($info['notice_html']) )
	JWSession::SetInfo('notice',$info['notice_html']);


JWTemplate::RedirectBackToLastUrl('/');
exit(0);
?>
