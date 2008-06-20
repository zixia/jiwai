<?php
require_once("../../../jiwai.inc.php");
$user_id = JWApi::GetAuthedUserId();
if( ! $user_id ){
	JWApi::RenderAuth(JWApi::AUTH_HTTP);
	die();
}
$pathParam = isset($_REQUEST['pathParam']) ? $_REQUEST['pathParam'] : null;
$format = trim( $pathParam, '.' );
if( !in_array( $format, array('json','xml') )){
	JWApi::OutHeader(406, true);
}

$rows = JWDevice::GetDeviceRowByUserId($user_id);
$result = array();
foreach ($rows as $r) {
	if ($r['type'] == 'facebook') continue;
	if (empty($r['secret'])) { //已绑定
		$result[] = array(
			'id' => (int) $r['idUser'],
			'status' => 'authenticated',
			'type' => $r['type'],
			'address' => $r['address'],
			'service' =>  ($r['type'] == 'sms') ? JWDevice::GetMobileSpNo($r['address']) : JWDevice::GetRobotFromType($r['type'] , $r['address']),
		);
	} else {
		$result[] = array(
			'id' => (int) $r['idUser'],
			'status' => 'pending',
			'type' => $r['type'],
			'address' => $r['address'],
			'service' =>  ($type == 'sms') ? JWDevice::GetMobileSpNo($r['address']) : JWDevice::GetRobotFromType($r['type'] , $r['address']),
		);
	}
}
$result = array('devices'=>$result);
switch($format){
	case 'xml':
		renderXmlReturn($result);
	break;
	case 'json':
		renderJsonReturn($result);
	break;
	default:
		JWApi::OutHeader(406, true);
}

function renderXmlReturn($result){
	$xmlString = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $result, 0 );
	echo $xmlString;
}

function renderJsonReturn($result){
	echo json_encode( $result );
}
?>
