<?php
require_once( dirname(__FILE__) . '/../../jiwai.inc.php');

$tag_name = isset($_REQUEST['tag']) ? $_REQUEST['tag'] : '笑话';
$pathParam = strval(@$_REQUEST['pathParam']) | '/';
$current_user_id = JWLogin::GetCurrentUserId();

@list ($dummy,$func,$param) = split('/', $pathParam, 3);

if( $tag_name ) {
	if ( false ==JWUnicode::unifyName( $tag_name ) ) {
		JWTemplate::RedirectToUrl( '/t/' . urlEncode($tag_name) . $pathParam );
	}
	$tag_id = JWTag::GetIdByNameOrCreate( $tag_name );
	if( null == $tag_id ) {
		JWTemplate::RedirectTo404NotFound();
	}
	$tag_row = JWDB_Cache_Tag::GetDbRowById( $tag_id );
} else {
	JWTemplate::RedirectBackToLastUrl('/');
}

$func = $func | 'channel';

switch ( $func ) {
	default:
	case 'channel':
		$tagid_php = dirname(__FILE__) . "/{$tag_row['id']}.inc.php";
		if (file_exists($tagid_php) ) {
			require_once( $tagid_php );
		} else {
			require_once( dirname(__FILE__) . '/tagid.inc.php' );
		}
		JWVisitTag::Record($tag_id, JWRequest::GetRemoteIP());
		break;
}
?>
