<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWLogin::Logout();

$pathParam = isset($_REQUEST['pathParam']) ? $_REQUEST['pathParam'] : null;

$type = trim( $pathParam, '.' );
if( !in_array( $type, array('json','xml') )){
	JWApi::OutHeader(406, true);
}

$apikey = @$_REQUEST['apikey'];
$email = @$_POST['email'];
$name_screen = @$_POST['name_screen'];
$pass = @$_POST['pass'];

$validApiKeys = array(
    '0e4a4c24954f22cecea6b06b33efbfd7'  => 'Widsets',
);

if (null == $apikey
    || !array_key_exists($apikey, $validApiKeys)) {
    JWApi::OutHeader(401, true);
}

if (null == $email
        || null == $name_screen
        || null == $pass ) {
    JWApi::OutHeader(406, true);
}

$validate_item = array(
		array( 'Email', $email ),
		array( 'NameScreen', $name_screen ),
);

$validate_result = JWFormValidate::Validate($validate_item);

if ( is_array($validate_result) )
{
    JWApi::OutHeader(406, true);
}

$user['email'] = $email;
$user['nameFull'] = $user['nameScreen'] = $name_screen;
$user['pass'] = $pass;
$user['ip'] = JWRequest::GetIpRegister();
$user['srcRegister'] = $validApiKeys[$apikey];

if ( $user_id = JWUser::Create($user) ) {
    $result = array( 'register' => 'success', );
    JWLogin::Login( $user_id );
} else {
    JWApi::OutHeader(406, true);
}

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
