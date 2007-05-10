<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');

JWUser::MustLogined();

$logined_user_info 	= JWUser::GetCurrentUserInfo();


$friend_ids			= JWFriend::GetFriend($logined_user_info['id']);
$friend_num			= count($friend_ids);
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


			<h2> 您有<?php echo $friend_num?>位好友。
		  		<a href="/wo/invitations/invite">邀请更多！</a>
			</h2>

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
foreach ( $friend_ids as $friend_id )
{
	$friend_info		= JWUser::GetUserInfoById($friend_id);
	$friend_icon_url	= JWPicture::GetUserIconUrl($friend_id);

	$action				= JWSns::GetUserAction($logined_user_info['id'],$friend_id);

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

