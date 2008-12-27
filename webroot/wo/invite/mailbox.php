<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$current_user_id = JWLogin::GetCurrentUserId();
if (empty($_POST) || empty($_POST['username']) || empty($_POST['password']))
{
	JWTemplate::RedirectToUrl( '/wo/invite/' );
}

$username = $password = $domain = null;
extract($_POST, EXTR_IF_EXISTS);
$user = $username;
if ( strpos($username, '@') ) {
	list($user, $domain) = explode('@', strtolower($username));
}
$extra = array();
//not valid
if ( !$username || !$domain ) {
	JWTemplate::RedirectBackToLastUrl();
}

//for qq, not enable now;
if ( 'qq.com' == $domain ) {
	$extra=array( 'ts' => substr($password, -10), 'p' => substr($password,0,strlen($password)-10) );
}

$contact_list = JWBuddy_Mailbox::GetContactList($user, $password, $domain, $extra);

//cache result for 30 minutes
$cache_key = md5(uniqid("${current_user_id}_mailbox"));
$cache_object = array(
	'user_id' => $current_user_id,
	'contact_list' => $contact_list,
);
$memcache = JWMemcache::Instance();
$memcache->Set( $cache_key, $cache_object, 0, 1800 );

JWTemplate::RedirectToUrl( '/wo/invite/step/'.$cache_key );
?>
