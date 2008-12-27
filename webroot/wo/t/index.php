<?php
require_once( '../../../jiwai.inc.php');
JWLogin::MustLogined(false);
$page_user_id = JWLogin::GetCurrentUserId();
$tag_name 	= @$_REQUEST['pathParam'];
if(empty($tag_name))
	$tag_name = "笑话";
if ( false == JWUnicode::unifyName( $tag_name ) )
	JWTemplate::RedirectToUrl( '/wo/t/'.urlEncode($tag_name).'/' );

	$tag_row = JWDB_Cache_Tag::GetDbRowByName( $tag_name );
if ( false == empty($tag_row) )
	require_once(dirname(__FILE__) . "/../../user/channelpublic.inc.php");
	else
	header( 'Location: /wo/t/' );
?>
