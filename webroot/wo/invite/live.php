<?php
require_once('../../../jiwai.inc.php');

$liveauth = JWLiveAuth::Instance(JWLiveAuth::AUTH_LIVE);
$token = $liveauth->GetToken();
if ( null == $token )
{
	$consent_url = $liveauth->GetConsentUrl();
	JWTemplate::RedirectToUrl( $consent_url );
}

$current_user_id = JWLogin::GetCurrentUserId();
$cache_key = md5(uniqid("${current_user_id}_live"));

//true will make interactive every times
$contact_list = $liveauth->GetContactList(true); 

if ( is_bool( $contact_list ) )
{
	$consent_url = $liveauth->GetConsentUrl();
	JWTemplate::RedirectToUrl( $consent_url );
}

$cache_object = array( 
	'user_id' => $current_user_id,
	'contact_list' => $contact_list,
);

//we initial cache 30 minutes
$memcache = JWMemcache::Instance();
$memcache->Set( $cache_key, $cache_object, 0, 1800 );
JWTemplate::RedirectToUrl( '/wo/invite/step/'.$cache_key );
?>
