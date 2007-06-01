<?php 
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$logined_user_info 	= JWUser::GetCurrentUserInfo();
$logined_user_id 	= $logined_user_info['id'];


$friend_ids			= JWFriendRequest::GetUserIds($logined_user_info['id'], 20);

if ( empty($friend_ids) )
{
	header ( "Location: /wo/" );
	exit(0);
}
?>

<html>

<?php JWTemplate::html_head() ?>

<body class="friend_requests" id="friend_requests">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div class="separator"></div>

<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">


			<h2>新增好友请求</h2>

<?php
JWTemplate::ShowActionResultTips();

?>

<table id="timeline" class="doing" cellspacing="0">

<?php

$friend_db_rows		= JWUser::GetUserDbRowsByIds($friend_ids);
$friend_icon_url_rows	= JWPicture::GetUserIconUrlRowsByIds($friend_ids);
$n = 0;

foreach ( $friend_ids as $friend_id )
{
	$friend_db_row		= $friend_db_rows[$friend_id];
	$friend_icon_url	= $friend_icon_url_rows[$friend_id];

	$odd_even			= ($n++ % 2) ? 'odd' : 'even';

	echo <<<_HTML_
	<tr class="$odd_even">

	<td class="thumb">
		<a href="/$friend_db_row[nameScreen]/"><img alt="$friend_db_row[nameFull]" src="$friend_icon_url" /></a>
	</td>
	<td>
		<a href="/$friend_db_row[nameScreen]/">$friend_db_row[nameFull]</a>
		
		<p class="friend-request">
			&rarr;
			<a href="/wo/friend_requests/accept/$friend_id">接受</a> |
			<a href="/wo/friend_requests/deny/$friend_id">拒绝</a>

		</p>
	</td>

	</tr>
_HTML_;
}
?>
</table>


		</div><!-- wrapper -->
	</div><!-- content -->
</div><!-- #container -->
			
<hr class="separator" />
	
<?php JWTemplate::footer() ?>

</body>
</html>

