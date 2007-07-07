<?php
if(!defined('TPL_COMPILED_DIR')) define('TPL_COMPILED_DIR',dirname(__FILE__).'/compiled');
if(!defined('TPL_TEMPLATE_DIR')) define('TPL_TEMPLATE_DIR',dirname(__FILE__).'/template');
require_once('../../../jiwai.inc.php');
require_once('./function.php');

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
		$unResult = JWUser::GetUserDbRowsByIds( $userIds );
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
