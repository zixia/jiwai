<?php
require_once('../../jiwai.inc.php');
//var_dump($_REQUEST);

$nameScreen	= @$_REQUEST['nameScreen'];
$pathParam 	= @$_REQUEST['pathParam'];

//die(var_dump($_GET));
@list ($dummy,$func,$param) = split('/', $pathParam, 3);

if ( 'dajia'===strtolower($nameScreen) )
{
	require_once(dirname(__FILE__) . '/dajia.inc.php');
	exit(0);
}

$idUserPage = JWUser::GetUserInfoByName($nameScreen,'id');

if ( null===$idUserPage )
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
		$param = preg_replace('/\.[^.]*$/','',$param);

		user_picture( $idUserPage, $param);

		exit(0);

	default:
		require_once(dirname(__FILE__) . "/wo.inc.php");
		exit(0);
		break;
}

?>

