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
		redirect_to_404();
	}

	$tag_row = JWDB_Cache_Tag::GetDbRowById( $tag_id );
}
else
{
	$func = 'public';
}

if( null == $func )
{
	$func = 'channel';
}

switch ( $func )
{
	case 'channel':
		require_once(dirname(__FILE__) . "/channel.inc.php");
		break;
	default:
		require_once(dirname(__FILE__) . "/public.inc.php");
		break;

}
exit(0);
?>
