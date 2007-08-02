<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page = ($page < 1 ) ? 1 : $page;

/*
 *	除了显示 /wo/friends/ 之外，还负责显示 /zixia/friends/
 *	如果是其他用户的 friends 页(/zixia/friends)，则 $g_user_friends = true, 并且 $g_page_user_id 是页面用户 id
 *
 */
$logined_user_info 	= JWUser::GetCurrentUserInfo();

$head_options = array();

if ( isset($g_user_friends) && $g_user_friends ) {
	$rows				= JWUser::GetUserDbRowsByIds(array($g_page_user_id));
	$page_user_info		= $rows[$g_page_user_id];
	$head_options['ui_user_id']		= $g_page_user_id;
} else {
	$page_user_info		= $logined_user_info;
}

$friend_num			= JWFriend::GetFriendNum	($page_user_info['id']);
$pagination         = new JWPagination($friend_num, $page);
$friend_ids         = JWFriend::GetFriendIds( $page_user_info['id'], $pagination->GetNumPerPage(), $pagination->GetStartPos() );
$friend_user_rows	= JWUser::GetUserDbRowsByIds	($friend_ids);

/*
$picture_ids        = JWFunction::GetColArrayFromRows($friend_user_rows, 'idPicture');
$picture_url_rows   = JWPicture::GetUrlRowByIds($picture_ids);
*/

?>

<html>

<head>
<?php JWTemplate::html_head($head_options) ?>
</head>


<body class="friends" id="friends">


<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div class="separator"></div>

<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">


<?php 

JWTemplate::ShowActionResultTips();

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

JWTemplate::ListUser($logined_user_info['id'], $friend_ids, array('element_id'=>'friends'));

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

