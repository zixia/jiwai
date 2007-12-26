<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();
JWLogin::MustLogined();

$logined_user_info 	= JWUser::GetCurrentUserInfo();

if ( isset($g_user_friends) && $g_user_friends ) {
	$rows				= JWDB_Cache_User::GetDbRowsByIds(array($g_page_user_id));
	$page_user_info		= $rows[$g_page_user_id];
	$head_options['ui_user_id']		= $g_page_user_id;
} else {
	$page_user_info		= $logined_user_info;
}

$follower_num			= JWFollower::GetFollowerNum	($page_user_info['id']);

$follower_ids         = JWFollower::GetFollowerIds( $page_user_info['id'] );
$follower_user_rows		= JWDB_Cache_User::GetDbRowsByIds	($follower_ids);

$picture_ids        = JWFunction::GetColArrayFromRows($follower_user_rows, 'idPicture');
$picture_url_row   	= JWPicture::GetUrlRowByIds($picture_ids);

$picture_ids = JWFunction::GetColArrayFromRows($follower_user_rows, 'idPicture');
$picture_url_row = JWPicture::GetUrlRowByIds($picture_ids);

?>


<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
)); ?>
</head>


<body class="normal">

<?php JWTemplate::header("/wo/account/settings");?>
<?php JWTemplate::ShowActionResultTipsMain();?>

<div id="container">
    <div id="wtFollow"><!-- wtFollow start -->

<?php
if ( isset($g_user_friends) && $g_user_friends ) {
    echo '<p class="title">关注'.$page_user_info['nameScreen'] .'的人（'.$follower_num.'）</p>';
}
else
{
    echo '<p class="title">关注你的人（'.$follower_num.'）</p>';
}
?>
        <div class="follow">
	<ul class="followlist">
<?php
foreach ( $follower_ids as $list_user_id )
{
    $list_user_row = $follower_user_rows[$list_user_id];

    $list_user_picture_id = @$list_user_row['idPicture'];

    $list_user_icon_url = JWTemplate::GetConst('UrlStrangerPicture');
    if ( $list_user_picture_id )
        $list_user_icon_url = $picture_url_row[$list_user_picture_id];
?>
<li><a href="/<?php echo $list_user_row['nameUrl']; ?>/" title="<?php echo $list_user_row['nameScreen']; ?>" rel="contact"><img icon="<?php echo $list_user_row['id'];?>" class="buddy_icon" src="<?php echo $list_user_icon_url; ?>" title="<?php echo $list_user_row['nameFull']; ?>" border="0" /><?php echo $list_user_row['nameScreen']; ?></a></li>
<?php
}
?>
	</ul>
        </div>
	<div style="clear: both;"></div>
    </div><!-- wtFollow end -->
    <div id="wtchannelsidebar">
    <div class="sidediv">
	    <form id="f3" action="<?php echo JW_SRVNAME . '/wo/search/users'; ?>">
		    <P class="title">成员搜索</P>
		    <p><input name="q" type="text" class="inputStyle" /></p>
		    <p class="sidediv3"><input name="Submit" type="submit" class="submitbutton" onClick="$('f3').submit();" value="搜索成员" /></p>
	    </form>

	    <div class="line"></div>
	    <P style="padding:5px 0 10px 10px"><a href="/wo/invitations/invite">&#187;&nbsp;邀请你的朋友加入叽歪</a></P>
	    <P style="padding:5px 0 10px 10px "><a href="/wo/followings/">&#187;&nbsp;你关注的人</a></P>
	    <div class="line"></div>
	    <div style="clear:both;"></div>
    </div><!-- sidediv -->
    </div><!-- wtsidebar -->
</div>

<?php JWTemplate::container_ending();?>
</div><!-- #container -->
<?php JWTemplate::footer(); ?>
</body>
</html>

