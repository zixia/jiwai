<?php
require_once( dirname(__FILE__) . '/function.php');

$un = null;
$im = null;
extract($_GET, EXTR_IF_EXISTS);

$unResult = array();
$imResult = array();
if( $un ) {
	$oneResult = JWUser::GetUserInfo( $un );
	if( $oneResult ) {
		array_push( $unResult, $oneResult );
		$imResult = JWDevice::GetDeviceRowByUserId( $oneResult['id'] );
	}
}

if ( $im ) {
	$imResult = JWDevice::GetDeviceInfoByAddress($im, array('qq','sms','gtalk','msn','skype','newsmth'));
	if( $imResult ){
		$userIds = array();
		foreach($imResult as $o){
			$userIds[] = $o['idUser'];
		}
		$unResult = JWUser::GetDbRowsByIds( $userIds );
	}
}

$render = new JWHtmlRender();
$render->display("imquery", array(
			'menu_nav' => 'imquery',
			'un' => $un,
			'im' => $im,
			'unResult' => $unResult,
			'imResult' => $imResult,
			));
?>
