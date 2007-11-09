<?php
require_once(dirname(__FILE__) . '/../../jiwai.inc.php');
JWTemplate::html_doctype();
JWLogin::MustLogined();

$q = $page = null;
extract($_GET, EXTR_IF_EXISTS);
$logined_user_info 	= JWUser::GetCurrentUserInfo();
$logined_user_id 	= $logined_user_info['id'];
$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page = ($page < 1 ) ? 1 : $page;

?>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php 
$options = array ('ui_user_id' => $logined_user_id );
JWTemplate::html_head($options);
?>
</head>


<body class="normal">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div id="container">
<?php 
/*
$now_str = strftime("%Y/%m/%d") ;
echo <<<_HTML_
	<div id="flaginfo">$now_str</div>
_HTML_;
*/
?>
<!-- google_ad_section_start -->
	<div id="content">
		<div id="wrapper">

<?php JWTemplate::ShowBalloon($logined_user_id) ?>
<?php JWTemplate::ShowAlphaBetaTips() ?>
<?php JWTemplate::ShowActionResultTips() ?>


<?php
     $options = array('sendtips' => 'true');
     JWTemplate::updater($options); 
?>

<?php JWTemplate::ShowActionResultTips(); ?>

<?php 
$active_tab = 'friends';

if ( isset($g_show_user_archive) && $g_show_user_archive)
	$active_tab = 'archive';

if ( isset($g_replies) && $g_replies )
	$active_tab = 'replies';

if ( (isset($g_search) && $g_search) || $q )
	$active_tab = 'search';


$menu_list = array (
	'archive'=> array('active'=>false, 'name'=>'历史', 'url'=>"/wo/archive/"),
	'replies'=> array('active'=>false, 'name'=>'回复', 'url'=>"/wo/replies/"),
	'friends'=> array('active'=>false, 'name'=>'最新', 'url'=>"/wo/"),
);
if( false == empty($q) )
	$menu_list['search'] = array('active'=>false, 'name'=>'搜索结果', 'url'=>"/wo/search/statuses?q=".urlEncode($q));

$menu_list[$active_tab]['active'] = true;

JWTemplate::tab_menu($menu_list, '你和你的朋友们在做什么？');

?>
<div class="tab">
<?php 
// when show archive, we set $show_archive=true, then include this file.

//die(var_dump($_REQUEST));

switch ( $active_tab )
{
	case 'archive':
		// 只显示用户自己的
		//$user_status_num= JWStatus::GetStatusNum($logined_user_id);
		$user_status_num= JWDB_Cache_Status::GetStatusNum($logined_user_id);

		$pagination		= new JWPagination($user_status_num, $page);

		//$status_data 	= JWStatus::GetStatusIdsFromUser($logined_user_id, $pagination->GetNumPerPage(), $pagination->GetStartPos() );
		$status_data 	= JWDB_Cache_Status::GetStatusIdsFromUser($logined_user_id, $pagination->GetNumPerPage(), $pagination->GetStartPos() );

		break;
	case 'replies':
		// 显示回复自己的
		//$user_status_num= JWStatus::GetStatusNumFromReplies($logined_user_id);
		$user_status_num= JWDB_Cache_Status::GetStatusNumFromReplies($logined_user_id);

		$pagination		= new JWPagination($user_status_num, $page);
		//$status_data 	= JWStatus::GetStatusIdsFromReplies($logined_user_id, $pagination->GetNumPerPage(), $pagination->GetStartPos() );
		$status_data 	= JWDB_Cache_Status::GetStatusIdsFromReplies($logined_user_id, $pagination->GetNumPerPage(), $pagination->GetStartPos() );

		break;
	case 'search':
		//搜索所有用户的Status更新
		$searchStatus = new JWSearchStatus();
		$searchStatus->setPageNo( $page );

		$searchStatus->setInSite("jiwai.de/$in_user/statuses/");
		$searchStatus->execute($eq);

		$user_status_num = $searchStatus->getTotalSize();
		$pagination	= new JWPagination($user_status_num, $page);

		$user_ids = $searchStatus->getUserIds();
		if( !empty($user_ids) )
			$user_ids = JWUser::GetUserIdsByNameScreens( $user_ids );
		$status_ids = $searchStatus->getStatusIds();

		$status_data = array('user_ids'=>$user_ids, 'status_ids'=>$status_ids);
		break;

	default:
	case 'friends':
		// 显示用户和好友的
		//$user_status_num= JWStatus::GetStatusNumFromFriends($logined_user_id);
		$user_status_num= JWDB_Cache_Status::GetStatusNumFromFriends($logined_user_id);

		$pagination		= new JWPagination($user_status_num, $page);

		//$status_data 	= JWStatus::GetStatusIdsFromFriends($logined_user_id,$pagination->GetNumPerPage(), $pagination->GetStartPos() );
		$status_data 	= JWDB_Cache_Status::GetStatusIdsFromFriends($logined_user_id,$pagination->GetNumPerPage(), $pagination->GetStartPos() );
		break;

}

//die(var_dump($status_data['status_ids']));

//$status_rows	= JWStatus::GetStatusDbRowsByIds($status_data['status_ids']);
$status_rows	= JWDB_Cache_Status::GetDbRowsByIds($status_data['status_ids']);


if( ( $active_tab == 'friends' || $active_tab == 'archive' ) 
	&& !empty($status_rows) 
	&& $page == 1
	) {
	$mergedStatusResult = JWQuarantineQueue::GetQuarantineStatusFromUser( $logined_user_id, $status_data['status_ids'], $status_rows);
	if( !empty( $mergedStatusResult ) ) {
		$status_data['status_ids'] = $mergedStatusResult['status_ids'];
		$status_rows = $mergedStatusResult['status_rows'];
	}
}

$user_rows		= JWUser::GetUserDbRowsByIds	($status_data['user_ids']);

JWTemplate::Timeline($status_data['status_ids'], $user_rows, $status_rows, array(
	'search' => true,
	//'pagination' => ($active_tab!='friends' ? $pagination : false),
	'pagination' => $pagination,
));
?>

			</div><!-- tab -->
		</div><!-- wrapper -->
	</div><!-- content -->

<?php
include_once ( dirname(__FILE__) . '/sidebar.php' );
JWTemplate::container_ending();
?>
</div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
