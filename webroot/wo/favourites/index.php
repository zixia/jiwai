<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

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

$status_ids		= JWFavourite::GetFavourite($page_user_info['id']);
$status_num		= JWFavourite::GetFavouriteNum($page_user_info['id']);

$status_rows	= JWStatus::GetStatusDbRowsByIds($status_ids);

$user_ids		= array_map( create_function('$row','return $row["idUser"];'), $status_rows );
$user_rows		= JWUser::GetUserDbRowsByIds($user_ids);

?>

<html>

<head>
<?php JWTemplate::html_head($head_options) ?>
</head>


<body class="favourings" id="favourings">


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
			<h2> 我的 $status_num 份收藏。 </h2>
_HTML_;
} 
else 
{
	echo <<<_HTML_
			<h2> $page_user_info[nameScreen] 的 $status_num 份收藏。</h2>
_HTML_;
	
}
?>

<p>我们将更新旁边的星标点亮后，它们就会被存在这里啦！</p>


<?php
$n = 0;
if ( isset($status_ids) )
{
	JWTemplate::Timeline($status_ids, $user_rows, $status_rows);
}
?>


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

