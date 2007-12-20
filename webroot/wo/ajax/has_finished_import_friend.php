<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');

$type = $username = $password = null;
extract( $_POST, EXTR_IF_EXISTS );

$mc_key = JWBuddy_Import::GetCacheKeyByTypeAndUsernameAndPassword($type, $username, $password);
$_SESSION['Buddy_Import_Key'] = $mc_key;
$memcache = JWMemcache::Instance();

$v = $memcache->Get( $mc_key );
if ( $v )
{
	echo "true";
}
else
{
	echo "false";
}
?>
