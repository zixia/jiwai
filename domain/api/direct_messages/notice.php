<?php
require_once("../../../jiwai.inc.php");

$pathParam = null;
$page = 1;
$since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : null;
$since_id = null;
extract($_REQUEST, EXTR_IF_EXISTS);
$page = ( $page < 1 ) ? 1 : intval($page);
$start = JWMessage::DEFAULT_MESSAGE_NUM * ( $page - 1 );

$idUser = JWApi::getAuthedUserId();
if( ! $idUser ){
	JWApi::RenderAuth( JWApi::AUTH_HTTP );
}

$since = ($since==null) ? null : ( is_numeric($since) ? date("Y-m-d H:i:s", $since) : $since );
$timeSince = ($since==null) ? null : date("Y-m-d H:i:s", strtotime($since) );
$idSince = abs(intval($since_id));
$messageIds = JWMessage::GetMessageIdsFromUser($idUser, JWMessage::NOTICE,JWMessage::DEFAULT_MESSAGE_NUM, $start, $idSince, $timeSince);
$messages = JWMessage::GetDbRowsByIds( $messageIds['message_ids'] );

$type = strtolower(trim($pathParam,'.'));
if( !in_array( $type, array('json','xml','atom','rss') )){
	JWApi::OutHeader(406, true);
}

switch( $type ){
	case 'xml':
		renderXmlReturn( $messages );
	break;
	case 'json':
		renderJsonReturn( $messages );
	break;
	case 'atom':
		renderFeedReturn( $messages, $idUser, JWFeed::ATOM);
	break;
	case 'rss':
		renderFeedReturn( $messages, $idUser, JWFeed::RSS20);
	break;
	default:
		JWApi::OutHeader(406, true);
}

function rebuildMessages( $messages, $needReBuild=true ){
	$rtn = array();
	foreach( $messages as $m ){
		$rtn[] = JWApi::ReBuildMessage( $m );
	}
	return $rtn;
}

function renderJsonReturn( $messages ){
	$messages = rebuildMessages( $messages );
	echo json_encode( $messages );
}

function renderXmlReturn( $messages ){
	$messages = rebuildMessages( $messages );
	
	$xmlString = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $messages, 0, 'direct_messages' );
	echo $xmlString;

}

function renderFeedReturn( $messages, $idUser, $feedType=JWFeed::RSS20 ){

	$tempUser = array();
	$userReceiver = isset( $tempUser[$idUser] ) ?
		$tempUser[$idUser] :
		( $tempUser[$idUser] = JWUser::GetUserInfo( $idUser ) );

	$feed = new JWFeed(array(
				'title'	=> $userReceiver['nameScreen'].'收到的提醒' , 
				'url'	=> 'http://JiWai.de/direct_messages/' , 
				'desc'	=> '所有发给'.$userReceiver['nameScreen'].'的提醒' , 
				'language' => 'zh_cn',
				'ttl'	=> 40,
				));
	
	foreach ( $messages as $m ) {
		$userSender = isset( $tempUser[$m['idUserSender']] ) ?
			$tempUser[$m['idUserSender']] :
			( $tempUser[$m['idUserSender']] = JWUser::GetUserInfo( $m['idUserSender'] ) );

		$feed->AddItem(array( 
				'title'	=> '来自'.$userSender['nameScreen'] . '的提醒',
				'desc'	=> JWApi::RemoveInvalidChar($m['message']) , 
				'author' => $userSender['nameScreen'],
				'date'	=> strtotime($m['timeCreate']), 
				'guid'	=> "http://JiWai.de/direct_messages/" . $m['idMessage'],
				'url'	=> "http://JiWai.de/direct_messages/" . $m['idMessage'],
				));
	}

	$feed->OutputFeed( $feedType );

}
?>
