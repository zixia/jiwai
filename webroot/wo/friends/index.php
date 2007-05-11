<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');

JWLogin::MustLogined();

/*
 *	除了显示 /wo/friends/ 之外，还负责显示 /zixia/friends/
 *	如果是其他用户的 friends 页(/zixia/friends)，则 $g_user_friends = true, 并且 $g_page_user_id 是页面用户 id
 *
 */
$logined_user_info 	= JWUser::GetCurrentUserInfo();

if ( isset($g_user_friends) && $g_user_friends ) {
	$rows				= JWUser::GetUserRowById(array($g_page_user_id));
	$page_user_info		= $rows[$g_page_user_id];
} else {
	$page_user_info		= $logined_user_info;
}

$friend_ids			= JWFriend::GetFriend		($page_user_info['id']);
$friend_user_rows	= JWUser::GetUserRowById	($friend_ids);
$friend_icon_url_rows = JWPicture::GetUserIconUrlRowById($friend_ids);

$friend_num			= JWFriend::GetFriendNum	($page_user_info['id']);
?>

<html>

<?php JWTemplate::html_head() ?>

<body class="friends" id="friends">


<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div class="separator"></div>

<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">


<?php 
if ( $page_user_info['id']==$logined_user_info['id'] )
{
	echo <<<_HTML_
			<h2> 我的 $friend_num 位好友。
		  		<a href="/wo/invitations/invite">邀请更多！</a>
			</h2>
_HTML_;
} 
else 
{
	echo <<<_HTML_
			<h2> $page_user_info[nameScreen] 的 $friend_num 位好友。</h2>
_HTML_;
	
}
?>
<style type="text/css">
.friend-actions ul li
{
	display: inline;
}

.subpage #content p {
line-height:1.2;
margin:5px 0pt;
}
.friend-actions {
text-indent:0.6em;
}

</style>
	
<table class="doing" cellspacing="0">

<?php
$n = 0;
if ( isset($friend_ids) )
{
	foreach ( $friend_ids as $friend_id )
	{
		$friend_info		= $friend_user_rows[$friend_id];
		$friend_icon_url	= $friend_icon_url_rows[$friend_id];

	/*
	 * /webroot/user/friends.php 会调用(include)这个页面
	 * 这时候，所有用户给出 follow 的操作
	 */
	if ( isset($g_user_friends) ) {
		if ( JWFollower::IsFollower($friend_id, $logined_user_info['id']) )
			$action = array ( 'leave'=>true );
		else
			$action = array ( 'follow'=>true );
	} else {
		$action	= JWSns::GetUserAction($logined_user_info['id'],$friend_id);
	}

	$odd_even			= ($n++ % 2) ? 'odd' : 'even';
	echo <<<_HTML_
	<tr class="$odd_even vcard">
		<td class="thumb">
			<a href="http://jiwai.de/$friend_info[nameScreen]/"><img alt="$friend_info[nameFull]" class="photo" src="$friend_icon_url" /></a>
		</td>
		<td>
			<strong>
		  		<a href="http://jiwai.de/$friend_info[nameScreen]/" class="url"><span class="fn">$friend_info[nameFull]</span> (<span class="uid">$friend_info[nameScreen]</span>)</a>
			</strong>
			<p class="friend-actions">
		  		<small>
_HTML_;

		JWTemplate::sidebar_action($action,$friend_id,false);

		echo <<<_HTML_
  		  		</small>
		</p>

	</td>
</tr>
_HTML_;
	}
}
?>

</table>

<div class="pagination">
<br/>
</div>


		</div><!-- wrapper -->
	</div><!-- content -->
</div><!-- #container -->

<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>

