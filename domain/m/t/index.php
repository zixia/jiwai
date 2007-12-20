<?php
require_once( '../config.inc.php' );

$tag_name	= @$_REQUEST['tag'];
$pathParam 	= @$_REQUEST['pathParam'];

// $pathParam is like: "/statuses/123"
@list ($dummy,$func,$param) = split('/', $pathParam, 3);

if( $tag_name ) 
{
	if ( false ==JWUnicode::unifyName( $tag_name ) )
	{ 
		JWTemplate::RedirectToUrl( '/t/' . urlEncode($tag_name) .'/' );
	}

	$tag_id = JWTag::GetIdByNameOrCreate( $tag_name );

	if( null == $tag_id ) 
	{
		JWTemplate::RedirectTo404NotFound();
	}

	$tag_row = JWTag::GetDbRowById( $tag_id );
}
else
{
	JWTemplate::RedirectBackToLastUrl('/');
}

if( null == $func )
{
	$func = 'channel';
}

switch ( $func )
{
	case 'channel':
	default:
		require_once(dirname(__FILE__) . "/channel.inc.php");
		break;

}
exit(0);
?>
