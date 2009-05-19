<?php
require_once("../../../jiwai.inc.php");
$pathParam = null;
extract( $_REQUEST, EXTR_IF_EXISTS );

$type = $thread_id = null;
@list($thread_id,$type) = explode('.', trim( strtolower($pathParam), '/') );

if( false==in_array($type, array('xml','json','atom','rss'))){
	JWApi::OutHeader(406, true);
}

$thread_id = intval($thread_id);
if ( ! $thread_id ){
	JWApi::OutHeader(406, true);
}

$status = JWDB_Cache_Status::GetDbRowById( $thread_id );
if ( empty( $status ) ){
	JWApi::OutHeader(403, true); //may 404, but 404 has beed dealed
}

$count_reply = JWDB_Cache_Status::GetCountReply( $thread_id );
$reply_data = JWDB_Cache_Status::GetStatusIdsByIdThread($thread_id, $count_reply);

$current_user_id = JWApi::GetAuthedUserId();
$reply_data['status_ids'] = array_unique(array_merge(array($thread_id), $reply_data['status_ids']));
$reply_data['user_ids'] = array_unique(array_merge(array($status['idUser']), $reply_data['user_ids']));

$statuses = JWDB_Cache_Status::GetDbRowsByIds( $reply_data['status_ids'] );
$users = JWDB_Cache_User::GetDbRowsByIds( $reply_data['user_ids'] );

switch($type){
	case 'xml':
		renderXmlReturn($statuses, $users);
	break;
	case 'json':
		renderJsonReturn($statuses, $users);
	break;
	case 'rss':
	case 'atom':
		renderFeedReturn($statuses, $users, $type);
	break;
	default:
		JWApi::OutHeader(406, true);
}

function renderXmlReturn($status_rows,$user_rows){
    ob_start();
    ob_start("ob_gzhandler");

	$statuses = getMergedStatuses($status_rows, $user_rows, true);
	$xmlString = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= '<?xml version="1.0" encoding="UTF-8"?>';
	$xmlString .= JWApi::ArrayToXml($statuses, 0, 'statuses');

	echo $xmlString;
    ob_end_flush();
    header('Content-Length: '.ob_get_length());
    ob_end_flush();
}

function renderJsonReturn($status_rows, $user_rows){
    ob_start();
    ob_start("ob_gzhandler");
	$statuses = getMergedStatuses($status_rows, $user_rows, true);
	if( $options['callback'] ){
		echo $options['callback'].'('. json_encode($statuses) .')';
	}else{
		echo json_encode($statuses);
	}
    ob_end_flush();
    header('Content-Length: '.ob_get_length());
    ob_end_flush();
}

function renderFeedReturn($status_rows, $user_rows, $feedType=JWFeed::ATOM){
    ob_start();
    ob_start("ob_gzhandler");

	global $status, $thread_id;
	$statuses = getMergedStatuses($status_rows, $user_rows, false);

	$feed = new JWFeed(array(
				'title'	=> $status['status'] ,
			       	'url'	=> 'http://JiWai.de/thread/'.$thread_id.'/' , 
				'desc'	=> 'JiWai Thread Timeline' , 
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
    ob_end_flush();
    header('Content-Length: '.ob_get_length());
    ob_end_flush();
}

/*
 * 	return public timeline as a array
 *	@param	array	options, include: count, since_id, since
 *
 */
function getMergedStatuses($status_rows, $user_rows, $needReBuild=false){

	$statuses = array();
	$current_user_id = JWApi::GetAuthedUserId();
	ksort($status_rows);

	foreach ( $status_rows as $status_id=>$status_row )
	{
		if ( JWSns::IsProtectedStatus( $status_id, $current_user_id ) )
			$status_row['status'] = 'status protected';

		$user_row = $user_rows[$status_row['idUser']];

		$user_row['idPicture'] = ( $status_row['idPicture'] && 'MMS' != $status_row['statusType'] )
			? $status_row['idPicture'] : $user_row['idPicture'];

		$statusInfo = ($needReBuild) ?
		       	JWApi::ReBuildStatus( $status_row ) : $status_row;
		$userInfo   = ($needReBuild) ?
			JWApi::ReBuildUser($user_row) : $user_row;

		$statusInfo['user'] = $userInfo;
		$statuses[] = $statusInfo;
	}
	return $statuses;
}
?>
