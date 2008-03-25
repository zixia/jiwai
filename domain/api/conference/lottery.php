<?php
require_once(dirname(__FILE__) . "/../../../jiwai.inc.php");

$pathParam = null;
$page = 1;
$idUserObject = null;
$idConference = null;
$callback = null;
$count = 20;
$since=null;
$since_id=null;
extract($_REQUEST, EXTR_IF_EXISTS);
$page = ( $page < 1 ) ? 1 : intval($page);
$start = JWFavourite::DEFAULT_FAVORITE_MAX * ( $page - 1 );

$type = strtolower( $pathParam );
$params = explode('.', $pathParam);
if( count($params) == 2 ) {
	list($idUserObject, $type) = $params;
}

$idUser = JWApi::getAuthedUserId();
if( !$idUser ){
	JWApi::RenderAuth( JWApi::AUTH_HTTP );
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
	'page' => intval($page),
	'count' => intval($count),
	'callback' => $callback,
	'idConference' => intval($idConference),
    'since' => $since,
    'since_id'  => $since_id,
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
	$statuses = getLottety( $options, true );
	$xmlString = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= '<?xml version="1.0" encoding="UTF-8"?>';
	$xmlString .= JWApi::ArrayToXml($statuses, 0, 'statuses');

	echo $xmlString;
}

function renderJsonReturn($options){
	$statuses = getLottety( $options, true );
	if( $options['callback'] ){
		echo $options['callback'].'('. json_encode($statuses) .')';
	}else{
		echo json_encode($statuses);
	}
}

function renderFeedReturn($options, $feedType=JWFeed::ATOM){

	$statuses = getLottety( $options, false );

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
 * 	return lottery as a array
 *	@param	array	options, include: count, since_id, since
 *
 */
function getLottety($options, $needReBuild=false){
	/* Twitter compatible */

    $count = $options['count'];
    $max_count = 20;
	if ( $count > $max_count )
		$count = $max_count;

    $idConference = intval($options['idConference']);
	
    $options['timeSince'] = ($options['since']==null)
        ? null
        : date("Y-m-d H:i:s", strtotime($options['since']) );

    $luckyData = getMobileShuffled( $idConference, $count, $options);
	$statuses = array();

	foreach ( $luckyData as $lucky){
		$statusInfo['mobile'] = $lucky;
		$statuses[] = $statusInfo;
	}

	return $statuses;
}

/**
 * @param
 * @return
 */
function getMobileShuffled($idConference, $count = 20, $options = null) {
    $ret    = array();
    $rand   = array();
    $tCount = 0;

    $iCount = intval($count);
    $iIdConference = intval($idConference);

    $timeSince = $options['timeSince'];
    $iIdSince = $options['since_id'];

    if (!$timeSince) $timeSince = date("Y-m-d H:i:s", 0);
    if (!$iIdSince) $iIdSince = 0;

    $query = <<<__SQL__
        SELECT COUNT(DISTINCT Device.address) AS Cnt
        FROM Device, Status
        WHERE Status.idConference=$iIdConference
        AND Device.idUser=Status.idUser
        AND Status.device='sms'
        AND Device.type='sms'
        AND Status.timeCreate > "$timeSince"
        AND Status.id > $iIdSince
__SQL__;

    try {
        $dbQuery = JWDB::GetQueryResult($query, false, true);
    } catch (Exception $e) {
        return $ret;
    }

    if (empty($dbQuery['Cnt']) || $dbQuery['Cnt'] < 1) {
        return $ret;
    }

    $tCount = intval($dbQuery['Cnt']);

    $query = <<<__SQL__
        SELECT DISTINCT(Device.address) AS Addr
        FROM Device, Status
        WHERE Status.idConference=$iIdConference
        AND Device.idUser=Status.idUser
        AND Status.device='sms'
        AND Device.type='sms'
        AND Status.timeCreate > "$timeSince"
        AND Status.id > $iIdSince
        LIMIT 0, $tCount
__SQL__;

    try {
        $dbQuery = JWDB::GetQueryResult($query, true, true);
    } catch (Exception $e) {
        return $ret;
    }

    foreach ($dbQuery as $k=>$v) {
        $ret[] = $v['Addr'];
    }

    if ($tCount <= $iCount) {
        return $ret;
    }

    shuffle($ret);

    return array_slice($ret, 0, $iCount);

}
?>
