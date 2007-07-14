<?php
require_once(dirname(__FILE__) . '/../../jiwai.inc.php');
JWTemplate::html_doctype();
JWLogin::MustLogined();

$logined_user_info 	= JWUser::GetCurrentUserInfo();
$logined_user_id 	= $logined_user_info['id'];

?>

<html>

<head>
<?php 
$options = array (	'ui_user_id'	=> $logined_user_id );
JWTemplate::html_head($options);
?>
</head>


<body class="normal">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div class="separator"></div>

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


<?php JWTemplate::ShowAlphaBetaTips() ?>
<?php JWTemplate::ShowActionResultTips() ?>


<?php JWTemplate::updater() ?>

  			<!-- p class="notice">
  				IM is down at the moment.  We're working on restoring it.  Thanks for your patience!
  			</p-->


<?php 
$active_tab = 'friends';

if ( isset($g_show_user_archive) && $g_show_user_archive)
	$active_tab = 'archive';

if ( isset($g_replies) && $g_replies )
	$active_tab = 'replies';

if ( isset($g_search) && $g_search )
	$active_tab = 'search';


$menu_list = array (
		 'archive'=> array('active'=>false	,'name'=>'历史'	,'url'=>"/wo/account/archive")
		,'replies'=> array('active'=>false	,'name'=>'回复'	,'url'=>"/wo/replies/")
		,'friends'=> array('active'=>false	,'name'=>'最新'	,'url'=>"/wo/")
	);

if( !empty($q) )
		$menu_list['search'] = array('active'=>false	,'name'=>'搜索结果'	,'url'=>"/wo/search/statuses?q=".urlEncode($q));

$menu_list[$active_tab]['active'] = true;

JWTemplate::tab_menu($menu_list) 
?>

			<div class="tab">

<?php 

JWTemplate::tab_header( array() ) 
?>

<?php 
// when show archive, we set $show_archive=true, then include this file.

//die(var_dump($_REQUEST));

switch ( $active_tab )
{
	case 'archive':
		// 只显示用户自己的
		//$user_status_num= JWStatus::GetStatusNum($logined_user_id);
		$user_status_num= JWDB_Cache_Status::GetStatusNum($logined_user_id);

		$pagination		= new JWPagination($user_status_num, @$_REQUEST['page']);

		//$status_data 	= JWStatus::GetStatusIdsFromUser($logined_user_id, $pagination->GetNumPerPage(), $pagination->GetStartPos() );
		$status_data 	= JWDB_Cache_Status::GetStatusIdsFromUser($logined_user_id, $pagination->GetNumPerPage(), $pagination->GetStartPos() );

		break;
	case 'replies':
		// 显示回复自己的
		//$user_status_num= JWStatus::GetStatusNumFromReplies($logined_user_id);
		$user_status_num= JWDB_Cache_Status::GetStatusNumFromReplies($logined_user_id);

		$pagination		= new JWPagination($user_status_num, @$_REQUEST['page']);
		//$status_data 	= JWStatus::GetStatusIdsFromReplies($logined_user_id, $pagination->GetNumPerPage(), $pagination->GetStartPos() );
		$status_data 	= JWDB_Cache_Status::GetStatusIdsFromReplies($logined_user_id, $pagination->GetNumPerPage(), $pagination->GetStartPos() );

		break;
	case 'search':
		//搜索所有用户的Status更新
		$searchStatus = new JWSearchStatus();

		$p = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
		$searchStatus->setPageNo( $p );

		$searchStatus->setInSite("jiwai.de/$in_user/statuses/");
		$searchStatus->execute($eq);

		$user_status_num = $searchStatus->getTotalSize();
		$pagination	= new JWPagination($user_status_num, @$_REQUEST['page']);

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

		$pagination		= new JWPagination($user_status_num, @$_REQUEST['page']);

		//$status_data 	= JWStatus::GetStatusIdsFromFriends($logined_user_id,$pagination->GetNumPerPage(), $pagination->GetStartPos() );
		$status_data 	= JWDB_Cache_Status::GetStatusIdsFromFriends($logined_user_id,$pagination->GetNumPerPage(), $pagination->GetStartPos() );
		break;

}

//$status_rows	= JWStatus::GetStatusDbRowsByIds($status_data['status_ids']);
$status_rows	= JWDB_Cache_Status::GetDbRowsByIds($status_data['status_ids']);

$user_rows		= JWUser::GetUserDbRowsByIds	($status_data['user_ids']);

JWTemplate::Timeline($status_data['status_ids'], $user_rows, $status_rows);

JWTemplate::pagination($pagination, empty($q) ? array() : array('q'=>$q) );

?>

<?php JWTemplate::rss('user',$logined_user_id) ?>
			</div><!-- tab -->

  			<script type="text/javascript">
//<![CDATA[  
/*new PeriodicalExecuter(function() { new Ajax.Request('/account/refresh?last_check=' + $('timeline').getElementsByTagName('tr')[0].id.split("_")[1], 
    {
      asynchronous:true, 
      evalScripts:true,
      onLoading: function(request) { Effect.Appear('timeline_refresh', {duration:0.3 }); },
      onComplete: function(request) { Element.hide('timeline_refresh'); }
    })}, 120);
*/
  //]]>
			</script>

		</div><!-- wrapper -->
	</div><!-- content -->

<?php 
$arr_count_param	= JWSns::GetUserState($logined_user_id);


$device_row			= JWDevice::GetDeviceRowByUserId($logined_user_id);

$active_options = array();

$supported_device_types = JWDevice::GetSupportedDeviceTypes();

foreach ( $supported_device_types as $type )
{
	if ( isset($device_row[$type]) 
				&& $device_row[$type]['verified']  )
	{	
		$active_options[$type]	= true;
	}
	else
	{
		$active_options[$type] 	= false;
	}
}


$arr_friend_list	= JWFriend::GetFriendIds($logined_user_id);

$via_device			= JWUser::GetSendViaDevice($logined_user_id);

$friend_request_num	= JWFriendRequest::GetUserNum($logined_user_id);

$arr_menu 			= array(
					array ('status'	, array($logined_user_info)) , 
					array ('friend_req'	, array($friend_request_num)) , 
					array ('count'		, array($arr_count_param)) , 
					array ('jwvia'		, array($active_options, $via_device)) ,
					array ('search'		, array(null, isset($q) ? $q : null)) ,
				       	array ('friend'		, array($arr_friend_list)) , 
				);
	
JWTemplate::sidebar( $arr_menu );
?>

</div><!-- #container -->

<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>

