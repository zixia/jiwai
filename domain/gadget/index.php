<?php
require_once("../../jiwai.inc.php");

header ("Content-Type: text/javascript");

/*
<script type="text/javascript" 
		src="http://gadget.jiwai.de/zixia/gadget
			?status={network|self}
			&num=4
			&theme=iChat" >
</script>
*/

if ( array_key_exists('nameScreen',$_REQUEST) )
	$nameScreen	= $_REQUEST['nameScreen'];
else
	$nameScreen = 'public_timeline';

if ( array_key_exists('nameFunc',$_REQUEST) )
	$nameFunc	= $_REQUEST['nameFunc'];
else
	$nameFunc	= 'gadget';


switch ($nameFunc)
{
	case 'gadget':
		$statusType	= @$_REQUEST['status'];
		$numMax		= @$_REQUEST['num'];
		$themeName	= @$_REQUEST['theme'];
		$thumbSize	= @$_REQUEST['thumb'];

		require_once('gadget.inc.php');
		gadget($nameScreen, $statusType, $themeName, $numMax);

		break;

	default:
		die ( "UNSUPPORT!" );
		break;
}

?>
