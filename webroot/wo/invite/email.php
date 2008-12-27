<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$emails = null;
extract($_POST, EXTR_IF_EXISTS);
$emails = preg_split('/([，,；;\r\n\t]+)/', $emails, -1, PREG_SPLIT_NO_EMPTY);

$current_user_id = JWLogin::GetCurrentUserId();
$current_user_info = JWUser::GetUserInfo($current_user_id);
$subject = "你的朋友 {$current_user_info['nameScreen']}({$current_user_info['nameFull']}) 邀请你加入叽歪";
$count = 0;

if (!empty($emails)) {
	if ( JWSns::EmailInvite($emails, $current_user_info)) {
		JWSession::SetInfo('notice', '已经帮你向你的朋友们发送了邮件邀请。');
	} else {
		JWSession::SetInfo('notice', '对不起，暂时无法用邮件邀请你的朋友。');
	}
}else if($_POST){
	JWSession::SetInfo('notice', '对不起，你没有填写任何邮件地址。');
}

JWTemplate::RedirectToUrl('/wo/invite/soon/1');
?>
