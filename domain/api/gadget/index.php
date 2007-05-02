<?php
require_once("../../jiwai.inc.php");

//echo '<pre>'; die(var_dump($_REQUEST));

header ("Content-Type: text/javascript");

/*
<script type="text/javascript" 
		src="http://gadget.jiwai.de/zixia/gadget
			?status={network|self}
			&count=4
			&theme=iChat" >
</script>
*/

if ( array_key_exists('idUser',$_REQUEST) )
	$idUser	= $_REQUEST['idUser'];
else
	die();//FIXME $idUser = 'public_timeline';

if ( array_key_exists('nameFunc',$_REQUEST) )
	$nameFunc	= $_REQUEST['nameFunc'];
else
	$nameFunc	= 'gadget';

switch ($nameFunc)
{
	case 'gadget':
		$statusType	= @$_REQUEST['status'];
		$countMax	= @$_REQUEST['count'];
		$themeName	= @$_REQUEST['theme'];
		$thumbSize	= @$_REQUEST['thumb'];

		require_once('gadget.inc.php');
		gadget($idUser, $statusType, $themeName, $countMax);

		break;

	default:
		die ( "UNSUPPORT!" );
		break;
}

?>
