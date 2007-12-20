<?php
require_once ('../../../jiwai.inc.php');

$param = $_REQUEST['pathParam'];

if ( preg_match('/^\/([\d\w=\/]+)$/',$param,$match) )
{
	$invitation_code = $match[1];
	if( $inviter_id = JWUser::GetIdUserFromIdEncoded( $invitation_code ) )
	{
		JWSession::SetInfo('inviter_id', null);
	}
	else
	{
		$invitation_row	= JWInvitation::GetInviteInfoByCode($invitation_code);
		$inviter_id = $invitation_row['idInvitation'];
		if ( $inviter_id )
		{
			JWInvitation::Destroy($inviter_id);
		}
	}

	$notice_html = '我们已经取消了对你的邀请。有时间的话在叽歪de网站上看看大家都在做什么吧！';
}

if ( isset($notice_html) )
{
	JWSession::SetInfo('notice',$notice_html);
}

JWTemplate::RedirectToUrl( '/' );
?>
