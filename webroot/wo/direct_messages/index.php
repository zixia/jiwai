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

<div class="separator"></div>

<div id="container">
	<div id="content">
		<div id="wrapper">

<style type="text/css">
#content #doingForm .bar {
line-height:2.5em;
padding:0pt 10px;
position:relative;
}
</style>

<?php JWTemplate::ShowActionResultTips() ?>

<form action="/wo/direct_messages/create" id="doingForm" method="post" name="f">
	<fieldset>
		<div class="bar odd">
			<h3><label for="doing">发送给 <select id="user_id" name="user[id]">

<?php
$be_friend_ids = JWFriend::GetBeFriendIds($logined_user_id);

$friend_rows	= JWUser::GetUserDbRowsByIds($be_friend_ids);

function cmp($a, $b)
{
	global $friend_rows;
   	return strcmp(strtolower($friend_rows[$a]["nameScreen"]), strtolower($friend_rows[$b]["nameScreen"]));
}

usort($be_friend_ids, "cmp");

foreach ( $be_friend_ids as $friend_id )
{
	$friend_row = $friend_rows[$friend_id];
	echo <<<_HTML_
<option value="$friend_id">$friend_row[nameScreen]</option>

_HTML_;
}
?>
</select> 一条悄悄话。</label></h3>

			<span>
				还可输入：<strong id="status-field-char-counter"></strong>个字
			</span>
		</div>

		<div class="info">
			<textarea cols="15" id="text" name="text" onkeypress="return (event.keyCode == 8) || (this.value.length &lt; 140);" onkeyup="updateStatusTextCharCounter(this.value)" rows="3"></textarea>
		</div>

		<div class="submit">
			<input id="submit" name="commit" class="buttonSubmit" value="送出悄悄话" type="submit">
		</div>

	</fieldset>
</form>

<script type="text/javascript">
//<![CDATA[
$('submit').onmouseover = function(){
    this.className += "Hovered"; 
}

$('submit').onmouseout = function(){
    this.className = this.className.replace(/Hovered/g, "");
}

//]]>
</script>

<script type="text/javascript">
//<![CDATA[
$('text').focus()
//]]>
</script>

<script type="text/javascript">
//<![CDATA[

	function updateStatusTextCharCounter(value) {
		$('status-field-char-counter').innerHTML = 140 - value.length;
	};

//]]>
</script>
<script type="text/javascript">
//<![CDATA[
$('status-field-char-counter').innerHTML = 140 - $('text').value.length;
//]]>
</script>

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
	<table class="doing" cellspacing="0">


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

	$tr_class	= $n++%2?'even':'odd';

	$asset_trash_url	= JWTemplate::GetAssetUrl("/img/icon_trash.gif");

	$time_desc			= JWMessage::GetTimeDesc($message_db_row['timeCreate']);

	echo <<<_HTML_
<tr class="$tr_class">
	<td class="status_actions">
		<ul>
		<li><a href="/wo/direct_messages/destroy/$message_db_row[idMessage]" onclick="return confirm('确认您要删除这条悄悄话吗？删除后将无法恢复！');"><img alt="删除" border="0" src="$asset_trash_url" /></a></li>
		</li>
		</ul>
	</td>

	<td class="thumb">
		<a href="/$user_db_row[nameScreen]/"><img alt="$user_db_row[nameFull]" src="$photo_url" /></a>
	</td>
	<td>
		<strong><a href="/$user_db_row[nameScreen]/">$user_db_row[nameScreen]</a></strong>
		$message_db_row[message]
		
		<span class="meta">
			<span class="meta">
									 $time_desc
							</span>

		|
			<a href="/wo/direct_messages/create/$user_id">悄悄话 $user_db_row[nameScreen]</a>
		</span>
	</td>
</tr>


_HTML_;
}

?>
  	
	</table>
	
<?php JWTemplate::pagination($pagination); ?>

</div>


		</div><!-- wrapper -->
	</div><!-- content -->

<?php
$arr_count_param	= JWSns::GetUserState($logined_user_id);

$arr_friend_list	= JWFriend::GetFriendIds($logined_user_id);

$arr_menu 			= array(	array ('status'			, array($logined_user_info))
								, array ('count'		, array($arr_count_param))
								, array ('friend'		, array($arr_friend_list))
							);
	
JWTemplate::sidebar( $arr_menu );

?>
	
</div><!-- #container -->

<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>


