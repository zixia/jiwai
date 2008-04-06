<?php
require_once('../../../jiwai.inc.php');

$token = JWLiveAuth::GetToken();
if ( null == $token )
{
	$consent_url = JWLiveAuth::GetConsentUrl();
	JWTemplate::RedirectToUrl( $consent_url );
}

$current_user_id = JWLogin::GetCurrentUserId();

$mc_key = $_SESSION['Buddy_Import_Key'] = "Contact_Live_Invite_$current_user_id";

$contact_list = JWLiveAuth::GetContactList(true);

if ( is_bool( $contact_list ) )
{
	$consent_url = JWLiveAuth::GetConsentUrl();
	JWTemplate::RedirectToUrl( $consent_url );
}

JWMemcache::Instance()->Set( $mc_key, $contact_list );
JWTemplate::RedirectToUrl( '/wo/invitations/invite_not_follow' );
?>
