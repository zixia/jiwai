<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
//$debug = JWDebug::instance();
//$debug->init();

/*
 *	可能会接收到从 index.php 过来的全局变量 
		$g_user_with_friends 
		$g_user_default
		$g_page_user_id
 */

$page_user_id		= $g_page_user_id;

$logined_user_info	= JWUser::GetCurrentUserInfo();
$page_user_info 	= JWUser::GetUserInfo($page_user_id);

//die(var_dump($_REQUEST));
//die( var_dump($page_user_id));
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
JWTemplate::ShowActionResultTips();


if ( $g_user_with_friends )
{
	// 显示用户和好友的
	$user_status_num= JWStatus::GetStatusNumFromFriends($page_user_id);

	$pagination		= new JWPagination($user_status_num, @$_REQUEST['page']);

	$status_data 	= JWStatus::GetStatusIdsFromFriends( $page_user_id, $pagination->GetNumPerPage(), $pagination->GetStartPos() );
}
else
{
	// 显示用户自己的
	$user_status_num= JWStatus::GetStatusNum($page_user_id);

	$pagination		= new JWPagination($user_status_num, @$_REQUEST['page']);

	$status_data 	= JWStatus::GetStatusIdsFromUser( $page_user_id, $pagination->GetNumPerPage(), $pagination->GetStartPos() );
}



$status_rows	= JWStatus::GetStatusDbRowsByIds($status_data['status_ids']);
$user_rows		= JWUser::GetUserDbRowsByIds	($status_data['user_ids']);

$head_status_data 	= JWStatus::GetStatusIdsFromUser( $page_user_id, 1 );
$head_status_rows 	= JWStatus::GetStatusDbRowsByIds($head_status_data['status_ids']);
$head_status_id 	= @array_shift($head_status_data['status_ids']); 

// 取出一个

//die(var_dump($page_user_id));
JWTemplate::StatusHead($page_user_id, $user_rows[$page_user_id], @$head_status_rows[$head_status_id] );

?>

<?php 
$menu_list = array (
		 '和朋友们'	=> array('active'=>false	,'url'=>"/$page_user_info[nameScreen]/with_friends")
		,'以前的'	=> array('active'=>false	,'url'=>"/$page_user_info[nameScreen]/")
	);

if ( $g_user_with_friends )
	$menu_list['和朋友们']['active'] = true;
else
	$menu_list['以前的']['active'] = true;


JWTemplate::tab_menu($menu_list) 
?>

			<div class="tab">

<?php JWTemplate::tab_header( array() ) ?>

<?php 
if ( !isset($g_user_with_friends) )
	$g_user_with_friends = false;

JWTemplate::Timeline($status_data['status_ids'], $user_rows, $status_rows, array('icon'=>$g_user_with_friends)) 
?>
  
<?php JWTemplate::pagination($pagination) ?>

<?php JWTemplate::rss( $g_user_with_friends ? 'friends' : 'user'
						,$page_user_id) ?>

			</div><!-- tab -->

		</div><!-- wrapper -->
	</div><!-- content -->

<?php 


//$arr_action_param	= JWSns::GetUserAction($logined_user_info['id'],$page_user_info['id']);

$user_action_rows	= JWSns::GetUserActions($logined_user_info['id'] , array($page_user_info['id']) );

if ( empty($user_action_rows) )
	$user_action_row	= array();
else
	$user_action_row	= $user_action_rows[$page_user_info['id']];


$arr_friend_list	= JWFriend::GetFriendIds($page_user_info['id']);
$arr_count_param	= JWSns::GetUserState($page_user_info['id']);

$arr_menu 			= array(	array ('user_notice'	, array($page_user_info))
								, array ('user_info'	, array($page_user_info))
								, array ('count'		, array($arr_count_param,$page_user_info['nameScreen']))
								, array ('action'	, array($user_action_row,$page_user_info['id']))
								, array ('friend'	, array($arr_friend_list))
							);

if ( ! JWLogin::IsLogined() )
	array_push ( $arr_menu, 
					array('register', array(true) )
				);


JWTemplate::sidebar( $arr_menu, $page_user_id);
?>

</div><!-- #container -->

<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
