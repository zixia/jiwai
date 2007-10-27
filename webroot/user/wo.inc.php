<?php
JWTemplate::html_doctype();
$q = isset($_REQUEST['q']) ? $_REQUEST['q'] : null;
$page = isset( $_REQUEST['page'] ) ? intval( $_REQUEST['page'] ) : 1;
$page = ( $page < 1 ) ? 1 : $page;

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

if ( !JWUser::IsAdmin($logined_user_info['idUser'])
		&& $logined_user_info['idUser']!=$page_user_id 
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
<html xmlns="http://www.w3.org/1999/xhtml">

<?php 

$active_tab = 'archive';

if ( isset($g_user_with_friends) && $g_user_with_friends )
	$active_tab = 'friends';

if( $func == 'search' )
	$active_tab = 'search';

/*
 *	使用 JWPagination 时，要注意用户在最上面已经显示了一条了，所以总数应该减一
 *
 */
switch ( $active_tab )
{
	default:
	case 'archive':
		if( $page_user_info['idConference'] ) {
			//论坛模式用户
			$user_status_num	= JWDB_Cache_Status::GetStatusNumFromSelfNReplies($page_user_id);
			$pagination		= new JWPagination($user_status_num-1, $page );
			$status_data 		= JWStatus::GetStatusIdsFromConferenceUser( $page_user_id, $pagination->GetNumPerPage(), $pagination->GetStartPos()+1 );
		}else{
			// 显示用户自己的
			$user_status_num= JWDB_Cache_Status::GetStatusNum($page_user_id);
			$pagination		= new JWPagination($user_status_num-1, $page);
			$status_data 	= JWDB_Cache_Status::GetStatusIdsFromUser( $page_user_id, $pagination->GetNumPerPage(), $pagination->GetStartPos()+1 );
		}
		break;

	case 'replies':
		die("UNSUPPORT");
		break;

	case 'friends':
		// 显示用户和好友的

		//$user_status_num= JWStatus::GetStatusNumFromFriends($page_user_id);
		$user_status_num= JWDB_Cache_Status::GetStatusNumFromFriends($page_user_id);

		$pagination		= new JWPagination($user_status_num, $page);

		//$status_data 	= JWStatus::GetStatusIdsFromFriends( $page_user_id, $pagination->GetNumPerPage(), $pagination->GetStartPos() );
		$status_data 	= JWDB_Cache_Status::GetStatusIdsFromFriends( $page_user_id, $pagination->GetNumPerPage(), $pagination->GetStartPos() );

		break;
	case 'search':
		$searchStatus = new JWSearchStatus();

		$p = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
		$searchStatus->setPageNo( $p );

		$searchStatus->setInSite("jiwai.de/".$page_user_info['nameUrl']."/statuses/");
		$searchStatus->execute($q);

		$user_status_num = $searchStatus->getTotalSize();
		$pagination	= new JWPagination($user_status_num, $page);
		$user_ids = array($page_user_id); 

		$status_ids = $searchStatus->getStatusIds();
		$status_data = array('user_ids'=>$user_ids, 'status_ids'=>$status_ids);
		break;
}

// use cache $status_rows	= JWStatus::GetStatusDbRowsByIds( $status_data['status_ids']);
$status_rows	= JWDB_Cache_Status::GetDbRowsByIds( $status_data['status_ids']);

//die(var_dump($status_rows));

$status_data['user_ids'][] = $page_user_id;

$user_rows		= JWUser::GetUserDbRowsByIds	($status_data['user_ids']);

if( $page_user_info['idConference'] ) {
	$head_status_data 	= JWStatus::GetStatusIdsFromConferenceUser( $page_user_id, 1 );
}else{
	$head_status_data 	= JWDB_Cache_Status::GetStatusIdsFromUser( $page_user_id, 1 );
}
$head_status_rows 	= JWDB_Cache_Status::GetDbRowsByIds($head_status_data['status_ids']);
$head_status_id 	= @array_shift($head_status_data['status_ids']); 


/*
 *	设置 html header
 */
$keywords 		= <<<_STR_
$page_user_info[nameUrl]($page_user_info[nameFull]) - $page_user_info[bio] $page_user_info[location] 
_STR_;

$description = "叽歪de $page_user_info[nameFull] ";
if ($show_protected_content) {

$description .= @$head_status_rows[$head_status_id]['status'];

foreach ( $status_data['status_ids'] as $status_id )
{
	$description .= ' '.$status_rows[$status_id]['status'];
	if ( mb_strlen($description,'UTF-8') > 140 )
	{
			$description = mb_substr($description,0,140,'UTF-8');
			break;
	}
}
}

$rss = array ( 	
	// User TimeLine RSS & Atom
	array(
		'url' => "http://api.jiwai.de/statuses/user_timeline/$page_user_id.rss",
		'title'	=> "$page_user_info[nameFull] (RSS)",
		'type' => "rss",
	),
	array(
		'url' => "http://api.jiwai.de/statuses/user_timeline/$page_user_id.atom",
		'title'	=> "$page_user_info[nameFull] (Atom)",
		'type' => "atom",
	),
	// Friends TimeLine RSS & Atom,
	array(
		'url' => "http://api.jiwai.de/statuses/friends_timeline/$page_user_id.rss",
		'title'	=> "$page_user_info[nameFull]和朋友们 (RSS)",
		'type' => "rss",
	),
	array(
		'url' => "http://api.jiwai.de/statuses/friends_timeline/$page_user_id.atom",
		'title' => "$page_user_info[nameFull]和朋友们 (Atom)",
		'type' => "atom",
	),
);

$options = array(
	'title'	=> "$page_user_info[nameScreen] / $page_user_info[nameFull]",
	'keywords' => htmlspecialchars($keywords),
	'description' => htmlspecialchars($description),
	'author' => htmlspecialchars($keywords),
	'rss' => $rss,
	'refresh_time' => '60',
	'refresh_url' => '',
	'ui_user_id' => $page_user_id,
	'openid_server'	=> "http://jiwai.de/wo/openid/server",
	'openid_delegate' => "http://jiwai.de/$page_user_info[nameUrl]/",
);

?>
<head>
<?php 
JWTemplate::html_head($options); 
?>
</head>


<body class="normal">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

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
	'friends' => array(
		'active' => false,
		'name' => "和朋友们",
		'url' => "/$page_user_info[nameUrl]/with_friends",
	),
	'archive' => array(
		'active' => false,
		'name' => "以前的",
		'url' => "/$page_user_info[nameUrl]/",
	),
);

if( null!==$q ){
	$menu_list['search'] = array(	
		'active' => false , 
		'name' => "搜索结果" ,
		'url' => "/$page_user_info[nameUrl]/search?q=".urlEncode($q)
	);
}

if( $active_tab !== 'search' ) 
	unset( $menu_list['search'] );

$menu_list[$active_tab]['active'] = true;
//die(var_dump($menu_list));


if ( $show_protected_content )
	JWTemplate::tab_menu($menu_list); 
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
	JWTemplate::Timeline( $status_data['status_ids'], $user_rows, $status_rows, array(
		'icon'	=> $g_user_with_friends,
		//如果当前用户就是保护的，则不显示；如果当前登录用户不是当前页面用户，也要保护。
		'protected'=> !( $show_protected_content || $logined_user_info['idUser']==$page_user_id ),
		//,'protected'=> $logined_user_info['idUser']!=$page_user_id
		'pagination' => $pagination, 
	) ) ;

?>
  
<?php 
/*
if ( $show_protected_content )
	JWTemplate::pagination($pagination, (null===$q) ? array() : array('q'=>$q) );
if ( $show_protected_content )
	JWTemplate::rss( $g_user_with_friends ? 'friends' : 'user' ,$page_user_id) 
*/
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

$idUserVistors = JWSns::GetIdUserVistors( $page_user_info['id'], @$logined_user_info['id'] );

$arr_menu = array(
	array ('user_notice', array($page_user_info)),
	array ('device_info', array($page_user_info)),
	array ('user_info', array($page_user_info)),
	array ('action', array($user_action_row,$page_user_info['id'])),
	array ('count', array($arr_count_param,$page_user_info)),
	array ('separator', array()),
	array ('friend', array($arr_friend_list)),
	array ('separator', array()),
	array ('vistors', array($idUserVistors )),
	array ('rss', array('user', $page_user_info['nameScreen'])),
);

if ( false == JWLogin::IsLogined() )
	array_push ( $arr_menu, array('register', array(true)) );

JWTemplate::sidebar( $arr_menu, $page_user_id);
JWTemplate::container_ending();
?>

</div><!-- #container -->
<?php JWTemplate::footer() ?>
</body>
</html>
