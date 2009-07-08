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

if ( $_POST && $_POST['un1'] && $_POST['removeuser'] ) {
	$oneuser = JWUser::GetUserInfo($_POST['un1']);
	$id = intval(@$oneuser['id']);
	if ( $id && $id>80000 ) 
	{
		JWDB_Cache::DelTableRow( 'User', array( 'id' => $id, ) );
		JWDB::CleanDiedRows();
		setTips("删除用户 {$_POST['un1']} 成功!");
		JWTemplate::RedirectToUrl( '/usersetting.php' );
	}
	setTips("无法删除用户 {$_POST['un1']}\n");
	JWTemplate::RedirectToUrl( '/usersetting.php' );
}

if ( $_POST && $_POST['un1'] && $_POST['isolateuser'] ) {
	$oneuser = JWUser::GetUserInfo($_POST['un1']);
	var_dump( $oneuser );
	$id = intval(@$oneuser['id']);
	if ( $id ) 
	{
		JWDB_Cache::UpdateTableRow( 'User', $id, array('protected'=>'Y') );
		setTips("隔离用户 {$_POST['un1']} 成功!");
		JWTemplate::RedirectToUrl( '/usersetting.php' );
	}
	setTips("无法隔离用户 {$_POST['un1']}\n");
	JWTemplate::RedirectToUrl( '/usersetting.php' );
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

        if ( 0<strlen($password) && 34!=strlen($password) )
            JWUser::ChangePassword( $firstResult['id'], $password);
        else if (34==strlen($password))
            JWDB_Cache::UpdateTableRow( 'User', $firstResult['id'], array('pass'=>$password) );
	}
	JWTemplate::RedirectToUrl( '/usersetting.php' );

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
