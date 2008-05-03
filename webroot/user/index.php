<?php
require_once('../../jiwai.inc.php');
//die(var_dump($_REQUEST));

$nameOrId	= @$_REQUEST['nameOrId'];
$pathParam 	= @$_REQUEST['pathParam'];

//die(var_dump($_GET));

// $pathParam is like: "/statuses/123"
@list ($dummy,$func,$param) = split('/', $pathParam, 3);
/**
 * url location order ( nameUrl, nameScreen, id )
 */
if ( true )
{
	$nameScreen = $nameOrId;
	if (!JWUnicode::unifyName($nameScreen)) { 
		//301 to UTF-8 URL if GBK
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: http://'.$_SERVER['HTTP_HOST'].'/'.urlencode($nameScreen).$pathParam);
		die();
	}

	$nameScreen = $nameOrId; //XXX go on even if name is invalid
	$nameScreen .= preg_match( '/\d+\.\d+\.\d+\.$/', $nameScreen) ? '*' : '';
	$page_user_info = JWUser::GetUserInfo($nameScreen,null,'nameUrl');

	if( empty($page_user_info) )
	{
		$page_user_info = JWUser::GetUserInfo( $nameScreen, null, 'nameScreen' );
		if( false == empty( $page_user_info ) )
		{
				$name_url = urlEncode($page_user_info['nameUrl']);
				JWTemplate::RedirectToUrl("/$name_url$pathParam");
		}
		else
		{
			/**
			 * maybe we really need get user info from idUser? (such as require of map.swf)
			 * add by seek@jiwai.com 2008-03-17 
			 */
			if ( preg_match('/^\d+$/', $nameOrId ) 
				&& $page_user_info = JWUser::GetUserInfo($nameOrId) )
			{
				$name_url = urlEncode($page_user_info['nameUrl']);
				JWTemplate::RedirectToUrl("/$name_url$pathParam");
			}
		}
	}

	if ( 't'==$func)
	{
		if (  preg_match('/^([^\/]+)\/?$/',$param,$matches)  )
			$func = 'channelpublic';
		else{
			JWTemplate::RedirectToUserPage( $page_user_info['nameUrl'] );
		}
	}

	$page_user_id = empty( $page_user_info ) ? null : $page_user_info['id'];
}


if ( 'public_timeline'===strtolower($nameScreen) )
{
	require_once(dirname(__FILE__) . '/public_timeline.inc.php');
	exit(0);
}


// userName/user_id not exist 
if ( empty($page_user_id) || empty($nameScreen) )
{
	JWTemplate::RedirectTo404NotFound();
	exit(0);
}


switch ( $func )
{
	case 'picture':
		require_once(dirname(__FILE__) . "/picture.inc.php");

		// get rid of file ext and dot: we know what type it is.
		//$param = preg_replace('/\.[^.]*$/','',$param);
		list($pict_size) = split('[\.\/]',$param);

		// TODO: http://jiwai.de/zixia/picture/thumb48/para.jpg
		user_picture($page_user_id, $pict_size);
		break;

	case 'thread':
		require_once(dirname(__FILE__) . "/thread.inc.php");

		if ( preg_match('/^(\d+)\/?(\d*)$/',$param,$matches) )
		{
			$status_id = intval($matches[1]);
			$reply_status_id = intval(@$matches[2]);
			user_status($page_user_id, $status_id, $reply_status_id);
			JWVisitThread::Record($status_id, JWRequest::GetRemoteIP());
		}
		else
		{
			JWTemplate::RedirectTo404NotFound();
			exit(0);
		}
		break;
	case 'channelpublic':
		{
			$tag_name = $matches[1];
			if ( false == JWUnicode::unifyName( $tag_name ) )
			{
				JWTemplate::RedirectToUrl( '/'.urlEncode( $nameScreen ).'/t/'.urlEncode($tag_name).'/' );
			}
			
			$tag_row = JWDB_Cache_Tag::GetDbRowByName( $tag_name );
			if ( false == empty($tag_row) )
				require_once(dirname(__FILE__) . "/channelpublic.inc.php");
			else
				header( 'Location: /'. urlencode( $nameScreen ) . '/' );
		}
		break;

	case 'statuses':
		require_once(dirname(__FILE__) . "/statuses.inc.php");

		if ( preg_match('/^(\d+)$/',$param,$matches) )
		{
			$status_id = intval($matches[1]);
			user_status($page_user_id, $status_id);
		}
		else
		{
			JWTemplate::RedirectTo404NotFound();
			exit(0);
		}
		break;
	case 'followers':
		// 用户好友列表
		require_once(dirname(__FILE__) . "/followers.inc.php");
		user_friends($page_user_id);	
		break;
	case 'followings':
		// 用户好友列表
		require_once(dirname(__FILE__) . "/followings.inc.php");
		user_friends($page_user_id);	
		break;
	case 'friends':
		// 用户好友列表
		require_once(dirname(__FILE__) . "/friends.inc.php");
		user_friends($page_user_id);	
		break;

	case 'favourites':
		require_once(dirname(__FILE__) . "/favourites.inc.php");
		user_favourites($page_user_id);	
		break;

	case 'mms_friends':
	case 'mms':
		$active = 'mms'; $mmsId = 0;
		@list( $active, $mmsId ) = explode( '/', trim( $_REQUEST['pathParam'], '/' ) );
		$g_page_user_id			= $page_user_id;

		if( $active=='mms' && $mmsId=intval($mmsId) ) {
			require_once(dirname(__FILE__) . "/mms_view.inc.php");
		}else{
			require_once(dirname(__FILE__) . "/mms.inc.php");
		}
		break;

	case 'with_friends':
		$g_user_with_friends 	= true;
		// fall to default
	default:
		if ( 'help'===strtolower($nameScreen) )
		{
			require_once(dirname(__FILE__) . '/help.inc.php');
			exit(0);
		}


		$g_user_default 		= true;
		$g_page_user_id			= $page_user_id;
		require_once(dirname(__FILE__) . "/wo.inc.php");
		break;
}
exit(0);

?>
