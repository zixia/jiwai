<?php 
if(!defined('TPL_COMPILED_DIR')) define('TPL_COMPILED_DIR',dirname(__FILE__).'/compiled');
if(!defined('TPL_TEMPLATE_DIR')) define('TPL_TEMPLATE_DIR',dirname(__FILE__).'/template');
require_once('../../../jiwai.inc.php');
require_once('./function.php');

$un1 = $un2 = null;
$dType = $dAddress = null;

if(isset($_POST["submit"]) ) {
    if(isset($_POST["un1"]) && isset($_POST["un2"])) {
		$un1 = $_POST["un1"];
		$un2 = $_POST["un2"];
		$dType = $_POST["device"];
		$dAddress = $_POST["address"];
		}
}
// Device
$im1Result = array();
$im2Result = array();

$idDevice = array();
$idDevice = JWDevice::GetDeviceDbRowByAddress( $dAddress, $dType );

if( !empty( $idDevice )) {
	$idUser = $idDevice['idUser'];
}

$userInfo = array();
$userInfo = JWUser::GetDbRowById( $idUser );
if( $un2 ) {
	if( $un2==$userInfo['nameScreen']) {
	    $secondResult = JWUser::GetUserInfo( $un2 );
	    if( $secondResult ) {
	    	$im2Result = JWDevice::GetDeviceRowByUserId( $secondResult['id'] );
		}
	}
}
if( $un1 ) {
	$firstResult = JWUser::GetUserInfo( $un1 );
	if( $firstResult ) {
		$im1Result = JWDevice::GetDeviceRowByUserId( $firstResult['id'] );
		}
	}

foreach($im2Result as $second) {
	  $isAlreadyExist = 0;
	  foreach($im1Result as $first){
		if ($second['type'] == $first['type']) {
		$isAlreasyExist = 1;
		break; 
    	}
    }
    if( $isAlreadyExist==0 ) {
		$sql ="UPDATE Device SET idUser='".$firstResult['id']."' WHERE idUser='".$secondResult['id']."'";
        if( !$changeDevice = JWDB::Execute($sql) )
			echo "update device failed";
	}
}

//Status
if( !empty($secondResult) ){
	$sql ="UPDATE Status SET idUser='".$firstResult['id']."' WHERE idUser='".$secondResult['id']."'";
	if( !$changeStatus = JWDB::Execute($sql))
		echo "update status failed";
}
/*
//Delete
$sql ="DELETE FROM User WHERE id='".$secondResult['id']."'";
if( !$deleteUser = JWDB::Execute($sql))
	echo "delete user failed";*/
$render = new JWHtmlRender();
$render->display("usermerge", array(
			));
?>
