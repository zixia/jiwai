<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();
JWLogin::MustLogined();

$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page = ($page < 1 ) ? 1 : $page;

$logined_user_id = JWLogin::GetCurrentUserId();
$logined_user_info = JWUser::GetUserInfo($logined_user_id);

if ( isset($g_direct_messages_sent) && $g_direct_messages_sent )
	$message_box_type = JWMessage::OUTBOX;
else
	$message_box_type = JWMessage::INBOX;


$n=0;

$message_num = JWMessage::GetMessageNum($logined_user_id, $message_box_type);

$pagination = new JWPagination($message_num, $page);

$message_info = JWMessage::GetMessageIdsFromUser( $logined_user_id, $message_box_type, $pagination->GetNumPerPage(), $pagination->GetStartPos() );

$message_ids = $message_info['message_ids'];
$user_ids = $message_info['user_ids'];

$message_db_rows = JWMessage::GetMessageDbRowsByIds($message_ids);
$user_db_rows = JWDB_Cache_User::GetDbRowsByIds($user_ids);

$picture_ids = JWFunction::GetColArrayFromRows($user_db_rows, 'idPicture');
$picture_url_row = JWPicture::GetUrlRowByIds($picture_ids);

if ( $message_box_type == JWMessage::INBOX ) {
	$messageIdsUpdate = array();
	foreach ( $message_ids as $message_id ) {
		if( $message_db_rows[$message_id]['messageStatusReceiver'] == JWMessage::MESSAGE_NOTREAD )
			array_push( $messageIdsUpdate, $message_id );
	}
	JWMessage::SetMessageStatus($messageIdsUpdate, JWMessage::INBOX, JWMessage::MESSAGE_HAVEREAD);
}

?>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php JWTemplate::html_head() ?>
</head>

<body class="direct_messages" id="direct_messages">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div id="container">
	<div id="content">
		<div id="wrapper">

<?php JWTemplate::ShowActionResultTips() ?>

<?php
$be_friend_ids = JWDB_Cache_Follower::GetBioFollowingIds($logined_user_id);

$friend_rows	= JWDB_Cache_User::GetDbRowsByIds($be_friend_ids);

function cmp($a, $b)
{
	global $friend_rows;
   	return strcmp(strtolower($a["nameScreen"]), strtolower($b["nameScreen"]));
}

usort($friend_rows, "cmp");
$frs_rows_neworder = array();
foreach($friend_rows as $f){
    $frs_rows_neworder[$f['id']] = $f;
}

JWTemplate::updater(array(
	'title' 	=> '发送悄悄话',
	'mode'		=> 1,
	'friends'	=> $frs_rows_neworder,
	));
?>

<?php
$menu_list = array (
	JWMessage::OUTBOX => array('active'=>false, 'name'=>'发件箱', 'url'=>"/wo/direct_messages/sent"),
	JWMessage::INBOX => array('active'=>false, 'name'=>'收件箱', 'url'=>"/wo/direct_messages/"),
);

$menu_list[$message_box_type]['active'] = true;

$options = array ( 'title2'=>'' );
switch ( $message_box_type )
{
	default:
	case JWMessage::INBOX:
		$options['title'] = '你收到的悄悄话';
		$owner = '发送者';
		break;
	case JWMessage::OUTBOX:
		$options['title'] = '你发送的悄悄话';
		$owner = '接收者';
		break;
}

JWTemplate::tab_menu( $menu_list, $options['title'] );
?>

<div class="tab">
	<div class="pagination">
        <table cellspacing="1" cellpadding="0" border="0">

          <tr>
            <td width="340" style="border-right:1px solid #D3D3D5;">内容</td>
            <td width="88" style="border-right:1px solid #D3D3D5; border-left:1px solid #ffffff;"><?php echo $owner;?></td>
            <td style="border-left:1px solid #ffffff;">时间</td>
          </tr>
        </table>
        <!-- div id="tips"><a href="#">X</a>只显示包括“关键字”的悄悄话</div -->
        <div id="timeline" style="margin-top:0;">

<?php

foreach ( $message_ids as $message_id )
{
	$message_db_row = $message_db_rows[$message_id];
	
	switch ($message_box_type)
	{
		default:
		case JWMessage::INBOX:
			$user_id = $message_db_row['idUserSender'];
			break;
		case JWMessage::OUTBOX:
			$user_id = $message_db_row['idUserReceiver'];
			break;
	}

	$user_db_row = $user_db_rows[$user_id];

	$user_picture_id = @$user_db_row['idPicture'];
	$photo_url = JWTemplate::GetConst('UrlStrangerPicture');
	if ( $user_picture_id )
		$photo_url = $picture_url_row[$user_picture_id];

	$asset_trash_url = JWTemplate::GetAssetUrl("/img/icon_trash.gif");

	$time_desc = JWMessage::GetTimeDesc($message_db_row['timeCreate']);

	if( $message_box_type == JWMessage::INBOX ){
		$replyString = <<<_REPLY_
		<a href="/wo/direct_messages/create/$user_id">回复</a>
_REPLY_;
	}else{
		$replyString = null;
	}

	echo <<<_HTML_
          <div class="odd">
            <div class="head"><a href="/wo/direct_messages/create/$user_id" title="悄悄话发给$user_db_row[nameScreen]"><img alt="$user_db_row[nameScreen]" src="$photo_url" width="48" height="48"/></a></div>
            <div class="cont">$message_db_row[message] $replyString
		<a href="/wo/direct_messages/destroy/$message_db_row[idMessage]" onclick="return confirm('确认你要删除这条悄悄话吗？删除后将无法恢复！');"><img alt="删除" border="0" src="$asset_trash_url" /></a>
            </div>
            <div class="write"><a href="/$user_db_row[nameUrl]/">$user_db_row[nameScreen]</a></div>
            <div class="time"> $time_desc </div>
          </div>
        <div style="clear:both;"></div>

_HTML_;
}
?>
	</div>
</div>
<?php JWTemplate::PaginationLimit( $pagination, $page, null, 4 ); ?>
</div>


		</div><!-- wrapper -->
	</div><!-- content -->

<?php
include_once ( dirname(dirname(__FILE__)). '/sidebar.php') ;
JWTemplate::container_ending(); 
?>

</div><!-- #container -->

	
<?php JWTemplate::footer() ?>

</body>
</html>
