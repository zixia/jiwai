<?php
require_once('../../jiwai.inc.php');
//var_dump($_REQUEST);

$nameOrId	= @$_REQUEST['nameOrId'];
$pathParam 	= @$_REQUEST['pathParam'];


//die(var_dump($_GET));

// $pathParam is like: "/statuses/123"
@list ($dummy,$func,$param) = split('/', $pathParam, 3);

if ( preg_match('/^\d+$/',$nameOrId) )
{
	$page_user_id = $nameOrId;
	$nameScreen	= JWUser::GetUserInfo($page_user_id,'nameScreen');
}
else
{
	$nameScreen = $nameOrId;

	// FIXME: 可能是 GBK 编码的"联通"，不过没关系，这种名字就只能用 utf8 访问好了
	if ( !IsValidUtf8($nameScreen) )
		$nameScreen = iconv('GBK','UTF-8', $nameScreen);

	$page_user_id	= JWUser::GetUserInfo($nameScreen,'id');
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
	case 'friends':
		// 用户好友列表
		require_once(dirname(__FILE__) . "/friends.inc.php");
		user_friends($page_user_id);	
		break;

	case 'favourites':
		require_once(dirname(__FILE__) . "/favourites.inc.php");
		user_favourites($page_user_id);	
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

function IsValidUtf8($string)
{
	$str_len = strlen($string);
	for($i=0;$i<$str_len;)
	{
		$str = ord($string[$i]);
		if($str>=0 && $str < 0x7f)
		{
			$i++;
			continue;
		}
		if($str< 0xc0 || $str>0xfd) return false;
		$count = $str>0xfc?5:$str>0xf8?4:$str>0xf0?3:$str>0xe0?2:1;
		if($i+$count > $str_len) return false;
		$i++;
		for($m=0;$m<$count;$m++)
		{
			if(ord($string[$i])<0x80 || ord($string[$i])>0xbf) return false;
			$i++;
		}
	}
	return true;
}
?>

