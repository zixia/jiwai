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


// thumb: thumb size: 48 / 24
if ( array_key_exists('thumb',$_REQUEST) )
	$thumb	= $_REQUEST['thumb'];

// encoding, default UTF-8
if ( array_key_exists('encoding',$_REQUEST) )
	$encoding	= $_REQUEST['encoding'];
else
	$encoding	= 'UTF-8';



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

$options	= array (
					'type'		=> JWFeed::RSS20
					, 'thumb'	=> @$thumb

					// compatible with twitter
					, 'count'	=> $count
					, 'since_id'=> @$since_id
					, 'since'	=> @$since
					, 'callback'=> @$callback
					, 'encoding'=> $encoding
				);

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

###############################################################
# functions here.
###############################################################

/*
 * 	output public timeline rss
 *	@param	array	options, include:
					count, since_id, since
 *
 */
function public_timeline_rss_n_atom($options)
{
	$count	= intval($options['count']);
	if ( 0>=$count )
		$count = JWStatus::DEFAULT_STATUS_NUM;

	//TODO: since_id / since
	
	
	//$statuses	= JWStatus::GetStatusListTimeline($count);
	$status_data	= JWStatus::GetStatusIdsFromPublic($count);
	$status_rows	= JWStatus::GetStatusDbRowsByIds($status_data['status_ids']);
	$user_rows		= JWUser::GetUserDbRowsByIds	($status_data['user_ids']);


	$feed = new JWFeed( array (	'title'		=> '叽歪广场'
							, 'url'		=> 'http://JiWai.de/public_timeline/'
							, 'desc'	=> '所有人叽歪de更新都在这里！'
						) );

	foreach ( $status_data['status_ids'] as $status_id )
	{
		$user_id = $status_rows[$status_id]['idUser'];
		$feed->AddItem(array( 
				'title'		=> $user_rows[$user_id]['nameFull'] . ' - ' . $status_rows[$status_id]['status']
				, 'desc'	=> $user_rows[$user_id]['nameFull'] . ' - ' . $status_rows[$status_id]['status']
				, 'date'	=> $status_rows[$status_id]['timestamp']
				, 'author'	=> $user_rows[$user_id]['nameFull']
				, 'guid'	=> "http://JiWai.de/" . $user_rows[$user_id]['nameScreen'] . "/statuses/" . $status_rows[$status_id]['idStatus']
				, 'url'		=> "http://JiWai.de/" . $user_rows[$user_id]['nameScreen'] . "/statuses/" . $status_rows[$status_id]['idStatus']
			) );
	}

	//Valid parameters are RSS0.91, RSS1.0, RSS2.0, PIE0.1 (deprecated),
	// MBOX, OPML, ATOM, ATOM1.0, ATOM0.3, HTML, JS

//die(var_dump($feed));
	$feed->OutputFeed($options['type']);
	exit(0);
}


/*
 * 	output public timeline  in xml format
 *	@param	array	options, include:
					count, since_id, since, callback
 *
 */
function public_timeline_xml($options)
{
	$statuses	= get_public_timeline_array($options);

	header('Content-Type: application/xml; charset=utf-8');

	$xml .= '<?xml version="1.0" encoding="UTF-8"?>';
	$xml .= "\n<statuses>\n";


	foreach ($statuses as $status)
	{
		$xml .= "\t<status>\n";
		$xml .= array_to_xml($status,2);
		$xml .= "\t</status>\n";
	}

	
	$xml .= "</statuses>\n";

	echo $xml;
}


/*
 * 	return public timeline as a array
 *	@param	array	options, include:
					count, since_id, since
 *
 */
function get_public_timeline_array($options)
{
	/* Twitter compatible */

	$count	= intval($options['count']);
	if ( 0>=$count )
		$count = JWStatus::DEFAULT_STATUS_NUM;

	//TODO: since_id / since

	/* Twitter compatible */
	
	if ( !empty($options['thumb']) && 48!=$options['thumb'] ) {
		$options['thumb'] = 24;
	}else{
		$options['thumb'] = 48;
	}

	//$statuses	= JWStatus::GetStatusListTimeline($count);

	$status_data	= JWStatus::GetStatusIdsFromPublic($count);
	$status_rows	= JWStatus::GetStatusDbRowsByIds($status_data['status_ids']);
	$user_rows		= JWUser::GetUserDbRowsByIds	($status_data['user_ids']);

	$statuses_array								= array();

	foreach ( $status_data['status_ids'] as $status_id )
	{
		$user_id	= intval($status_rows[$status_id]['idUser']);

		$status_array['created_at']			= date("r",$status_rows[$status_id]['timestamp']);
		$status_array['id']					= intval($status_id);
		$status_array['text']				= $status_rows[$status_id]['status'];

		$status_array['user']['id']			= $user_id;
		$status_array['user']['name']		= $user_rows[$user_id]['nameFull'];
		$status_array['user']['screen_name']= $user_rows[$user_id]['nameScreen'];
		$status_array['user']['location']	= $user_rows[$user_id]['location'];
		$status_array['user']['description']= $user_rows[$user_id]['bio'];

		$status_array['user']['profile_image_url']= JWPicture::GetUserIconUrl($user_id, "thumb$options[thumb]");
		$status_array['user']['url']		= $user_rows[$user_id]['url'];
		$status_array['user']['protected']	= $user_rows[$user_id]['protected']==='Y' ? true : false;

		if ( 'UTF-8'!=$options['encoding'] )
		{
			$status_array['text']				= mb_convert_encoding($status_rows[$status_id]['status']	
																			,$options['encoding'],'UTF-8');
			$status_array['user']['name']		= mb_convert_encoding($user_rows[$user_id]['nameFull']	
																			,$options['encoding'],'UTF-8');
			$status_array['user']['location']	= mb_convert_encoding($user_rows[$user_id]['location']	
																			,$options['encoding'],'UTF-8');
			$status_array['user']['description']= mb_convert_encoding($user_rows[$user_id]['bio']		
																			,$options['encoding'],'UTF-8');
		}


		array_push($statuses_array, $status_array);
	}

	return $statuses_array;
}


/*
 *	convert a key=>val array to xml. 
 *	can recursion key=>val array, but can't process a array('a','b','c').
 *	@param	array	需要处理的array
 *	@param	level	使用 "\t" 缩进的个数
 *	@return	xml		处理过的 xml 片段
 */
function array_to_xml($array, $level=1) {
	$xml = '';

    foreach ($array as $key=>$value) {
        $key = strtolower($key);
		
        if (is_array($value)) { // 大于一层的 assoc array
			$xml .= str_repeat("\t",$level)
					."<$key>\n"
					. array_to_xml($value, $level+1)
					. str_repeat("\t",$level)."</$key>\n";
        } else { // 一层的 assoc array
//			if (trim($value)!='') 
//			{
				if (htmlspecialchars($value)!=$value) {
					$xml .= str_repeat("\t",$level)
						."<$key><![CDATA[$value]]></$key>\n";
				} else {
					$xml .= str_repeat("\t",$level).
						"<$key>$value</$key>\n";
				}
//			}
        }
    }
    return $xml;
}

?>
