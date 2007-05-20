<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined();

//die(var_dump($_SERVER));
//die(var_dump($_REQUEST));


$logined_user_id 	= JWLogin::GetCurrentUserId();
$logined_user_row	= JWUser::GetUserInfo($logined_user_id);

if ( $logined_user_id )
{
	$param = $_REQUEST['pathParam'];
	if ( ! preg_match('/^\/(\d+)$/',$param,$match) )
	{
		$error_html = <<<_HTML_
哎呀！系统路径好像不太正确……
_HTML_;
	}
	else
	{
		$nudged_user_id = intval($match[1]);
		$nudged_user_rows	= JWUser::GetUserDbRowsByIds(array($nudged_user_id));
		$nudged_user_row	= $nudged_user_rows[$nudged_user_id];

		$send_via_device	= JWUser::GetSendViaDeviceByUserId($nudged_user_id);

		if ( 'none'==$send_via_device )
		{
			$notice_html = <<<_HTML_
$nudged_user_row[nameFull]现在不想被挠挠。。。要不稍后再试吧？
_HTML_;
		}
		else
		{
			if ( JWFriend::IsFriend($nudged_user_id, $logined_user_id) )
			{
				$nudge_message = <<<_NUDGE_
$logined_user_row[nameScreen]挠挠了你一下，提醒你更新JiWai！回复本消息既可更新你的JiWai。
_NUDGE_;

				$is_succ = JWNudge::NudgeUserIds(array($nudged_user_id), $nudge_message, 'nudge');

				if ($is_succ )
				{
					$notice_html = <<<_HTML_
我们已经帮您挠挠了$nudged_user_row[nameScreen]一下！期待很快能得到您朋友的回应。
_HTML_;
				}
				else
				{
					$error_html = <<<_HTML_
哎呀！由于系统故障，挠挠好友失败了……
请稍后再尝试吧。
_HTML_;
				}
			} 
			else
			{
				$notice_html = <<<_HTML_
哎呀！您现在不是$nudged_user_row[nameScreen]的好友，不能挠挠！
_HTML_;
			}
		}
	}

	if ( !empty($error_html) )
		JWSession::SetInfo('error',$error_html);

	if ( !empty($notice_html) )
		JWSession::SetInfo('notice',$notice_html);
}

JWTemplate::RedirectBackToLastUrl('/');

exit(0);
?>
