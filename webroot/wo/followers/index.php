<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');

JWLogin::MustLogined();

/*
 *
 */
$logined_user_info 	= JWUser::GetCurrentUserInfo();

$page_user_info		= $logined_user_info;

$follower_ids			= JWFollower::GetFollower	($page_user_info['id']);
$follower_user_rows		= JWUser::GetUserRowById	($follower_ids);
$follower_icon_url_rows = JWPicture::GetUserIconUrlRowById($follower_ids);

$follower_num			= JWFollower::GetFollowerNum	($page_user_info['id']);

?>

<html>

<?php JWTemplate::html_head() ?>

<body class="followers" id="followers">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div class="separator"></div>

<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">

<?php
JWTemplate::ShowActionResultTips();
?>
			<h2>我的 <?php echo $follower_num?> 位粉丝</h2>


<?php if ( !empty($follower_ids) ) { ?>
 			<p>
    			<a href="/wo/followers/befriend_all" onclick="return confirm('清确认： 这样可能使你一下多出很多很多的好友！');">将所有粉丝加为好友？</a>
  			</p>
<?php } ?>



	
<table class="doing" cellspacing="0">

<?php
$n = 0;
if ( isset($follower_ids) )
{
	foreach ( $follower_ids as $follower_id )
	{
		$follower_info		= $follower_user_rows[$follower_id];
		$follower_icon_url	= $follower_icon_url_rows[$follower_id];

		$odd_even			= ($n++ % 2) ? 'odd' : 'even';

		echo <<<_HTML_
	<tr class="$odd_even vcard">
		<td class="thumb">
			<a href="http://jiwai.de/$follower_info[nameScreen]/"><img alt="$follower_info[nameFull]" class="photo" src="$follower_icon_url" /></a>
		</td>
		<td>
			<strong>
		  		<a href="http://jiwai.de/$follower_info[nameScreen]/" class="url"><span class="fn">$follower_info[nameFull]</span> (<span class="uid">$follower_info[nameScreen]</span>)</a>
			</strong>
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

