<?php
require_once('../../../jiwai.inc.php');
$pathParam = null;
extract($_REQUEST, EXTR_IF_EXISTS);

/**
  * User Authentication
  */
$current_user_id = JWApi::getAuthedUserId();
if( null==$current_user_id )
{
	JWApi::RenderAuth( JWApi::AUTH_HTTP );
}
$follow_user_id = $type = null;
@list($follow_user_id, $type)= preg_split('/[\.\/]+/', $pathParam, 2, PREG_SPLIT_NO_EMPTY ); 
if( false==in_array( $type, array('json','xml') ))
{
	JWApi::OutHeader(406, true);
}

$follow_user = JWUser::GetUserInfo( $follow_user_id );
if( empty($follow_user) ) 
{
	JWApi::OutHeader(400, true);
}

if ( false==JWSns::CreateFollower( $follow_user['id'], $current_user_id, 'Y' ) )
{
	JWApi::OutHeader(406, true);
}

switch($type){
	case 'xml':
		renderXmlReturn($follow_user);
		break;
	case 'json':
		renderJsonReturn($follow_user);
		break;
	default:
		JWApi::OutHeader(400, true);
}	

function renderXmlReturn($user)
{
	$user_info = JWApi::ReBuildUser( $user );
	$xml_string = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $user_info, 0 , 'user');
	echo $xmlString;

}

function renderJsonReturn($user){
	$user_info = JWApi::ReBuildUser( $user );
	echo json_encode( $user_info );
}
?>
