<?php
require_once('../../../jiwai.inc.php');

$liveauth = JWLiveAuth::Instance(JWLiveAuth::AUTH_GOOGLE);
$token = $liveauth->GetToken();
if ( null == $token )
{
	$consent_url = $liveauth->GetConsentUrl();
	JWTemplate::RedirectToUrl( $consent_url );
}

$current_user_id = JWLogin::GetCurrentUserId();

$mc_key = $_SESSION['Buddy_Import_Key'] = "Contact_Google_Invite_$current_user_id";

$contact_list = $liveauth->GetContactList(true); //false will make interactive every times

if ( is_bool( $contact_list ) )
{
	$consent_url = $liveauth->GetConsentUrl();
	JWTemplate::RedirectToUrl( $consent_url );
}

JWMemcache::Instance()->Set( $mc_key, $contact_list );
JWTemplate::RedirectToUrl( '/wo/invitations/invite_not_follow' );
?>
