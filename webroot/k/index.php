<?php
require_once( dirname(__FILE__) . '/../../jiwai.inc.php');

$key = $_GET['q'] = trim(@$_REQUEST['k']);
$pathParam = strval(@$_REQUEST['pathParam']) | '/';
$current_user_id = JWLogin::GetCurrentUserId();

@list ($dummy,$func,$param) = split('/', $pathParam, 3);

if( $key ) {
	if ( false == JWUnicode::unifyName( $key ) ) {
		JWTemplate::RedirectToUrl( '/k/' . urlEncode($key) . $pathParam );
	} else if ( preg_match('/[\s\+\-\(\)]+/', $key) ) {
		JWTemplate::RedirectToUrl( '/wo/search/statuses?q=' . urlEncode($key));
	}
} else {
	$func = 'public';
}

$func = $func ? $func : 'channel';

switch ( $func ) {
	case 'public':
		require_once( dirname(__FILE__) . '/public.inc.php' );
		break;
	default:
	case 'channel':
		require_once( dirname(__FILE__) . '/key.inc.php' );
		break;
}
?>
