<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');

$type = $username = $password = null;
extract( $_POST, EXTR_IF_EXISTS );

/**
 * fetch email contact list self;
 */
if ( 'email' == $type )
{
	list($user, $domain) = explode('@', $username);
	$extra = array();
	if ( 'qq.com' == $domain )
	{
		$extra=array( 'ts' => substr($password, -10), 'p' => substr($password,0,strlen($password)-10) );
	}
	$contact_list = JWBuddy_Mailbox::GetContactList($user, $password, $domain, $extra);
	$mc_key = JWBuddy_Import::GetCacheKeyByTypeAndUsernameAndPassword($type, $username, $password);
	$memcache = JWMemcache::Instance();
	$memcache->Set( $mc_key, $contact_list );
}
else if ( in_array( $type, array('msn', 'gtalk') ) )
{
	JWBuddy_Robot::SendImportRequest($type, $username, $password);
}
?>
