<?php
require_once ('../../../jiwai.inc.php');

//die(var_dump($_SERVER));
//die(var_dump($_REQUEST));


$logined_user_id	= JWLogin::GetCurrentUserId();

$param = $_REQUEST['pathParam'];
if ( preg_match('/^\/(\w+)$/',$param,$match) )
{
	$invite_code = $match[1];
    
    if( $inviter_id = JWUser::GetIdUserFromIdEncoded( $invite_code ) )
    {
        JWSession::SetInfo('inviter_id', $inviter_id );
    }else{

        $invitation_row	= JWInvitation::GetInvitationInfoByCode($invite_code);

        if ( isset($invitation_row) )
        {
            JWSession::SetInfo('invitation_id',$invitation_row['idInvitation']);

            JWSns::AcceptInvitation($invitation_row['idInvitation']);
        }
    }
}

header ("Location: /wo/account/create");
exit(0);
?>
