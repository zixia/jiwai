<?php
require_once('../../jiwai.inc.php');
//die(var_dump($_REQUEST));

$tag_name	= @$_REQUEST['tag'];
$pathParam 	= @$_REQUEST['pathParam'];

// $pathParam is like: "/statuses/123"
@list ($dummy,$func,$param) = split('/', $pathParam, 3);

if( $tag_name ) 
{
	$tag_row = JWTag::GetDbRowByName( $tag_name );
	if( false == empty( $tag_row ) ) {
		$tag_id = $tag_row['id'];
	}else{
		$tag_id = JWTag::Create( $tag_name );
		if( ! $tag_id ) 
		{
			JWTemplate::RedirectTo404NotFound();
		}
		$tag_row = JWTag::GetDbRowById( $tag_id );
	}
}else
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
		$page_user_id = $status_row['idUser'];

		if ( false==JWLogin::IsLogined())
			$_SESSION['login_redirect_url'] = $_SERVER['HTTP_REFERER'];

		user_status($page_user_id, $status_id, $reply_status_id, $tag_id);

		break;

	case 'channel':
		require_once(dirname(__FILE__) . "/channel.inc.php");
		break;

}
exit(0);
?>
