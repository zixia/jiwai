<?php
require_once('../../../jiwai.inc.php');
if( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
	JWApi::OutHeader(405,true);
}

$status = null;
$idPartner = null;
$idStatusReplyTo = null;
$idUserReplyTo = null;
// geocode
$mcc = null;
$mnc = null;
$cid = null;
$lac = null;
extract($_POST, EXTR_IF_EXISTS);
$pathParam = isset($_REQUEST['pathParam']) ? $_REQUEST['pathParam'] : null;
$idStatusReplyTo = intval($idStatusReplyTo);
$idUserReplyTo = intval($idUserReplyTo);

$type = trim( $pathParam, '.' );
if( !in_array( $type, array('json','xml') )){
	JWApi::OutHeader(406, true);
}

if( !$status ) {
	JWApi::OutHeader(400,true);
}
$status = mb_convert_encoding( $status, "UTF-8", "GB2312,UTF-8");

if( ! $idUser=JWApi::GetAuthedUserId() ){
	JWApi::RenderAuth( JWApi::AUTH_HTTP );
}

$device = 'api';
$timeCreate = date("Y-m-d H:i:s");
$status = urlDecode( $status );
$isSignature = 'N';
$serverAddress = null;
$options = array(
                'idPartner' => $idPartner,
		'idStatusReplyTo' => $idStatusReplyTo ? $idStatusReplyTo : null,
		'idUserReplyTo' => $idUserReplyTo ? $idUserReplyTo : null,
            );


/**
 * Support limited robot lingo in API update
 * array('D', 'GET', 'FOLLOW', 'LEAVE')
 */
$supported_lingo = array( 'D', 'GET', 'FOLLOW', 'LEAVE' );
if ( preg_match('/^(\w+)\s+.*$/i', $status, $matches)
	&& in_array( strtoupper($matches[1]), $supported_lingo ) )
{
	$robot_msg = new JWRobotMsg();
	$robot_msg->Set( $idUser, 'api', $status, 'api.jiwai.de' );

	$reply_msg = JWRobotLogic::ProcessMo( $robot_msg );
	
	if ( false===$reply_msg )
	{
		JWApi::OutHeader(406, true);
	}
	
	$out_message = array(
		'reply_message' => $reply_msg->GetBody(),
	);

	switch ($type)
	{
		case 'xml':
			header('Content-Type: application/xml; charset=utf-8');
			echo <<<_XML_
<?xml version="1.0" encoding="UTF-8"?>
<reply_message>$out_message[reply_message]</reply_message>
_XML_;
			exit;
		case 'json':
			echo json_encode( $out_message );
			exit;
	}
}
//end

if ( null != $cid ) {
    // geocoding by cid/lac
    $options['idGeocode'] = JWGeocode::GetGeocode(
            JWGeocode::GEOCODING_FUNC_CELL,
            array(
                'mcc' => $mcc,
                'mnc' => $mnc,
                'cid' => $cid,
                'lac' => $lac
                ));
}

if( $insertedId = JWSns::UpdateStatus($idUser, $status, $device, $time=null, $isSignature, $serverAddress, $options) )
{
	if( $insertedId === true ) 
	{
		$insertedId = JWDB::GetInsertedId();
	}
	$status = JWDB_Cache_Status::GetDbRowById( $insertedId );
	if (empty($status))
		JWApi::OutHeader(406, true);

	switch($type)
	{
		case 'xml':
			renderXmlReturn($status);
		break;
		case 'json':
			renderJsonReturn($status);
		break;
		default:
			JWApi::OutHeader(406, true);
	}	
}
else
{
	JWApi::OutHeader(500, true);
}

function renderXmlReturn($status)
{
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

function renderJsonReturn($status)
{
	$oStatus = JWApi::RebuildStatus($status);
	$user = JWUser::GetUserInfo( $status['idUser'] );
	$userInfo = JWApi::ReBuildUser( $user );
	$oStatus['user'] = $userInfo;

	echo json_encode( $oStatus );
}
?>
