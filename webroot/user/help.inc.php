<?php
JWTemplate::html_doctype();

JWLogin::MustLogined();

$logined_user_info 	= JWUser::GetCurrentUserInfo();
$logined_user_id 	= $logined_user_info['id'];

$help_user_info	= JWUser::GetUserInfo('help');
$help_user_id	= $help_user_info['idUser'];
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


<?php 
$options = array ( 'default_text' => '@help ' );
JWTemplate::updater($options) ;
?>

  			<!-- p class="notice">
  			</p-->


<?php 
$menu_list = array (
		'archive_n_replies'	=> array('active'=>true	,'name'=>'叽歪de留言板'	,'url'=>"/help/")
	);

JWTemplate::tab_menu($menu_list) 
?>

			<div class="tab">

<?php 

JWTemplate::tab_header( array('title'=>'这一刻，大家都想告诉叽歪de什么呢？') ) 
?>

<?php 
// when show archive, we set $show_archive=true, then include this file.

//die(var_dump($_REQUEST));

// 显示自己和回复自己的

$user_status_num= JWStatus::GetStatusNumFromSelfNReplies($help_user_id);
$pagination		= new JWPagination($user_status_num, @$_REQUEST['page']);
$status_data 	= JWStatus::GetStatusIdsFromSelfNReplies($help_user_id, $pagination->GetNumPerPage(), $pagination->GetStartPos() );


$status_rows	= JWStatus::GetStatusDbRowsByIds($status_data['status_ids']);
$user_rows		= JWUser::GetUserDbRowsByIds	($status_data['user_ids']);

JWTemplate::Timeline($status_data['status_ids'], $user_rows, $status_rows);
  
JWTemplate::pagination($pagination);

?>

<?php JWTemplate::rss('user',$help_user_id) ?>
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
$arr_count_param	= JWSns::GetUserState($help_user_id);


$device_row			= JWDevice::GetDeviceRowByUserId($help_user_id);

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


$arr_friend_list	= JWFriend::GetFriendIds($help_user_id);

$arr_menu 			= array(	array ('status'			, array($help_user_info))
								, array ('user_info'	, array($help_user_info))
								, array ('count'		, array($arr_count_param))
								, array ('friend'		, array($arr_friend_list))
							);
	
JWTemplate::sidebar( $arr_menu );
?>

</div><!-- #container -->

<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>

