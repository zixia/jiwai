<?php
require_once('../../jiwai.inc.php');
//die(var_dump($_REQUEST));

$tag_name	= @$_REQUEST['tag'];
$pathParam 	= @$_REQUEST['pathParam'];

// $pathParam is like: "/statuses/123"
@list ($dummy,$func,$param) = split('/', $pathParam, 3);

if( $tag_name ) 
{
	if ( false ==JWUnicode::unifyName( $tag_name ) )
	{ 
		JWTemplate::RedirectToUrl( '/t/' . urlEncode($tag_name) . $pathParam );
	}

	$tag_id = JWTag::GetIdByNameOrCreate( $tag_name );

	if( null == $tag_id ) 
	{
		JWTemplate::RedirectTo404NotFound();
	}

	$tag_row = JWDB_Cache_Tag::GetDbRowById( $tag_id );
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
	case 'thread':
		require_once(dirname(__FILE__) . "/channelthread.inc.php");

		if ( false == preg_match( '/^(\d+)\/?(\d*)$/', $param, $matches ) )
			JWTemplate::RedirectTo404NotFound();

		$status_id = intval($matches[1]);
		$reply_status_id = intval(@$matches[2]);

		$status_row = JWDB_Cache_Status::GetDbRowById( $status_id );
		if (empty($status_row) )
		{
			JWTemplate::RedirectTo404NotFound();
		}
		$page_user_id = $status_row['idUser'];

		if ( false==JWLogin::IsLogined())
			$_SESSION['login_redirect_url'] = $_SERVER['HTTP_REFERER'];

		user_status($page_user_id, $status_id, $reply_status_id, $tag_id);

		break;

	case 'channel':
		if ( 9259 != $tag_row['id'] )
			require_once(dirname(__FILE__) . "/channel.inc.php");
		else
			require_once(dirname(__FILE__) . "/channel.inc.dongzai.php");
		JWVisitTag::Record($tag_id, JWRequest::GetRemoteIP());
		break;

}
?>
