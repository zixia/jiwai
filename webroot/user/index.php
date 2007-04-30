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
	$idUserPage = $nameOrId;
	$nameScreen	= JWUser::GetUserInfoById($idUserPage,'nameScreen');
}
else
{
	$nameScreen = $nameOrId;
	$idUserPage	= JWUser::GetUserInfoByName($nameScreen,'id');
}


if ( 'public_timeline'===strtolower($nameScreen) )
{
	require_once(dirname(__FILE__) . '/public_timeline.inc.php');
	exit(0);
}


// userName/user_id not exist 
if ( null===$idUserPage || null===$nameScreen )
{
	$_SESSION['404URL'] = $_SERVER['SCRIPT_URI'];
	header ( "Location: " . JWTemplate::GetConst("UrlError404") );
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
		user_picture($idUserPage, $pict_size);
		break;

	case 'statuses':
		require_once(dirname(__FILE__) . "/statuses.inc.php");

		if ( preg_match('/^(\d+)$/',$param,$matches) )
		{
			$idStatus = intval($matches[1]);
			user_status($idUserPage, $idStatus);
		}
		else
		{
			$_SESSION['404URL'] = $_SERVER['SCRIPT_URI'];
			header ( "Location: " . JWTemplate::GetConst("UrlError404") );
		}
		break;
		
	default:
		require_once(dirname(__FILE__) . "/wo.inc.php");
		break;
}
exit(0);

?>

