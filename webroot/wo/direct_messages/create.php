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
else if ( preg_match('/^\/(\w+)$/',@$_REQUEST['pathParam'] ,$matches) )
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
		|| !JWFriend::IsFriend($receiver_user_id, $logined_user_id) )
{
	JWTemplate::RedirectTo404NotFound();
	exit(0);
}

/*
 *	如果提交了消息内容
 *
 */
if ( isset($_REQUEST['text']) )
{
	$message = $_REQUEST['text'];

	$message = trim($message);

	if ( empty($message) )
	{
		$error_html = <<<_HTML_
哎呀！请不要发送空悄悄话！
_HTML_;
	}else if ( JWSns::CreateMessage(	 $logined_user_id
										,$receiver_user_id
										,$message
									)
			)
	{
		$notice_html = <<<_HTML_
你的悄悄话已经发送给<a href="/$receiver_user_row[nameScreen]/">$receiver_user_row[nameFull]</a>了，耶！
_HTML_;
	}
	else
	{
		$error_html = <<<_HTML_
哎呀！由于系统临时故障，你的悄悄话未能成功的发送给<a href="/$receiver_user_row[nameScreen]/">$receiver_user_row[nameFull]</a>，请稍后再试吧。
_HTML_;
	}

    if ( !empty($error_html) )
		JWSession::SetInfo('error',$error_html);

   	if ( !empty($notice_html) )
   		JWSession::SetInfo('notice',$notice_html);

	header("Location: /wo/direct_messages/");
	exit(0);
}


//die(var_dump($_REQUEST));

?>

<head>
<?php JWTemplate::html_head() ?>
</head>



<body class="direct_messages" id="create">

<?php JWTemplate::accessibility() ?>

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

$arr_friend_list	= JWFriend::GetFriendIds($logined_user_id);

$arr_menu 			= array(	array ('status'			, array($logined_user_info))
								, array ('count'		, array($arr_count_param))
								, array ('friend'		, array($arr_friend_list))
							);
	
JWTemplate::container_ending();
JWTemplate::sidebar( $arr_menu );

?>
	
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>


