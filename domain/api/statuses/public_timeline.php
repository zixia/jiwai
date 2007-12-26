<?php
require_once("../../../jiwai.inc.php");
$callback = null;
$count = 20;
$since_id = null;
$since = null;
$thumb = 48;
$pathParam = null;
extract( $_REQUEST, EXTR_IF_EXISTS );
$since = ($since) ? $since :  ( isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : null );

$type = trim( strtolower($pathParam), '.' );
if( !in_array($type, array('xml','json','atom','rss'))){
	JWApi::OutHeader(406, true);
}

$options = array(
		'count' => intval($count),
		'since_id' => intval($since_id),
		'since' => $since,
		'thumb' => $thumb,
		'callback' => $callback,
		);

switch($type){
	case 'xml':
		renderXmlReturn($options);
	break;
	case 'json':
		renderJsonReturn($options);
	break;
	case 'atom':
		renderFeedReturn($options, JWFeed::ATOM);
	break;
	case 'rss':
		renderFeedReturn($options, JWFeed::RSS20);
	break;
	default:
		JWApi::OutHeader(406, true);
}

function renderXmlReturn($options){

	$statuses = getPublicTimelineStatuses( $options, true );
	
	$xmlString = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= '<?xml version="1.0" encoding="UTF-8"?>';
	$xmlString .= JWApi::ArrayToXml($statuses, 0, 'statuses');

	echo $xmlString;
}

function renderJsonReturn($options){
	$statuses = getPublicTimelineStatuses( $options, true );
	if( $options['callback'] ){
		echo $options['callback'].'('. json_encode($statuses) .')';
	}else{
		echo json_encode($statuses);
	}
}

function renderFeedReturn($options, $feedType=JWFeed::ATOM){

	$statuses = getPublicTimelineStatuses( $options, false );

	$feed = new JWFeed(array(
				'title'	=> '叽歪广场' ,
			       	'url'	=> 'http://JiWai.de/public_timeline/' , 
				'desc'	=> '所有人叽歪de更新都在这里！' , 
				'ttl'	=> 40,
				)); 

	foreach ( $statuses as $status ){
		$feed->AddItem(array( 
				'title'	=> $status['user']['nameScreen'] . ' - ' . JWApi::RemoveInvalidChar($status['status']) , 
				'desc'	=> $status['user']['nameScreen'] . ' - ' . JWApi::RemoveInvalidChar($status['status']) , 
				'date'	=> strtotime( $status['timeCreate'] ) , 
				'author'=> $status['user']['nameScreen'] , 
				'guid'	=> "http://JiWai.de/" . $status['user']['nameUrl'] . "/statuses/" . $status['idStatus'] , 
				'url'	=> "http://JiWai.de/" . $status['user']['nameUrl'] . "/statuses/" . $status['idStatus'],
				));
	}
	$feed->OutputFeed($feedType);
	exit(0);
}

/*
 * 	return public timeline as a array
 *	@param	array	options, include: count, since_id, since
 *
 */
function getPublicTimelineStatuses($options, $needReBuild=false){
	/* Twitter compatible */

	$count	= intval($options['count']);
	if ( 0>=$count )
		$count = JWStatus::DEFAULT_STATUS_NUM;

	//TODO: since_id / since
	$timeSince = ($options['since']==null) ? null : date("Y-m-d H:i:s", strtotime($options['since']) );

	$status_data    = JWStatus::GetStatusIdsFromPublic($count, 0, $options['since_id'], $timeSince);
	$status_rows	= JWStatus::GetDbRowsByIds($status_data['status_ids']);
	$user_rows	= JWDB_Cache_User::GetDbRowsByIds($status_data['user_ids']);

	$statuses = array();

	foreach ( $status_data['status_ids'] as $status_id ){
		$user_id = intval($status_rows[$status_id]['idUser']);
		
		$statusInfo = ($needReBuild) ?
		       	JWApi::ReBuildStatus( $status_rows[$status_id] ) : $status_rows[$status_id];
		$userInfo   = ($needReBuild) ?
			JWApi::ReBuildUser($user_rows[$user_id]) : $user_rows[$user_id];
		$statusInfo['user'] = $userInfo;
		$statuses[] = $statusInfo;
	}

	return $statuses;
}
?>
