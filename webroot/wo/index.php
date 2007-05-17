<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once(dirname(__FILE__) . '/../../jiwai.inc.php');

JWLogin::MustLogined();

$logined_user_info 	= JWUser::GetCurrentUserInfo();
$logined_user_id 	= $logined_user_info['id'];


$debug = JWDebug::instance();
$debug->init();
?>

<html>

<?php JWTemplate::html_head() ?>

<body class="normal">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div class="separator"></div>

<div id="container">
<?php 
$now_str = strftime("%Y/%m/%d") ;
echo <<<_HTML_
	<div id="flaginfo">$now_str</div>
_HTML_;
?>
<!-- google_ad_section_start -->
	<div id="content">
		<div id="wrapper">

<?php JWTemplate::ShowActionResultTips() ?>


<?php JWTemplate::updater() ?>

  			<!-- p class="notice">
  				IM is down at the moment.  We're working on restoring it.  Thanks for your patience!
  			</p-->


<?php JWTemplate::tab_menu() ?>

			<div class="tab">

<?php JWTemplate::tab_header( array() ) ?>

<?php 
// when show archive, we set $show_archive=true, then include this file.
if ( !isset($show_user_archive) )
	$show_user_archive = false;;

if ( $show_user_archive )
	$status_data = JWStatus::GetStatusIdsFromUser($logined_user_id);
else
	$status_data = JWStatus::GetStatusIdsFromFriends($logined_user_id);

$status_rows	= JWStatus::GetStatusRowsByIds($status_data['status_ids']);
$user_rows		= JWUser::GetUserRowsByIds	($status_data['user_ids']);

JWTemplate::Timeline($status_data['status_ids'], $user_rows, $status_rows);
  
JWTemplate::pagination() 
?>

<?php JWTemplate::rss() ?>
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


$arr_device_active	= JWDevice::GetDeviceRowByUserId($logined_user_id);

$active_options = array();

if ( isset($arr_device_active['im']) 
							&& $arr_device_active['im']['verified']  )
{
	$active_options['im']	= true;
}

if ( isset($arr_device_active['sms']) 
							&& $arr_device_active['sms']['verified'] )
{
	$active_options['sms']	= true;
}

$arr_friend_list	= JWFriend::GetFriendIds($logined_user_id);

$via_device			= JWUser::GetSendViaDevice($logined_user_id);

$arr_menu 			= array(	array ('status'			, array($logined_user_info))
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

