<?php
require_once("../../../jiwai.inc.php");

//die(var_dump($_SERVER));
// json callback
if ( array_key_exists('callback',$_REQUEST) )
	$callback	= $_REQUEST['callback'];

// return num
if ( array_key_exists('count',$_REQUEST) )
	$count		= $_REQUEST['count'];
else
	$count		= 20;

// since_id: only return the status with id >= since_id;
if ( array_key_exists('since_id',$_REQUEST) )
	$since_id	= $_REQUEST['since_id'];

// since: HTTP-formatted date, only return the status with newer then since.
if ( array_key_exists('since',$_REQUEST) )
	$since		= $_REQUEST['since'];
else if ( array_key_exists('HTTP_IF_MODIFIED_SINCE',$_SERVER) )
	$since		= $_SERVER['HTTP_IF_MODIFIED_SINCE'];


// rewrite param, may incluce the file ext name and user id/name
$pathParam	= $_REQUEST['pathParam'];


##################### params done ####################################
# count 			default 20
# since_id  		Optional.  Returns only public statuses with an ID greater than 
#					(that is, more recent than) the specified ID.  Ex:
# since 			HTTP-formatted date
# 					If-Modified-Since HTTP_HEADER HTTP-formatted date
# id 				nameScreen or #id
# callback 			JSON only, callback function name.
#
# pathParam			string, part of url request.
#################################################################


switch ($pathParam[0])
{
	case '.':
		// http://api.jiwai.de/statuses/public_timeline.rss
		if ( preg_match('/^\.(\w+)$/',$pathParam,$matches) )
			$output_type = strtolower($matches[1]);

		switch ($output_type)
		{
			case 'rss':
				public_timeline_rss(array(
						'count'		=> $count
						, 'since_id'=> @$since_id
						, 'since'	=> @$since
					) );
				break;
			case 'atom':
				break;
			case 'json':
				break;
			case 'xml':
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

###############################################################
# functions here.
###############################################################

/*
 * 	output public timeline rss
 *	@param	array	options, include:
					count, since_id, since
 *
 */
function public_timeline_rss($options)
{
	$count	= intval($options['count']);
	if ( 0>=$count )
		$count = JWStatus::DEFAULT_STATUS_NUM;

	//TODO: since_id / since
	
	$statuses	= JWStatus::GetStatusListTimeline($count);


	$feed = new JWFeed( array (	'title'		=> '叽歪广场'
							, 'url'		=> 'http://beta.jiwai.de/public_timeline/'
							, 'desc'	=> '所有人叽歪de更新都在这里！'
						) );

	foreach ( $statuses as $status )
	{
		$user	= JWUser::GetUserInfoById($status['idUser']);

		$feed->AddItem(array( 
				'title'		=> "$user[nameFull] - $status[status]"
				, 'url'		=> "http://beta.jiwai.de/$user[nameScreen]/"
				, 'desc'	=> "$user[nameFull] - $status[status]"
				, 'date'	=> $status['timestamp']
				, 'author'	=> $user['nameFull']
				, 'guid'	=> "http://beta.jiwai.de/$user[nameScreen]/statuses/$status[idStatus]"
				, 'url'		=> "http://beta.jiwai.de/$user[nameScreen]/statuses/$status[idStatus]"
			) );
	}

	//Valid parameters are RSS0.91, RSS1.0, RSS2.0, PIE0.1 (deprecated),
	// MBOX, OPML, ATOM, ATOM1.0, ATOM0.3, HTML, JS

	$feed->OutputFeed(JWFeed::RSS20);
	exit(0);
}

?>
