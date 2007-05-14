<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined();

if ( $idUser=JWLogin::GetCurrentUserId() 
		&& array_key_exists('_method',$_REQUEST)
		&& array_key_exists('pathParam',$_REQUEST) ){

	$method = $_REQUEST['_method'];
	$param = $_REQUEST['pathParam'];

	$idDevice = null;

	if ( preg_match('/^\/(\d+)$/',$param,$match) ){
		$idDevice = $match[1];

		if ( $method==='delete' ){
			JWDevice::Destroy($idDevice);
		}
	}
}

$return_url = '/wo/device/';

if ( isset($_SERVER['HTTP_REFERER']) )
	$return_url = $_SERVER['HTTP_REFERER'];

header ("Location: $return_url");

exit(0);
?>
