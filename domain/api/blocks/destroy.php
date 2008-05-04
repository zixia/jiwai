<?php
require_once('../../../jiwai.inc.php');

$pathParam = isset($_REQUEST['pathParam']) ? $_REQUEST['pathParam'] : null;

$pathParam = trim( strtolower($pathParam), './' );
$id = $type = null;
list($id, $type) = explode( '.', $pathParam );
if( !in_array( $type, array('json','xml') )){
	JWApi::OutHeader(406, true);
}

$idUser = JWApi::GetAuthedUserId();
if( ! $idUser ){
	JWApi::RenderAuth(JWApi::AUTH_HTTP);
}

$user = JWUser::GetUserInfo( $id );
if ( empty($user) )
{
	JWApi::OutHeader(406, true);
}

if ( JWBlock::Destroy($idUser, $user['id']) )
{
	$result = array( 'user_id'=> $idUser, 'unblocked_user_id' => $user['id'] );
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
}else{
	JWApi::OutHeader(500, true);
}

function renderXmlReturn($message){
	$xmlString = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $message, 0, 'block' );
	echo $xmlString;
}

function renderJsonReturn($message){
	echo json_encode( $message );
}
?>
