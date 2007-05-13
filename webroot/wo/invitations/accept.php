<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined();

//die(var_dump($_SERVER));
//die(var_dump($_REQUEST));


$logined_user_id	= JWLogin::GetCurrentUserId();

$param = $_REQUEST['pathParam'];
if ( preg_match('/^\/(\w+)$/',$param,$match) ){
	$invite_code = $match[1];

	$invitation_row	= JWInvite::GetInviteInfoByCode($invite_code);

	if ( isset($invitation_row) )
	{
		JWSession::SetInfo('invitation_id',$invitation_row['idInvitation']);

		JWInvite::Accept($invitation_row['idInvitation']);
	}
}

header ("Location: /wo/account/create");
exit(0);
?>
