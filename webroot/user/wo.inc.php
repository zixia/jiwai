<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
//$debug = JWDebug::instance();
//$debug->init();

$logined_user_info	= JWUser::GetCurrentUserInfo();
$page_user_info 	= JWUser::GetUserInfoById($page_user_id);

//die( var_dump($page_user_info));
//die( var_dump($logined_user_info));
?>
<html>

<?php JWTemplate::html_head() ?>

<body class="normal">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div class="separator"></div>

<div id="container">
	<!-- div id="flaginfo">zixia</div -->
<!-- google_ad_section_start -->
	<div id="content">
		<div id="wrapper">

<?php
if ( empty($error_html) )
	$error_html	= JWSession::GetInfo('error');

if ( empty($notice_html) )
	$notice_html	= JWSession::GetInfo('notice');


if ( !empty($error_html) )
{
		echo <<<_HTML_
			<div class="notice">$error_html</div>
_HTML_;
}

if ( !empty($notice_html) )
{
		echo <<<_HTML_
			<div class="notice">$notice_html</div>
_HTML_;
}

$status_data 	= JWStatus::GetStatusIdFromUser($page_user_id, JWStatus::DEFAULT_STATUS_NUM+1);
$status_rows	= JWStatus::GetStatusRowById($status_data['status_ids']);
$user_rows		= JWUser::GetUserRowById	($status_data['user_ids']);

// 取出一个
$head_status_id = @array_shift($status_data['status_ids']); 

JWTemplate::StatusHead($page_user_id, $user_rows[$page_user_id], $status_rows[$head_status_id] );

?>

<?php JWTemplate::tab_menu() ?>

			<div class="tab">

<?php JWTemplate::tab_header( array() ) ?>

<?php JWTemplate::Timeline($status_data['status_ids'], $user_rows, $status_rows, array('icon'=>false)) ?>
  
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

$arr_action_param	= array ();

$arr_action_param	= JWSns::GetUserAction($logined_user_info['id'],$page_user_info['id']);


$arr_friend_list	= JWFriend::GetFriend($page_user_info['id']);
$arr_count_param	= JWSns::GetUserState($page_user_info['id']);

$arr_menu 			= array(	array ('user_notice'	, array($page_user_info))
								, array ('user_info'	, array($page_user_info))
								, array ('count'		, array($arr_count_param,$page_user_info['nameScreen']))
								, array ('action'	, array($arr_action_param,$page_user_info['id']))
								, array ('friend'	, array($arr_friend_list))
							);

if ( ! JWLogin::IsLogined() )
	array_push ( $arr_menu, 
					array('register', null)
				);


JWTemplate::sidebar( $arr_menu, $page_user_id);
?>

</div><!-- #container -->

<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
