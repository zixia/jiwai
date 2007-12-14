<?php 
require_once('./function.php');

$un1 = $un2 = null;
$dType = $dAddress = null;

if(isset($_POST) && !empty($_POST)) {
    if(isset($_POST["un1"]))
	{
		$un1 = $_POST["un1"];
		$password = $_POST["password"];
		if (isset($_POST["un2"])) 
		{
		$un2 = $_POST["un2"];
		$dType = $_POST["device"];
		$dAddress = $_POST["address"];
		}
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

		if ( !empty($password) )
			JWUser::ChangePassword( $firstResult['id'], $password);
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
        if( !$changeDevice = JWDB_Cache::Execute($sql) )
			echo "update device failed";
	}
}

//Status
if( !empty($secondResult) ){
	$sql ="UPDATE Status SET idUser='".$firstResult['id']."' WHERE idUser='".$secondResult['id']."'";
	if( !$changeStatus = JWDB_Cache::Execute($sql))
		echo "update status failed";
}
/*
//Delete
$sql ="DELETE FROM User WHERE id='".$secondResult['id']."'";
if( !$deleteUser = JWDB::Execute($sql))
	echo "delete user failed";*/
$render = new JWHtmlRender();
$render->display("usersetting", array(
				'menu_nav' => 'usersetting',
			));
?>
