<?php
require_once("../../../jiwai.inc.php");

$pathParam = null;
$page = 1;
extract($_REQUEST, EXTR_IF_EXISTS);
$page = ( $page < 1 ) ? 1 : intval($page);

$pathParam = trim( $pathParam, '/' );
if( ! $pathParam ) {
	JWApi::OutHeader(400, true);
}

$authed = false;
@list($_, $type) = explode( ".", $pathParam, 2);
if( !in_array( $type, array('json','xml','atom','rss') )){
	JWApi::OutHeader(406, true);
}

$idUser = JWApi::GetAuthedUserId();
if( !$idUser ){
	JWApi::RenderAuth(JWApi::AUTH_HTTP);
}

switch( $type ){
	case 'json':
		renderJsonStatuses($idUser);
	break;
	case 'xml':
		renderXmlStatuses($idUser);
	break;
	case 'atom':
		renderFeedStatuses($idUser, JWFeed::ATOM);
	break;
	case 'rss':
		renderFeedStatuses($idUser, JWFeed::RSS20);
	break;
	default:
		JWApi::OutHeader(406, true);
}

function renderJsonStatuses($idUser){
	$statusesWithUser = getStatusesWithUser( $idUser );
	echo json_encode( $statusesWithUser );
}

function renderXmlStatuses($idUser){
	$statusesWithUser = getStatusesWithUser( $idUser );
	
	$xmlString = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $statusesWithUser, 0, "statuses" );
	echo $xmlString;
}

function renderFeedStatuses($idUser, $feedType) {
	$user = JWUser::GetUserInfo( $idUser );
	$statusesWithUser = getStatusesWithUser( $idUser , false);

	$feed = new JWFeed( array (	
				'title'		=> 'JiWai/叽歪 - 对'.$user['nameScreen'].'的回复' , 
				'url'		=> 'http://JiWai.de/'.$user['nameUrl'] ,
				'desc'		=> 'JiWai/叽歪 - 对'.$user['nameScreen'].'的回复' , 
				'ttl'		=> 40,
			));

	foreach ( $statusesWithUser as $status )
	{
		$feed->AddItem(array( 
				'title'	=> $status['user']['nameScreen'] . ' - ' . JWApi::RemoveInvalidChar($status['status']) , 
				'desc'	=> $status['user']['nameScreen'] . ' - ' . JWApi::RemoveInvalidChar($status['status']) , 
				'date'	=> $status['timeCreate'] , 
				'author'=> $status['user']['nameScreen'] , 
				'guid'	=> "http://JiWai.de/" . $status['user']['nameUrl'] . "/statuses/" . $status['idStatus'] , 
				'url'	=> "http://JiWai.de/" . $status['user']['nameUrl'] . "/statuses/" . $status['idStatus'] , 
			) );
	}
	$feed->OutputFeed($feedType);
}

/**
  * 获取回复给idUser Status并内含user信息
  * @param $idUser, 用户id
  * @param $needRebuild, 是不是按照 xml/json方式，重新组织field名
  */
function getStatusesWithUser($idUser, $needReBuild=true){

	global $page;
	if( 1>=$page ) $page = 1;
	$start = 20 * ( $page - 1 );

	$statusIds = JWStatus::GetStatusIdsFromReplies( $idUser, 20, $start);
	$statuses = JWStatus::GetDbRowsByIds( $statusIds['status_ids'] );
	$statusesWithUser = array();
	$userTemp = array();
	foreach( $statusIds['status_ids'] as $sid ){
		$s = isset($statuses[$sid]) ? $statuses[$sid] : null;
		if( !$s )
			continue;
		$oInfo = $needReBuild ? JWApi::ReBuildStatus( $s ) : $s;
		if( false === isset( $userTemp[$s['idUser']] ) ){
			$user = JWUser::GetUserInfo($s['idUser']);
			$userTemp[$s['idUser']] = $needReBuild ? JWApi::ReBuildUser($user) : $user;
		}
		$oInfo['user'] = $userTemp[$s['idUser']];
		$statusesWithUser[] = $oInfo;
	}
	return $statusesWithUser;
}

?>
