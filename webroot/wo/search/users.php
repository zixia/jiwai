<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$q = null;
extract($_GET, EXTR_IF_EXISTS);

$logined_user_info 	= JWUser::GetCurrentUserInfo();

$head_options = array();

if ( isset($g_user_friends) && $g_user_friends ) {
	$rows				= JWUser::GetUserDbRowsByIds(array($g_page_user_id));
	$page_user_info		= $rows[$g_page_user_id];
	$head_options['ui_user_id']		= $g_page_user_id;
} else {
	$page_user_info		= $logined_user_info;
}

$searched_ids		= JWSearch::GetSearchUserIds($q);
$searched_user_rows	= JWUser::GetUserDbRowsByIds($searched_ids);

/*
$picture_ids        = JWFunction::GetColArrayFromRows($searched_user_rows, 'idPicture');
$picture_url_rows   = JWPicture::GetUrlRowByIds($picture_ids);
*/

$searched_num		= count( $searched_ids );
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
	if( $searched_num < 100 ) 
		$tipstring = "共搜索到 $searched_num 位JiWai用户。";
	else
		$tipstring = "显示符合条件的前 100 位JiWai用户。";
	echo <<<_HTML_
			<h2>  $tipstring
		  		<a href="/wo/invitations/invite">邀请更多！</a>
			</h2>
_HTML_;
} 
else 
{
	echo <<<_HTML_
			<h2> $page_user_info[nameScreen] 的 $searched_num 位好友。</h2>
_HTML_;
	
}

JWTemplate::ListUser($logined_user_info['id'], $searched_ids, array('element_id'=>'friends'));
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
