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

$type = empty($_REQUEST['type']) ? '' : strtolower(trim($_REQUEST['type']));
if ($type == 'mobile') $type = 'sms';
if ($type == 'phone') $type = 'sms';
$allowed_array = array('msn','sms','qq','gtalk','skype','yahoo','jabber','aol','newsmth','fetion');
if ( false == in_array( $type, $allowed_array ) )
{
	JWApi::OutHeader(404, true);
}

$address = empty($_REQUEST['address']) ? '' : trim($_REQUEST['address']);
if (empty($address))
{
	JWApi::OutHeader(404, true);
}

$rows = JWDevice::GetDeviceRowByUserId($user_id);
$found = false;
foreach ($rows as $r) {
	if ($r['address'] == $address && $r['type'] == $type) {
		$found = true;
		JWSns::DestroyDevice($r['id']);
	}
}
if (!$found)
{
	JWApi::OutHeader(404, true);
}

$result = array('devices'=>array());
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
