<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();
JWLogin::MustLogined();

$logined_user_info 	= JWUser::GetCurrentUserInfo();
$logined_user_id 	= $logined_user_info['id'];
$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page = ($page < 1 ) ? 1 : $page;

/*
 *	除了显示 /wo/favourites/ 之外，还负责显示 /zixia/favourites/
 *	如果是其他用户的 favourites 页(/zixia/friends)，则 $g_user_favourites = true, 并且 $g_page_user_id 是页面用户 id
 *
 */

$logined_user_info 	= JWUser::GetCurrentUserInfo();

$head_options = array();

if ( isset($g_user_favourites) && $g_user_favourites ) {
	$rows				= JWUser::GetUserDbRowsByIds(array($g_page_user_id));
	$page_user_info		= $rows[$g_page_user_id];
	$head_options['ui_user_id']		= $g_page_user_id;
} else {
	$page_user_info		= $logined_user_info;
}

$status_num		= JWFavourite::GetFavouriteNum($page_user_info['id']);
$pagination		= new JWPagination($status_num, $page);
$status_ids		= JWFavourite::GetFavourite($page_user_info['id'], $pagination->GetNumPerPage(), $pagination->GetStartPos() );

$status_rows	= JWStatus::GetStatusDbRowsByIds($status_ids);

$user_ids		= array_map( create_function('$row','return $row["idUser"];'), $status_rows );
$user_rows		= JWUser::GetUserDbRowsByIds($user_ids);


?>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php JWTemplate::html_head($head_options) ?>
</head>


<body class="normal">


<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>


<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">
<?php 
if ( $page_user_info['id']==$logined_user_info['id'] ) {
	JWTemplate::tab_menu( null, '我标记的更新' );
} else {
	JWTemplate::tab_menu( null, $page_user_info['nameScreen'].'标记的更新' ); 
}
?>
			<div class="tab">

<?php
$n = 0;
if ( isset($status_ids) )
{
	JWTemplate::Timeline($status_ids, $user_rows, $status_rows, array('pagination' => $pagination));
}
?>
			</div><!-- tab -->
		</div><!-- wrapper -->
	</div><!-- content -->
<?php
include_once dirname( dirname(__FILE__) ).'/sidebar.php';
JWTemplate::container_ending();
?>

</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
