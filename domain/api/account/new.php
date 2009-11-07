<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');

$caller = (int) JWApi::GetAuthedUserId();
$consumer = JWApi::$OAuthConsumer;
if ($consumer && $caller!=$consumer->idUser) {
//    JWApi::OutHeader(401, true);
}

if (!$caller && JWRateLimit::Protect('account_new', JWRequest::GetClientIp(), 5, 60))
    JWApi::OutHeader(403, true);
JWLogin::Logout();

$pathParam = isset($_REQUEST['pathParam']) ? $_REQUEST['pathParam'] : null;

$type = trim( $pathParam, '.' );
if( !in_array( $type, array('json','xml') )){
	JWApi::OutHeader(406, true);
}

if (isset($_POST['apikey'])
    && $_POST['apikey'] == '0e4a4c24954f22cecea6b06b33efbfd7') { // work-around for widsets
    $source = 'widsets';
} elseif (!$consumer && isset($_REQUEST['apikey'])) {
	$ds = new JWOAuth_DataStore();
	$consumer = $ds->lookup_consumer($_REQUEST['apikey']);
	$source = $consumer ? $consumer->title : null;
} else {
	$source = $consumer->title;
}

if (!$source) {
    JWApi::OutHeader(401, true);
}

$email = @$_REQUEST['email'];
$name_screen = @$_POST['name_screen'];
$pass = @$_POST['pass'];
if (isset($_REQUEST['screen_name'])) $name_screen = $_REQUEST['screen_name'];
if ($caller) {
	if (!$name_screen) $name_screen = $source.'_'.JWDevice::GenSecret(8);
	$pass = md5(time().$name_screen);
}
$name_screen = JWUser::GetPossibleName($name_screen, $email);

$autoFollowers = array(
    //2,  // JiWai
);

if (null == $email
        || null == $name_screen
        || null == $pass ) {
    JWApi::OutHeader(406, true);
}

$validate_item = array(
		array( 'Email', $email ),
		array( 'NameScreen', $name_screen ),
);

if (!$caller) {
	$validate_result = JWFormValidate::Validate($validate_item);

	if ( is_array($validate_result) )
	{
		JWApi::OutHeader(406, true);
	}
}

$user['email'] = $email;
$user['nameFull'] = $user['nameScreen'] = $name_screen;
$user['pass'] = $pass;
$user['ip'] = JWRequest::GetIpRegister();
$user['srcRegister'] = $source;

if ( $user_id = JWSns::CreateUser($user) ) {
    foreach ($autoFollowers as $idFollower) {
        JWSns::CreateFollower($user_id, $idFollower);
        JWSns::CreateFollower($idFollower, $user_id);
    }
    $result = array( 'register' => 'success', );
} else {
    JWApi::OutHeader(406, true);
}
if ($caller) {
	$token = new JWOAuth_RequestToken('', '', '');
	$token->idUser = $user_id;
	$token = JWApi::$OAuthServer->get_data_store()->new_access_token($token, $consumer);
	$result = array('account'=>array(
		'id' => $user_id,
		'screen_name' => $name_screen,
		'access_token' => array(
			'key' => $token->key,
			'secret' => $token->secret
			)
		));
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
