<?php
require_once(dirname(__FILE__) . '/../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$logined_user_info 	= JWUser::GetCurrentUserInfo();
$logined_user_id 	= $logined_user_info['id'];

?>

<html>

<?php JWTemplate::html_head() ?>

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
if ( !isset($g_show_user_archive) )
	$g_show_user_archive = false;;

$menu_list = array (
		 '历史'	=> array('active'=>false	,'url'=>"/wo/account/archive")
		,'最新'	=> array('active'=>false	,'url'=>"/wo/")
	);

if ( $g_show_user_archive )
	$menu_list['历史']['active'] = true;
else
	$menu_list['最新']['active'] = true;

JWTemplate::tab_menu($menu_list) 
?>

			<div class="tab">

<?php 

JWTemplate::tab_header( array() ) 
?>

<?php 
// when show archive, we set $show_archive=true, then include this file.

//die(var_dump($_REQUEST));

if ( $g_show_user_archive )
{
	// 只显示用户自己的
	$user_status_num= JWStatus::GetStatusNum($logined_user_id);
	$pagination		= new JWPagination($user_status_num, @$_REQUEST['page']);
	$status_data 	= JWStatus::GetStatusIdsFromUser($logined_user_id, $pagination->GetNumPerPage(), $pagination->GetStartPos() );
}
else
{
	// 显示用户和好友的
	$user_status_num= JWStatus::GetStatusNumFromFriends($logined_user_id);
	$pagination		= new JWPagination($user_status_num, @$_REQUEST['page']);
	$status_data 	= JWStatus::GetStatusIdsFromFriends($logined_user_id,$pagination->GetNumPerPage(), $pagination->GetStartPos() );
}

$status_rows	= JWStatus::GetStatusDbRowsByIds($status_data['status_ids']);
$user_rows		= JWUser::GetUserDbRowsByIds	($status_data['user_ids']);

JWTemplate::Timeline($status_data['status_ids'], $user_rows, $status_rows);
  
JWTemplate::pagination($pagination);

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

$arr_menu 			= array(	array ('status'			, array($logined_user_info))
								, array ('friend_req'	, array($friend_request_num))
								, array ('count'		, array($arr_count_param))
								, array ('jwvia'		, array($active_options, $via_device))
								, array ('friend'		, array($arr_friend_list))
							);
	
JWTemplate::sidebar( $arr_menu );
?>

</div><!-- #container -->

<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>

