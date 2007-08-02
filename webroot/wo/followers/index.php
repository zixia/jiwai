<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page = ($page < 1 ) ? 1 : $page;

/*
 *
 */
$logined_user_info 	= JWUser::GetCurrentUserInfo();

$page_user_info		= $logined_user_info;

$follower_num			= JWFollower::GetFollowerNum	($page_user_info['id']);
$pagination         = new JWPagination($follower_num, $page);
$follower_ids         = JWFollower::GetFollowerIds( $page_user_info['id'], $pagination->GetNumPerPage(), $pagination->GetStartPos() );
$follower_user_rows		= JWUser::GetUserDbRowsByIds	($follower_ids);

$picture_ids        = JWFunction::GetColArrayFromRows($follower_user_rows, 'idPicture');
$picture_url_row   	= JWPicture::GetUrlRowByIds($picture_ids);

?>

<html>

<head>
<?php JWTemplate::html_head() ?>
</head>


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

		$follower_picture_id= @$follower_info['idPicture'];

		$follower_icon_url  = JWTemplate::GetConst('UrlStrangerPicture');

		if ( $follower_picture_id )
			$follower_icon_url	= $picture_url_row[$follower_picture_id];

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

<?php
$words = array(
		'first' => '<< 首页',
		'last' => '末页 >>',
		'pre' => '< 上一页',
		'next' => '下一页 >',
	      );
JWTemplate::pagination( $pagination, array(), $words );
?>

		</div><!-- wrapper -->
	</div><!-- content -->
</div><!-- #container -->

<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>

