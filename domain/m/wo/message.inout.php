<?php
if ( 'sent' == $action ) {
	$pageTitle = '发件箱';
	$boxType = JWMessage::OUTBOX;
	$url = '/wo/message/sent';
	$tpl = '/wo/message_sent';
} else if ('notice' == $action ) {
	$pageTitle = '提醒';
	$boxType = JWMessage::NOTICE;
	$url = '/wo/message/notice';
	$tpl = '/wo/message_notice';
} else {
	$pageTitle = '收件箱';
	$boxType = JWMessage::INBOX;
	$url = '/wo/message/inbox';
	$tpl = '/wo/message_inbox';
}


$messageNum = JWMessage::GetMessageNum( $loginedUserInfo['id'], $boxType );
$pagination = new JWPagination( $messageNum, $page , 10 );

$messageInfo = JWMessage::GetMessageIdsFromUser( $loginedUserInfo['id'], $boxType, $pagination->GetNumPerPage(), $pagination->GetStartPos()); 

$messageIds = $messageInfo['message_ids'];
$userIds    = $messageInfo['user_ids'];

$messageRows    = JWMessage::GetDbRowsByIds( $messageIds );
$userRows       = JWDB_Cache_User::GetDbRowsByIds( $userIds);

if( $boxType == JWMessage::INBOX ) {
	$messageIdsUpdate = array();
	foreach( $messageRows as $r ) {
		if( $r['messageStatusReceiver'] == JWMessage::MESSAGE_NOTREAD ) {
			array_push( $messageIdsUpdate, $r['id'] );
		}
	}
	JWMessage::SetMessageStatus( $messageIdsUpdate, JWMessage::INBOX, JWMessage::MESSAGE_HAVEREAD );
}

krsort( $messageRows );

$pageString = paginate( $pagination, $url );
$shortcut = array('logout', 'public_timeline', 'my', 'followings', 'index', 'search', 'replies');
JWRender::Display( $tpl, array(
            'messages' => $messageRows,
            'users' => $userRows,
            'loginedUserInfo' => $loginedUserInfo,
            'pageString' => $pageString,
            'shortcut' => $shortcut,
        ));

?>
