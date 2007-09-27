<?php
require_once('../../../jiwai.inc.php');

//var_dump( getUserInfo( '13645673232', '哈哈' ) );

if( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
	JWApi::OutHeader(405,true);
}

$status = null;
$phone = null;
$idConference = null;
$nickName = null;
extract($_POST, EXTR_IF_EXISTS);

$pathParam = isset($_REQUEST['pathParam']) ? $_REQUEST['pathParam'] : null;
$type = trim( $pathParam, '.' );
if( !in_array( $type, array('json','xml') )){
	JWApi::OutHeader(406, true);
}

if( $status == null || $phone == null || $idConference == null ) {
	JWApi::OutHeader(400,true);
}

$conference = JWConference::GetDbRowById( $idConference );
if( empty($conference) ){
	JWApi::OutHeader(404,true);
}
$userInfo = JWUser::GetUserInfo( $conference['idUser'] );
if( empty($userInfo) || $userInfo['idConference'] != $idConference ){
	JWApi::OutHeader(404,true);
}


$status = mb_convert_encoding( $status, "UTF-8", "GB2312,UTF-8");
$nickName = mb_convert_encoding( $nickName, "UTF-8", "GB2312,UTF-8");

$loginedUserInfo = getUserInfo( $phone, $nickName );
if( empty($loginedUserInfo) ) {
	JWApi::OutHeader(404,true);
}


$idUser = $loginedUserInfo['id'];
$device = 'sms';
$status = urlDecode( $status );
$isSignature = 'N';
$serverAddress = null;
$options = array(
		'idConference' => $idConference,
);

if( $insertedId = JWSns::UpdateStatus($idUser, $status, $device, $time=null, $isSignature, $serverAddress, $options) ){
	if( $insertedId === true ) {
		$insertedId = JWDB::GetInsertedId();
	}
	$status = JWStatus::GetStatusDbRowById( $insertedId );
	switch($type){
		case 'xml':
			renderXmlReturn($status);
		break;
		case 'json':
			renderJsonReturn($status);
		break;
		default:
			JWApi::OutHeader(406, true);
	}	
}else{
	JWApi::OutHeader(500, true);
}

function renderXmlReturn($status){
	$oStatus = JWApi::ReBuildStatus($status);
	$user = JWUser::GetUserInfo( $status['idUser'] );
	$userInfo = JWApi::ReBuildUser( $user );
	$oStatus['user'] = $userInfo;
	
	$xmlString = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $oStatus, 0, 'status' );
	echo $xmlString;
}

function renderJsonReturn($status){
	$oStatus = JWApi::RebuildStatus($status);
	$user = JWUser::GetUserInfo( $status['idUser'] );
	$userInfo = JWApi::ReBuildUser( $user );
	$oStatus['user'] = $userInfo;

	echo json_encode( $oStatus );
}



// ugly method;
function getUserInfo( $phone, $nick=null ) {

	if( false == preg_match( '/\d{11,12}/', $phone ) )
		return array();

	$vmUserId = 13000;
	$idUserBegin = 13001;
	$idUserEnd = 14000;

	$nameScreen = "vm$phone";

	$userInfo = JWUser::GetUserInfo( $nameScreen );
	if( false == empty( $userInfo ) ) {
		return $userInfo;
	}
	
	//Create or update
	if( $nick == null )
		$nick = $nameScreen;

	$mIndex = JWUser::GetUserInfo( $vmUserId, 'bio' );
	$mIndex++;

	if( $mIndex > $idUserEnd ) {
		$mIndex = $idUserBegin;
	}else if( $mIndex < $idUserBegin ){
		$mIndex = $idUserBegin;
	}

	//update vmUser
	$updateArray = array( 'bio' => $mIndex );
	JWDB::UpdateTableRow( 'User', $vmUserId, $updateArray );

	
	//Update user or create User;
	$exist = JWDB::ExistTableRow( 'User', array( 'id' => $mIndex ) );
	if( $exist ) {
		$updateArray = array(
			'nameScreen' => $nameScreen,
			'nameFull' => $nick,
		);

		if ( false == JWDB::UpdateTableRow( 'User', $mIndex, $updateArray ) ){
			return array();
		}
	}else{
		$createArray = array(
			'id' => $mIndex,
			'nameScreen' => $nameScreen,
			'nameFull' => $nick,
		);
		if( false == JWDB::SaveTableRow( 'User', $createArray ) ){
			return array();
		}
	}

	return JWUser::GetUserInfo( $nameScreen );
}
?>
