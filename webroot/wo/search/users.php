<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$q = null;
extract($_GET, EXTR_IF_EXISTS);

$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page = ($page < 1 ) ? 1 : $page;

$logined_user_info = JWUser::GetCurrentUserInfo();

$head_options = array();

if ( isset($g_user_friends) && $g_user_friends ) {
	$rows				= JWUser::GetUserDbRowsByIds(array($g_page_user_id));
	$page_user_info		= $rows[$g_page_user_id];
	$head_options['ui_user_id']		= $g_page_user_id;
} else {
	$page_user_info		= $logined_user_info;
}

$searched_ids		= JWSearch::GetSearchUserIds($q);
$searched_num		= count( $searched_ids );
$pagination         = new JWPagination($searched_num, $page, 15);

$searched_ids       = @array_slice( $searched_ids, ($page-1)*15, 15 );
$searched_user_rows	= JWUser::GetUserDbRowsByIds($searched_ids);

/*
$picture_ids        = JWFunction::GetColArrayFromRows($searched_user_rows, 'idPicture');
$picture_url_rows   = JWPicture::GetUrlRowByIds($picture_ids);
*/

?>

<html>

<head>
<?php JWTemplate::html_head($head_options) ?>
</head>

<body class="account" id="friends">
<?php JWTemplate::header("/wo/account/settings") ?>
<?php JWTemplate::ShowActionResultTipsMain(); ?>

<div id="container">
<h2 style="margin-bottom:10px;">找用户 - <form style="display:inline;margin:0px;padding:0;" action="/wo/search/users" method="GET" id="search_user"><input type="text" style="height:18px;padding-left:5px;" name="q" value="<?php echo (isset($q)) ? $q : '用户名、Email';?>" onclick='this.value=""' /><input type="button" style="height:24px; padding:2px 5px;" onClick='$("search_user").submit();' value="找"/></form></h2>
<?php JWTemplate::FriendsTab( $page_user_info['id'], 'search' ); ?>
<div class="tabbody" id="myfriend">

    <table width="100%" border="0" cellspacing="1" cellpadding="0" class="tablehead">
    <tr>
        <td width="285"><a href="#">用户名</a></td>
        <td width="60"><a href="#">消息数</a></td>
        <td width="60"><a href="#">彩信数</a></td>
        <td><a href="#">最后更新时间</a></td>
    </tr>
    </table>


<?php JWTemplate::ListUser($logined_user_info['id'], $searched_ids, array('type'=>'search')); ?>
</div>

<?php JWTemplate::PaginationLimit( $pagination, $page, null, $limit = 4 ) ; ?>

<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
