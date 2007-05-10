<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once(dirname(__FILE__) . '/../../jiwai.inc.php');

JWUser::MustLogined();

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
	$arr_status_list = JWStatus::GetStatusListUser($logined_user_id);
else
	$arr_status_list = JWStatus::GetStatusListFriends($logined_user_id);

JWTemplate::timeline($arr_status_list, array('icon'=>!$show_user_archive,'trash'=>true)) 
?>
  
<?php JWTemplate::pagination() ?>

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


$arr_device_active	= JWDevice::GetDeviceInfo($logined_user_id);

$im_actived 		= ( isset($arr_device_active['im']) 
							&& $arr_device_active['im']['verified']  );
$sms_actived 		= ( isset($arr_device_active['sms']) 
							&& $arr_device_active['sms']['verified'] );


$arr_friend_list	= JWFriend::GetFriend($logined_user_id);


$arr_menu 			= array(	array ('status'			, array($logined_user_info))
								, array ('count'		, array($arr_count_param))
								, array ('jwvia'		, array($logined_user_info))
								, array ('active'		, array($im_actived, $sms_actived))
								, array ('friend'		, array($arr_friend_list))
							);
	
JWTemplate::sidebar( $arr_menu );
?>

</div><!-- #container -->

<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>

