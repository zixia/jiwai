<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined();

//die(var_dump($_SERVER));
//die(var_dump($_REQUEST));


$param = $_REQUEST['pathParam'];
if ( preg_match('/^\/(\w+)$/',$param,$match) ){
	$invitation_code = $match[1];

	$invitation_row	= JWInvitation::GetInviteInfoByCode($invitation_code);

	$inviter_id		= $invitation_row['idInvitation'];

	if ( $inviter_id )
	{
		JWInvitation::Destroy($inviter_id);

		$notice_html = <<<_HTML_
我们已经取消了对你的邀请。有时间的话在叽歪de网站上看看大家都在做什么吧！
_HTML_;
	}
} 

if ( isset($notice_html) )
{
	JWSession::SetInfo('notice',$notice_html);
}

header ("Location: /public_timeline");
exit(0);
?>
