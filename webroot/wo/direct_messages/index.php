<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();
JWLogin::MustLogined();

$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page = ($page < 1 ) ? 1 : $page;

$current_user_id = JWLogin::GetCurrentUserId();
$current_user_info = JWUser::GetUserInfo($current_user_id);

if ( isset($g_direct_messages_sent) ) {
	if ( $g_direct_messages_sent === true )
		$message_box_type = JWMessage::OUTBOX;
	else 
		$message_box_type = JWMessage::NOTICE;
}
else
	$message_box_type = JWMessage::INBOX;


$n=0;

$message_num = JWMessage::GetMessageNum($current_user_id, $message_box_type);

$pagination = new JWPagination($message_num, $page);

$message_info = JWMessage::GetMessageIdsFromUser( $current_user_id, $message_box_type, $pagination->GetNumPerPage(), $pagination->GetStartPos() );

//$message_ids = $message_info['message_ids'];
$user_ids = $message_info['user_ids'];

//$message_db_rows = JWMessage::GetDbRowsByIds($message_ids);
$message_info = JWMessage::GetMessageIdsFromUser( $current_user_id, $message_box_type, $pagination->GetNumPerPage(), $pagination->GetStartPos() );

$message_ids = $message_info['message_ids'];
$message_db_rows = JWMessage::GetDbRowsByIds( $message_ids );

$user_ids = $message_info['user_ids'];
$user_db_rows = JWUser::GetDbRowsByIds($user_ids);

$picture_ids = JWFunction::GetColArrayFromRows($user_db_rows, 'idPicture');
$picture_url_row = JWPicture::GetUrlRowByIds($picture_ids);
?>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php JWTemplate::html_head();?>
</head>


<body class="normal">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div id="container">
    <div id="content">
        <div id="wrapper">

<?php JWTemplate::ShowActionResultTips(); ?>

<?php
$be_friend_ids = JWFollower::GetBioFollowingIds($current_user_id);
$friend_rows = JWUser::GetDbRowsByIds($be_friend_ids);

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
	'title' => '发送悄悄话',
	'mode' => 1,
	'friends' => $frs_rows_neworder,
));

$menu_list = array (
	JWMessage::NOTICE => array('active'=>false, 'name'=>'提醒', 'url'=>"/wo/direct_messages/notice"),
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
                <div id="wtTimeline">
<?php
foreach ( $message_db_rows as $message_id=>$message_row )
{
        switch ($message_box_type)
        {
            case JWMessage::INBOX:
                $user_id = $message_row['idUserSender'];
                break;
            case JWMessage::OUTBOX:
                $user_id = $message_row['idUserReceiver'];
                break;
            default:
                $user_id = $message_row['idUserReceiver'];
		break;
        }

        $user_db_row = $user_db_rows[$user_id];
        $user_picture_id = @$user_db_row['idPicture'];
        $photo_url = JWTemplate::GetConst('UrlStrangerPicture');
        if ( $user_picture_id )
            $photo_url = $picture_url_row[$user_picture_id];

        $asset_trash_url = JWTemplate::GetAssetUrl("/img/icon_trash.gif");

	$timeCreate = $message_row['timeCreate'];
        $time_desc = JWStatus::GetTimeDesc( $timeCreate );
	$deviceName = JWDevice::GetNameFromType($message_row['device']);

	$content_class_name = 'cont';
	if ( JWMessage::INBOX==$message_box_type && JWMessage::MESSAGE_NOTREAD==$message_row['messageStatusReceiver'] )
	{
		JWMessage::SetMessageStatus($message_row['id'], JWMessage::INBOX, JWMessage::MESSAGE_HAVEREAD);
		$content_class_name = 'content';
	}
?>
        <div class="odd" id="status_<?php echo $message_row['id']; ?>">
        <div class="head"><a href="/<?php echo $user_db_row['nameUrl']; ?>/" rel="contact"><img icon="<?php echo $user_db_row['id']; ?>" class="buddy_icon" width="48" height="48" title="<?php echo $user_db_row['nameScreen']; ?>" src="<?php echo $photo_url; ?>"/></a></div>
    <div class="<?php echo $content_class_name;?>">
        <div class="bg"></div>

<?php echo $message_row['message']; ?><br/>
            <span class="meta">
                <span class="floatright">
                    <span class="reply"><a href="/wo/direct_messages/create/<?php echo $user_id;?>/<?php echo $message_row['id']; ?>" title="<? echo $message_row['timeCreate'];?>">回复</a></span>
                    <span id="status_actions_<?php echo $message_row['id']; ?>">

<a href="/wo/direct_messages/destroy/<?php echo $message_row['id'] ?>" onclick="return confirm('确认你要删除这条悄悄话吗？删除后将无法恢复！');" title="删除"><img border="0" src="<?php echo $asset_trash_url;?>" /></a>
                    </span>
                </span>
                <a class="normLink" href="<?php echo JW_SRVNAME.'/'. $user_db_row['nameUrl']; ?>"><?php echo $user_db_row['nameScreen']; ?></a>&nbsp;<span title="<?php echo $timeCreate;?>"><?php echo $time_desc; ?></span>&nbsp;通过&nbsp;<?php echo "$deviceName"?>
            </span><!-- meta -->

<?php 
if(false == empty($message_row['idMessageReplyTo']))
{
    $reply_message_id = $message_row['idMessageReplyTo'];
    $reply_message = JWMessage::GetDbRowById($reply_message_id);
?>
	<div class="graybg">回复原文：<?php echo $reply_message['message']; ?></div>
<?php
}
?>

    </div><!-- content -->
</div><!-- odd -->
<?
}
?>

<!-- pagination -->
<?php if (1<$pagination->GetOldestPageNo()) { ?>
    <div class="line"></div>
    <div class="add">
        <div class="pages">
		<?php JWTemplate::PaginationLimit( $pagination, $page, null, 4 ); ?>
        </div>
        <div style="clear:both;"></div>
    </div>
<?php } ?>

</div><!-- wtTimeline -->

</div><!-- tab -->
</div><!-- wrapper -->
</div><!-- content -->

<?php
include_once ( dirname(dirname(__FILE__)). '/sidebar.php') ;
JWTemplate::container_ending(); 
?>

    </div>
</div><!-- #container -->
<?php JWTemplate::footer(); ?>
</body>
</html>
