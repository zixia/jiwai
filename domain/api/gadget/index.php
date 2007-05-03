<?php
die ( 'NO USE' );
require_once("../../../jiwai.inc.php");

echo '<pre>'; die(var_dump($_REQUEST));

/*
<script type="text/javascript" 
		src="http://api.jiwai.de/gadget/statuses/1.js
			?status={friends|self}
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
	$nameFunc	= 'statuses';


// rewrite param, may incluce the file ext name and user id/name
$pathParam	= $_REQUEST['pathParam'];


switch ($pathParam[0])
{
	case '.':
		// http://api.jiwai.de/statuses/public_timeline.rss
		if ( preg_match('/^\.(\w+)$/',$pathParam,$matches) )
			$output_type = strtolower($matches[1]);

		switch ($output_type)
		{
			case 'atom':
				$options['type']	= JWFeed::ATOM;
				public_timeline_rss_n_atom($options);
				break;
			case 'rss':
				$options['type']	= JWFeed::RSS20;
				public_timeline_rss_n_atom($options);
				break;
			case 'json':
				$statuses	= get_public_timeline_array($options);

				if ( empty($options['callback']) )
					echo json_encode($statuses);
				else
					echo $options['callback'] . '(' . json_encode($statuses) . ')';

				break;
			case 'xml':
				public_timeline_xml($options);
				break;
			default: 
				break;
		}
		break;
	case '/':
		break;
	default:
		break;
}

exit(0);


header ("Content-Type: text/javascript");

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
