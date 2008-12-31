<?php
require_once ('../../../jiwai.inc.php');

$param = $_REQUEST['pathParam'];

if ( preg_match('/^\/([\d\w=\/]+)$/',$param,$match) )
{
	$code = $match[1];
	$invitation_row	= JWInvitation::GetInvitationInfoByCode($code);
	if ( $invitation_row['id'] ) {
		JWInvitation::Destroy($invitation_row['id']);
	}

	JWSession::SetInfo('inviter_id', null);
	JWSession::SetInfo('invitation_id', null);
	$notice_html = '我们已经取消了对你的邀请。有时间的话在叽歪de网站上看看大家都在做什么吧！';
}

if ( isset($notice_html) )
{
	JWSession::SetInfo('notice',$notice_html);
}

JWTemplate::RedirectToUrl( '/' );
?>
