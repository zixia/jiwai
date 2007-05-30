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


$show_protected_content = true;

if ( $logined_user_info['idUser']!=$page_user_id 
		&& JWUser::IsProtected($page_user_id) )
{
	if ( empty($logined_user_info) )
		$show_protected_content= false;
	else if ( ! JWFriend::IsFriend($page_user_id, $logined_user_info['idUser']) )
		$show_protected_content= false;
}


//die(var_dump($_REQUEST));
//die( var_dump($page_user_id));
//die( var_dump($logined_user_info));
?>
<html>

<?php 

if ( !isset($g_user_with_friends) )
	$g_user_with_friends = false;


/*
 *	使用 JWPagination 时，要注意用户在最上面已经显示了一条了，所以总数应该减一
 *
 */
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

	$pagination		= new JWPagination($user_status_num-1, @$_REQUEST['page']);

	$status_data 	= JWStatus::GetStatusIdsFromUser( $page_user_id, $pagination->GetNumPerPage(), $pagination->GetStartPos()+1 );
}



$status_rows	= JWStatus::GetStatusDbRowsByIds( $status_data['status_ids']);

array_push($status_data['user_ids'],$page_user_id);
$user_rows		= JWUser::GetUserDbRowsByIds	($status_data['user_ids']);

$head_status_data 	= JWStatus::GetStatusIdsFromUser( $page_user_id, 1 );
$head_status_rows 	= JWStatus::GetStatusDbRowsByIds($head_status_data['status_ids']);
$head_status_id 	= @array_shift($head_status_data['status_ids']); 


/*
 *	设置 html header
 */
$keywords 		= <<<_STR_
$page_user_info[nameScreen]($page_user_info[nameFull]) - $page_user_info[bio] $page_user_info[location] 
_STR_;

$description = "叽歪de$page_user_info[nameFull] ";
$description .= @$head_status_rows[$head_status_id]['status'];

foreach ( $status_data['status_ids'] as $status_id )
{
	$description .= $status_rows[$status_id]['status'];
	if ( mb_strlen($description,'UTF-8') > 140 )
	{
			$description = mb_substr($description,0,140,'UTF-8');
			break;
	}
}


$rss			= array ( 	
							// User TimeLine RSS & Atom
							 array(	 'url'		=> "http://api.jiwai.de/statuses/user_timeline/$page_user_id.rss"
									,'title'	=> "$page_user_info[nameFull] (RSS)"
									,'type'		=> "rss"
								)
							,array(	 'url'		=> "http://api.jiwai.de/statuses/user_timeline/$page_user_id.atom"
									,'title'	=> "$page_user_info[nameFull] (Atom)"
									,'type'		=> "atom"
								)

							// Friends TimeLine RSS & Atom
							,array(	 'url'		=> "http://api.jiwai.de/statuses/friends_timeline/$page_user_id.rss"
									,'title'	=> "$page_user_info[nameFull]和朋友们 (RSS)"
									,'type'		=> "rss"
								)
							,array(	 'url'		=> "http://api.jiwai.de/statuses/friends_timeline/$page_user_id.atom"
									,'title'	=> "$page_user_info[nameFull]和朋友们 (Atom)"
									,'type'		=> "atom"
								)
						);

$options = array(	 'title'		=> "$page_user_info[nameScreen] / $page_user_info[nameFull]"
					,'keywords'		=> htmlspecialchars($keywords)
					,'description'	=> htmlspecialchars($description)
					,'author'		=> htmlspecialchars($keywords)
					,'rss'			=> $rss
					,'refresh_time'	=> '600'
					,'refresh_url'	=> ''
			);

JWTemplate::html_head($options) ;

?>

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


//die(var_dump($page_user_id));
JWTemplate::StatusHead($page_user_id, $user_rows[$page_user_id], @$head_status_rows[$head_status_id]
						, null // options
						, $show_protected_content 
					);

?>

<?php 
$menu_list = array (
		 '和朋友们(24小时内)'	=> array('active'=>false	,'url'=>"/$page_user_info[nameScreen]/with_friends")
		,'以前的'	=> array('active'=>false	,'url'=>"/$page_user_info[nameScreen]/")
	);

if ( $g_user_with_friends )
	$menu_list['和朋友们(24小时内)']['active'] = true;
else
	$menu_list['以前的']['active'] = true;


if ( $show_protected_content )
	JWTemplate::tab_menu($menu_list) 
?>

			<div class="tab">

<?php 
if ( $show_protected_content )
	JWTemplate::tab_header( array() ) 
?>

<?php 
if ( !isset($g_user_with_friends) )
	$g_user_with_friends = false;

// 只有用户不设置保护，或者设置了保护是好友来看的时候，才显示内容
if ( $show_protected_content )
	JWTemplate::Timeline($status_data['status_ids'], $user_rows, $status_rows, array('icon'=>$g_user_with_friends)) 

?>
  
<?php 
if ( $show_protected_content )
	JWTemplate::pagination($pagination) 
?>

<?php 
if ( $show_protected_content )
	JWTemplate::rss( $g_user_with_friends ? 'friends' : 'user' ,$page_user_id) 
?>

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
