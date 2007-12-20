<?php
require_once ('../../../jiwai.inc.php');

$param = $_REQUEST['pathParam'];
if ( preg_match('/^\/([\w\d=]+)$/',$param,$match) )
{
	$invite_code = $match[1];
	if( $inviter_id = JWUser::GetIdUserFromIdEncoded( $invite_code ) )
	{
		JWSession::SetInfo('inviter_id', $inviter_id );
	}
	else
	{
		$invitation_row	= JWInvitation::GetInvitationInfoByCode($invite_code);
		if ( isset($invitation_row) )
		{
			JWSession::SetInfo('invitation_id',$invitation_row['idInvitation']);
			JWSns::AcceptInvitation($invitation_row['idInvitation']);
		}
	}
}

if ( JWLogin::IsLogined() )
{
	$current_user_id = JWLogin::GetCurrentUserId();

	/* for invitation */
	$invitation_id	= JWSession::GetInfo('invitation_id');
	if ( isset($invitation_id) )
		JWSns::FinishInvitation($current_user_id, $invitation_id);

	$inviter_id = JWSession::GetInfo('inviter_id');
	if ( isset($inviter_id) )
		JWSns::FinishInvite($current_user_id, $inviter_id);
	/* end invitation */

	JWTemplate::RedirectToUrl( '/wo/' );
}
else
{
	JWTemplate::RedirectToUrl( '/wo/account/create' );
}
?>
