<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$logined_user_id 	= JWLogin::GetCurrentUserId();
$logined_user_info 	= JWUser::GetUserInfo($logined_user_id);
?>

<?php JWTemplate::html_head() ?>

<body class="direct_messages" id="direct_messages">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div class="separator"></div>

<div id="container">
	<div id="content">
		<div id="wrapper">


<?php JWTemplate::ShowActionResultTips() ?>

<form action="/wo/direct_messages/create" id="doingForm" method="post" name="f">
	<fieldset>
		<div class="bar">
			<h3><label for="doing">发送给 <select id="user_id" name="user[id]">

<?php
$friend_ids = JWFriend::GetFriendIds($logined_user_id);

$friend_rows	= JWUser::GetUserDbRowsByIds($friend_ids);

function cmp($a, $b)
{
	global $friend_rows;
   	return strcmp(strtolower($friend_rows[$a]["nameScreen"]), strtolower($friend_rows[$b]["nameScreen"]));
}

usort($friend_ids, "cmp");

foreach ( $friend_ids as $friend_id )
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
$active_tab = 'inbox';

$menu_list = array (
		 'sent'		=> array('active'=>false	,'name'=>'发件箱'	,'url'=>"/wo/direct_messages/sent")
		,'inbox'	=> array('active'=>false	,'name'=>'收件箱'	,'url'=>"/wo/direct_messages")
	);

$menu_list[$active_tab]['active'] = true;

JWTemplate::tab_menu($menu_list) ;
JWTemplate::tab_header(  array(	 'title'=>'发给您的悄悄话' 
								,'title2'=>''
						) 
					) ;
?>

<div class="tab">
	<table class="doing" cellspacing="0">


<?php
$n=0;

//$message_info 		= JWMessage::GetMessageIdsFromUser	($logined_user_id, JWMessage::INBOX);
$message_info 		= JWMessage::GetMessageIdsFromUser	($logined_user_id, JWMessage::SENT);

$message_ids		= $message_info['message_ids'];
$user_ids			= $message_info['user_ids'];

$message_db_rows 	= JWMessage::GetMessageDbRowsByIds	($message_ids);
$user_db_rows 		= JWUser::GetUserDbRowsByIds		($user_ids);

$photo_url_rows		= JWPicture::GetUserIconUrlRowsByUserIds($user_ids);

foreach ( $message_ids as $message_id )
{
	//die(var_dump($message_ids));
	$message_db_row 	= $message_db_rows[$message_id];
	
	$user_id 			= $message_db_row['idUserReceiver'];

	$user_db_row		= $user_db_rows		[$user_id];
	$photo_url			= $photo_url_rows	[$user_id];

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
<tr class="odd">
	<td class="status_actions">
		<ul>
		<li><a href="/direct_messages/destroy/3195462" onclick="return confirm('确认您要删除这条悄悄话吗？删除后将无法恢复！');"><img alt="删除" border="0" src="http://assets0.twitter.com/images/icon_trash.gif?1180755379" /></a></li>
		</li>
		</ul>
	</td>

	<td class="thumb">
		<a href="http://twitter.com/xinyu19"><img alt="_____" src="http://assets3.twitter.com/system/user/profile_image/774107/normal/_____.jpg?1171560596" /></a>
	</td>
	<td>
		<strong><a href="http://twitter.com/xinyu19">maxinyu</a></strong>
		hihi
		
		<span class="meta">
			<span class="meta">
									 03:33 PM May 29, 2007
							</span>

		|
			<a href="/direct_messages/create/774107">message xinyu19</a>
		</span>
	</td>
</tr>


<tr class="even">
	<td class="status_actions">
		<ul>
		<li><a href="/direct_messages/destroy/1871282" onclick="return confirm('Sure you want to delete this message? There is NO undo!');"><img alt="Icon_trash" border="0" src="http://assets0.twitter.com/images/icon_trash.gif?1180755379" /></a></li>

		</li>
		</ul>
	</td>
	<td class="thumb">
		<a href="http://twitter.com/DAODAO19"><img alt="Default_profile_image_normal" src="http://assets1.twitter.com/images/default_profile_image_normal.gif?1180755379" /></a>
	</td>
	<td>
		<strong><a href="http://twitter.com/DAODAO19">DAODAO19</a></strong>

		老公坏蛋
		
		<span class="meta">
			<span class="meta">
									about 1 month ago
							</span>
			|
			<a href="/direct_messages/create/5533532">message DAODAO19</a>
		</span>
	</td>
</tr>


<tr class="odd">
	<td class="status_actions">
		<ul>
		<li><a href="/direct_messages/destroy/1736192" onclick="return confirm('Sure you want to delete this message? There is NO undo!');"><img alt="Icon_trash" border="0" src="http://assets0.twitter.com/images/icon_trash.gif?1180755379" /></a></li>
		</li>
		</ul>
	</td>
	<td class="thumb">

		<a href="http://twitter.com/xinyu19"><img alt="_____" src="http://assets3.twitter.com/system/user/profile_image/774107/normal/_____.jpg?1171560596" /></a>
	</td>
	<td>
		<strong><a href="http://twitter.com/xinyu19">maxinyu</a></strong>
		lg you r so cute
		
		<span class="meta">
			<span class="meta">
									 09:35 PM April 26, 2007
							</span>

			|
			<a href="/direct_messages/create/774107">message xinyu19</a>
		</span>
	</td>
</tr>


<tr class="even">
	<td class="status_actions">
		<ul>
		<li><a href="/direct_messages/destroy/451871" onclick="return confirm('Sure you want to delete this message? There is NO undo!');"><img alt="Icon_trash" border="0" src="http://assets0.twitter.com/images/icon_trash.gif?1180755379" /></a></li>

		</li>
		</ul>
	</td>
	<td class="thumb">
		<a href="http://twitter.com/xinyu19"><img alt="_____" src="http://assets3.twitter.com/system/user/profile_image/774107/normal/_____.jpg?1171560596" /></a>
	</td>
	<td>
		<strong><a href="http://twitter.com/xinyu19">maxinyu</a></strong>

		kiss....
		
		<span class="meta">
			<span class="meta">
									 06:45 PM March 18, 2007
							</span>
			|
			<a href="/direct_messages/create/774107">message xinyu19</a>
		</span>
	</td>
</tr>

  	
	</table>
	
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


