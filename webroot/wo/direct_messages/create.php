<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$logined_user_id 	= JWLogin::GetCurrentUserId();
$logined_user_info 	= JWUser::GetUserInfo($logined_user_id);

//die(var_dump($_REQUEST));


/*
 *	得到 receiver user id
 *
 */
if ( preg_match('/^\/(\d+)$/',@$_REQUEST['pathParam'] ,$matches) )
{
	$receiver_user_id	= $matches[1];
}
else if ( preg_match('/^\/(\S+)$/',@$_REQUEST['pathParam'] ,$matches) )
{
	$receiver_user_id	= JWUser::GetUserInfo($matches[1],'idUser');
}
else if ( isset($_REQUEST['user']['id']) )
{
	$receiver_user_id	= $_REQUEST['user']['id'];
}


$receiver_user_row	= JWUser::GetUserInfo($receiver_user_id);


/*
 *	如果接受者不存在，或者发送者不是接受者的好友，就404
 */
if ( empty($receiver_user_row) 
//		|| !JWFriend::IsFriend($receiver_user_id, $logined_user_id) 
)
{
	JWTemplate::RedirectTo404NotFound();
	exit(0);
}

/*
 *	如果提交了消息内容
 *
 */
if ( isset($_REQUEST['jw_status']) )
{
	$message = $_REQUEST['jw_status'];

	$message = trim($message);

	if ( !empty($message) )
	{
		JWSns::ExecWeb($logined_user_id, "d $receiver_user_row[nameScreen]", '发送悄悄话');
	}
	else
	{
		JWSession::SetInfo('哎呀！请不要发送空悄悄话！');
	}

	JWTemplate::RedirectBackToLastUrl('/');
	exit;
	/*header("Location: /wo/direct_messages/");
	exit(0);*/
}


//die(var_dump($_REQUEST));

?>

<head>
<?php JWTemplate::html_head() ?>
</head>



<body class="direct_messages" id="create">

<?php JWTemplate::header() ?>

<div id="container">
	<div id="content">
		<div id="wrapper">

<?php
JWTemplate::updater(array(
	'title' 	=> '发送悄悄话',
	'mode'		=> 1,
	'friends'	=> array($receiver_user_id => $receiver_user_row)
	));
?>

		</div><!-- wrapper -->
	</div><!-- content -->

<?php
$arr_count_param	= JWSns::GetUserState($logined_user_id);

$arr_friend_list	= JWFollower::GetFollowingIds($logined_user_id);

$arr_menu 			= array(	array ('status'			, array($logined_user_info))
								, array ('count'		, array($arr_count_param))
								, array ('friend'		, array($arr_friend_list))
							);
	
JWTemplate::sidebar( $arr_menu );
JWTemplate::container_ending();
?>
</div><!-- #container -->

<?php
JWTemplate::footer();
?>

</body>
</html>
