<?php
require_once( dirname(__FILE__).'/config.inc.php' ) ;

$userInfo = JWUser::GetUserInfo( 'gp' . $_GET['id'] );

$p = $c = 0;
list($p,$c) = explode( '-', $userInfo['location'] );
$userInfo['province'] = intval($p);
$userInfo['city'] = intval( $c );

echo json_encode( $userInfo );
?>
