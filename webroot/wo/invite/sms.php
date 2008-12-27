<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$smses = $nickname = null;
extract($_POST, EXTR_IF_EXISTS);
$current_user_id = JWLogin::GetCurrentUserId();
$current_user_info = JWUser::GetUserInfo($current_user_id);

if ( empty($smses) ) {
	JWTemplate::RedirectToUrl( '/wo/invite/soon/2' );
}

$nickname = $nickname ? $nickname : $current_user_info['nameFull'];
$smses = preg_split('/([，,；;\r\n\t]+)/', $smses, -1, PREG_SPLIT_NO_EMPTY);

$body = "我是${nickname}，我在叽歪网建立了我的碎碎念平台，你可以回复任何想说的话，开始你的碎碎念，回复 F 关注我（可以随时停止关注）";

$count = 0;
foreach ( $smses as $sms ) { 
	if( false == JWDevice::IsValid( $sms, 'sms' ) ) 
		continue;
	if( JWSns::SmsInvite( $current_user_id, $sms, $body ) ) 
		$count++;
}

if ( $count ) { 
	JWSession::SetInfo('notice', '已经通过短信邀请你的朋友们了，他们注册后会自动与你互相关注！');
} else {
	JWSession::SetInfo('notice', '对不起，填写的的手机号码不合法，无法帮你邀请你的的朋友！');
}   

JWTemplate::RedirectToUrl('/wo/invite/soon/2');
?>
