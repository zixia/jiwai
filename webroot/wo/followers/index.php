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

if ( isset($g_user_friends) && $g_user_friends ) {
	$rows				= JWUser::GetDbRowsByIds(array($g_page_user_id));
	$page_user_info		= $rows[$g_page_user_id];
	$head_options['ui_user_id']		= $g_page_user_id;
} else {
	$page_user_info		= $logined_user_info;
}

$follower_num			= JWFollower::GetFollowerNum	($page_user_info['id']);
$pagination         = new JWPagination($follower_num, $page, 15);
$follower_ids         = JWFollower::GetFollowerIds( $page_user_info['id'], $pagination->GetNumPerPage(), $pagination->GetStartPos() );
$follower_user_rows		= JWUser::GetDbRowsByIds	($follower_ids);

$picture_ids        = JWFunction::GetColArrayFromRows($follower_user_rows, 'idPicture');
$picture_url_row   	= JWPicture::GetUrlRowByIds($picture_ids);

?>

<html>

<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="friends">

<?php JWTemplate::header("/wo/account/settings") ?>
<?php JWTemplate::ShowActionResultTipsMain(); ?>

<div id="container">
<?php JWTemplate::FriendsTab($page_user_info['id'], 'followers' ); ?>

<div class="tabbody" id="myfriend">
    <table width="100%" border="0" cellspacing="1" cellpadding="0" class="tablehead">
    <tr>
        <td width="285"><a href="#">用户名</a></td>
        <td width="60"><a href="#">叽歪数</a></td>
        <!--td width="60"><a href="#">彩信数</a></td-->
        <td><a href="#">最后更新时间</a></td>
    </tr>
    </table>
	
<?php JWTemplate::ListUser($logined_user_info['id'], $follower_ids, array('type'=>'followers')); ?>
</div>

<?php JWTemplate::PaginationLimit( $pagination, $page, null, $limit = 4 ) ; ?>

<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>

