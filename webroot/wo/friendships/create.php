<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined();

//die(var_dump($_SERVER));
//die(var_dump($_REQUEST));

function do_create()
{
	$idLoginedUser=JWLogin::GetCurrentUserId();

	$param = $_REQUEST['pathParam'];
	if ( ! preg_match('/^\/(\d+)$/',$param,$match) )
	{
/*		$error_html =<<<_HTML_
哎呀！系统路径好像不太正确……
_HTML_;
		return array('error_html'=>$error_html);*/
		$idPageUser = JWUser::GetUserInfo(trim(substr($param, 1)),'id');
	} else {
		$idPageUser = intval($match[1]);
	}
	$page_user_name	= JWUser::GetUserInfo($idPageUser,'nameFull');
	if (!$page_user_name) {
		$error_html =<<<_HTML_
哎呀！系统路径好像不太正确……
_HTML_;
		return array('error_html'=>$error_html);
	}
	// 如果页面用户设置了保护，并且页面用户没有添加当前登录用户位好友，则需要发送验证请求
	if ( JWUser::IsProtected($idPageUser) && !JWFollower::IsFollowing($idPageUser, $idLoginedUser) )
	{
		if ( JWFriendRequest::IsExist($idLoginedUser, $idPageUser) )
		{
			$notice_html =<<<_HTML_
你向${page_user_name}发送的关注请求，他还没有回应，再等等吧。
_HTML_;
			return array('notice_html'=>$notice_html);
		}
				
		$is_succ = JWSns::CreateFriendRequest($idLoginedUser, $idPageUser, empty($_GET['note']) ? '' : $_GET['note']);

		if ($is_succ )
		{
			$notice_html =<<<_HTML_
已经向${page_user_name}发送了关注请求，希望能很快得到回应。
_HTML_;
			return array('notice_html'=>$notice_html);
		}
		else
		{
			$error_html=<<<_HTML_
哎呀！由于系统故障，发送关注请求失败了……
请稍后再尝试吧。
_HTML_;
			return array('error_html'=>$error_html);
		}
	}
	else
	{
var_dump(111);
		$is_succ = JWSns::CreateFriends($idLoginedUser, array($idPageUser));
var_dump(222);

		if ($is_succ )
		{
			$notice_html = <<<_HTML_
已经关注${page_user_name}，耶！
_HTML_;
			return array('notice_html'=>$notice_html);
		}
		else
		{
			$error_html = <<<_HTML_
哎呀！由于系统故障，关注此人失败了……
请稍后再尝试吧。
_HTML_;
			return array('error_html'=>$error_html);
		} 
	}
}

$info = do_create();

if ( !empty($info['error_html']) )
	JWSession::SetInfo('error',$info['error_html']);

if ( !empty($info['notice_html']) )
	JWSession::SetInfo('notice',$info['notice_html']);

JWTemplate::RedirectBackToLastUrl('/');
exit(0);
?>
