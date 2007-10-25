<?php
require_once("../../../jiwai.inc.php");

$pathParam = null;
$since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? 
	$_SERVER['HTTP_IF_MODIFIED_SINCE'] : null;
extract($_REQUEST, EXTR_IF_EXISTS);

$idUser = JWApi::getAuthedUserId();
if( ! $idUser ){
	JWApi::RenderAuth( JWApi::AUTH_HTTP );
}

$since = ($since==null) ? null : ( is_numeric($since) ? date("Y-m-d H:i:s", $since) : $since );
$timeSince = ($since==null) ? null : date("Y-m-d H:i:s", strtotime($since) );
$messageIds = JWMessage::GetMessageIdsFromUser($idUser, JWMessage::INBOX,JWMessage::DEFAULT_MESSAGE_NUM, 0, $timeSince);
$messages = JWMessage::GetMessageDbRowsByIds( $messageIds['message_ids'] );

$type = strtolower($pathParam);
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
				'title'	=> $userReceiver['nameScreen'].'收到的悄悄话' , 
				'url'	=> 'http://JiWai.de/direct_messages/' , 
				'desc'	=> '所有发给'.$userReceiver['nameFull'].'悄悄话' , 
				'language' => 'zh_cn',
				'ttl'	=> 40,
				));
	
	foreach ( $messages as $m ) {
		$userSender = isset( $tempUser[$m['idUserSender']] ) ?
			$tempUser[$idUserSender] :
			( $tempUser[$idUserSender] = JWUser::GetUserInfo( $m['idUserSender'] ) );

		$feed->AddItem(array( 
				'title'	=> $userSender['nameScreen'] . '给'. $userReceiver['nameFull'].'的悄悄话',
				'desc'	=> JWApi::RemoveInvalidChar($m['message']) , 
				'date'	=> $m['timeCreate'], 
				'guid'	=> "http://JiWai.de/direct_messages/" . $m['idMessage'],
				'url'	=> "http://JiWai.de/direct_messages/" . $m['idMessage'],
				));
	}

	$feed->OutputFeed( $feedType );

}
?>
