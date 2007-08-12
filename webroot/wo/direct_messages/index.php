<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$logined_user_id 	= JWLogin::GetCurrentUserId();
$logined_user_info 	= JWUser::GetUserInfo($logined_user_id);

if ( isset($g_direct_messages_sent) && $g_direct_messages_sent )
	$message_box_type = JWMessage::SENT;
else
	$message_box_type = JWMessage::INBOX;
?>

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
$be_friend_ids = JWFriend::GetBeFriendIds($logined_user_id);

$friend_rows	= JWUser::GetUserDbRowsByIds($be_friend_ids);

function cmp($a, $b)
{
	global $friend_rows;
   	return strcmp(strtolower($a["nameScreen"]), strtolower($b["nameScreen"]));
}

usort($friend_rows, "cmp");

JWTemplate::updater(array(
	'title' 	=> '发送悄悄话',
	'mode'		=> 1,
	'friends'	=> $friend_rows
	));
?>
	</fieldset>
</form>
<?php
$menu_list = array (
		 JWMessage::SENT		=> array('active'=>false	,'name'=>'发件箱'	,'url'=>"/wo/direct_messages/sent")
		,JWMessage::INBOX		=> array('active'=>false	,'name'=>'收件箱'	,'url'=>"/wo/direct_messages/")
	);

$menu_list[$message_box_type]['active'] = true;

JWTemplate::tab_menu($menu_list) ;

$options = array ( 'title2'=>'' );
switch ( $message_box_type )
{
	default:
	case JWMessage::INBOX:
		$options['title'] = '您收到的悄悄话';
		break;
	case JWMessage::SENT:
		$options['title'] = '您发送的悄悄话';
		break;
}

JWTemplate::tab_header( $options );
?>

<div class="tab">
	<div class="pagination">
        <table cellspacing="1" cellpadding="0" border="0">

          <tr>
            <td width="340" style="border-right:1px solid #D3D3D5;">内容</td>
            <td width="88" style="border-right:1px solid #D3D3D5; border-left:1px solid #ffffff;">发送人</td>
            <td style="border-left:1px solid #ffffff;">时间</td>
          </tr>
        </table>
        <!-- div id="tips"><a href="#">X</a>只显示包括“关键字”的悄悄话</div -->
        <div id="timeline" style="margin-top:0;">

<?php
$n=0;

$message_num		= JWMessage::GetMessageNum 			($logined_user_id, $message_box_type);

$pagination			= new JWPagination					($message_num, @$_REQUEST['page']);

$message_info 		= JWMessage::GetMessageIdsFromUser	(	 $logined_user_id
															,$message_box_type
															,$pagination->GetNumPerPage()
															,$pagination->GetStartPos()
														);

$message_ids		= $message_info['message_ids'];
$user_ids			= $message_info['user_ids'];

$message_db_rows 	= JWMessage::GetMessageDbRowsByIds	($message_ids);
$user_db_rows 		= JWUser::GetUserDbRowsByIds		($user_ids);

$picture_ids        = JWFunction::GetColArrayFromRows($user_db_rows, 'idPicture');
$picture_url_row   	= JWPicture::GetUrlRowByIds($picture_ids);

//$photo_url_rows		= JWPicture::GetUserIconUrlRowsByUserIds($user_ids);

foreach ( $message_ids as $message_id )
{
	//die(var_dump($message_ids));
	$message_db_row 	= $message_db_rows[$message_id];
	
	switch ($message_box_type)
	{
		default:
		case JWMessage::INBOX:
			$user_id 			= $message_db_row['idUserSender'];
			break;
		case JWMessage::SENT:
			$user_id 			= $message_db_row['idUserReceiver'];
			break;
	}
			

	$user_db_row		= $user_db_rows		[$user_id];

	$user_picture_id    = @$user_db_row['idPicture'];
	$photo_url      = JWTemplate::GetConst('UrlStrangerPicture');
	if ( $user_picture_id )
		$photo_url		= $picture_url_row[$user_picture_id];

	$asset_trash_url	= JWTemplate::GetAssetUrl("/img/icon_trash.gif");

	$time_desc			= JWMessage::GetTimeDesc($message_db_row['timeCreate']);

	echo <<<_HTML_
          <div class="odd">
            <div class="head"><a href="/wo/direct_messages/create/$user_id" title="悄悄话发给$user_db_row[nameScreen]"><img alt="$user_db_row[nameFull]" src="$photo_url" width="48" height="48"/></a></div>
            <div class="cont">$message_db_row[message]
		<a href="/wo/direct_messages/destroy/$message_db_row[idMessage]" onclick="return confirm('确认您要删除这条悄悄话吗？删除后将无法恢复！');"><img alt="删除" border="0" src="$asset_trash_url" /></a>
            </div>
            <div class="write"><a href="/$user_db_row[nameScreen]/">$user_db_row[nameFull]</a></div>
            <div class="time"> $time_desc </div>
          </div>

	<div class="line"></div>

_HTML_;
}

?>
  	
	</div>

</div>
</div>


		</div><!-- wrapper -->
	</div><!-- content -->

<?php
$arr_count_param	= JWSns::GetUserState($logined_user_id);

$arr_friend_list	= JWFriend::GetFriendIds($logined_user_id);

$device_row			= JWDevice::GetDeviceRowByUserId($logined_user_id);
$active_options = array();
$supported_device_types = JWDevice::GetSupportedDeviceTypes();
foreach ( $supported_device_types as $type )
{
	if ( isset($device_row[$type]) 
				&& $device_row[$type]['verified']  )
	{	
		$active_options[$type]	= true;
	}
	else
	{
		$active_options[$type] 	= false;
	}
}
$via_device			= JWUser::GetSendViaDevice($logined_user_id);

$arr_menu 			= array(	array ('status'			, array($logined_user_info))
								, array ('count'		, array($arr_count_param))
					,array ('jwvia'		, array($active_options, $via_device)) 
					,array ('separator'	, array()) 
								, array ('friend'		, array($arr_friend_list))
							);
	
JWTemplate::sidebar( $arr_menu );
JWTemplate::container_ending();

?>
	
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>


