<?php
require_once("../../../jiwai.inc.php");

$pathParam = null;
$page = 1;
$idUserObject = null;
$callback = null;
$count = 20;
extract($_REQUEST, EXTR_IF_EXISTS);
$page = ( $page < 1 ) ? 1 : intval($page);
$start = JWFavourite::DEFAULT_FAVORITE_MAX * ( $page - 1 );

$type = strtolower( $pathParam );
$params = explode('.', $pathParam);
if( count($params) == 2 ) {
	list($idUserObject, $type) = $params;
}

if ( false == ($idUserObject == 50641) )
{
	$idUser = JWApi::getAuthedUserId();
	if( !$idUser ){
		JWApi::RenderAuth( JWApi::AUTH_HTTP );
	}
}

if( $idUserObject ) {
	$objectUser = JWUser::GetUserInfo( $idUserObject );
	if( empty( $objectUser ) ){
		JWApi::OutHeader(404, true);
	}
	$idUserObject = $objectUser['id'];
	if( $objectUser['protected'] == 'Y' ) {
		if( false == JWFollower::IsFollower( $idUser, $objectUser['id'] ) ){
			JWApi::OutHeader( 403, true );
		}
	}
} else {
	$idUserObject = $idUser;
}


if( !in_array( $type, array('json','xml','atom','rss') )){
	JWApi::OutHeader(406, true);
}

$options = array(
	'idUserObject' => $idUserObject,
	'page' => $page,
	'count' => $count,
	'callback' => $callback,
);

switch( $type ){
	case 'xml':
		renderXmlReturn( $options );
	break;
	case 'json':
		renderJsonReturn( $options );
	break;
	case 'atom':
		renderFeedReturn( $options, JWFeed::ATOM);
	break;
	case 'rss':
		renderFeedReturn( $options, JWFeed::RSS20);
	break;
	default:
		JWApi::OutHeader(406, true);
}

function renderXmlReturn($options){
	$statuses = getFavouriteStatuses( $options, true );
	$xmlString = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= '<?xml version="1.0" encoding="UTF-8"?>';
	$xmlString .= JWApi::ArrayToXml($statuses, 0, 'statuses');

	echo $xmlString;
}

function renderJsonReturn($options){
	$statuses = getFavouriteStatuses( $options, true );
	if( $options['callback'] ){
		echo $options['callback'].'('. json_encode($statuses) .')';
	}else{
		echo json_encode($statuses);
	}
}

function renderFeedReturn($options, $feedType=JWFeed::ATOM){

	$statuses = getFavouriteStatuses( $options, false );

	$feed = new JWFeed(array(
				'title'	=> '叽歪 / '. $user['nameScreen'],
			       	'url'	=> 'http://JiWai.de/'.$user['nameUrl'] , 
				'desc'	=> $user['nameScreen'].'的叽歪de更新' , 
				'ttl'	=> 40,
				)); 

	foreach ( $statuses as $status ){

		$feed->AddItem(array( 
				'title'	=> JWApi::RemoveInvalidChar($status['status']) , 
				'desc'	=> $status['user']['nameScreen'] . ' - ' . JWApi::RemoveInvalidChar($status['status']) , 
				'date'	=> strtotime( $status['timeCreate'] ), 
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
function getFavouriteStatuses($options, $needReBuild=false)
{
	/* Twitter compatible */

	$idUserObject = $options['idUserObject'];

	$count	= intval($options['count']);
	if ( 0>=$count )
		$count = JWFavourite::DEFAULT_FAVORITE_MAX;
	
	$page = intval($options['page']);
	if( 1>=$page ) $page = 1;
	$start = $count * ( $page - 1 );

	$favouriteData = JWFavourite::GetFavouriteData($idUserObject, $count, $start);
	$statusIds = empty($favouriteData) ? array() : $favouriteData['status_ids'] ;
	$favouriteIds = empty($favouriteData) ? array() : $favouriteData['favourite_ids'] ; 
	$statusRows = JWStatus::GetDbRowsByIds($statusIds);

	$status_rows = array();
	foreach( $statusIds as $status_id ) 
	{
		$favourite_id = array_shift( $favouriteIds );
		if( isset( $statusRows[ $status_id ] ) )
		{
			$status = $statusRows[ $status_id ];
			$status[ 'favourite_id'] = $favourite_id;
			array_push( $status_rows, $status);
		}
	}

	$user_rows = array();
	$statuses = array();
	foreach ( $status_rows as $status )
	{
		$user_id = intval($status['idUser']);
		
		$statusInfo = ($needReBuild) ?
		       	JWApi::ReBuildStatus( $status ) : $status;

		if( false == isset($user_rows[$user_id]) ) 
		{
			$user_rows[$user_id] = JWUser::GetUserInfo( $user_id );
		}

		$user_row = $user_rows[$user_id];
		$user_row['idPicture'] = ( $status['idPicture'] && 'MMS' != $status['statusType'] )
			? $status['idPicture'] : $user_row['idPicture'];

		$userInfo   = ($needReBuild) ?
			JWApi::ReBuildUser($user_row) : $user_row;
		$statusInfo['user'] = $userInfo;
		$statuses[] = $statusInfo;
	}

	return $statuses;
}
?>
