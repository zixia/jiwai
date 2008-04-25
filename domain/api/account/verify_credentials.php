<?php
require_once('../../../jiwai.inc.php');

$pathParam = isset($_REQUEST['pathParam']) ? $_REQUEST['pathParam'] : null;

$type = trim( $pathParam, '.' );
if( !in_array( $type, array('json','xml') )){
	JWApi::OutHeader(406, true);
}

$idUser = JWApi::GetAuthedUserId();
if( ! $idUser ){
	JWApi::RenderAuth(JWApi::AUTH_HTTP);
}
$user_info = JWUser::GetUserInfo($idUser);

$result = array( 'verify' => array( 'authorized' => true, 'idUser' => $idUser, 'nameScreen' => $user_info['nameScreen'] ) );
$openid_id = JWOpenID::GetIdByUserId($idUser);
if ($openid_id) {
	$openid_db_row  = JWOpenID::GetDbRowById($openid_id);
	$openid_url     = JWOpenID::GetFullUrl($openid_db_row['urlOpenid']);
} else {
	if ($user_info['isUrlFixed']=='Y') {
		$openid_url = JW_SRVNAME . "/${user_info['nameUrl']}/";
	} else {
		$openid_url = false;
	}
}
if ($openid_url) $result['verify']['openid'] = $openid_url;

switch($type){
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
