<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$current_user_id = JWLogin::GetCurrentUserId();
$current_user_info = JWUser::GetUserInfo($current_user_id);

/*
 *	得到 receiver user id
 *
 */
$reply_message_id = null;
if ( preg_match('/^\/(\d+)(\/\d+|\/?)$/',@$_REQUEST['pathParam'] ,$matches) )
{
	$receiver_user_id = $matches[1];
	$reply_message_id = intval(trim($matches[2],'/'));
	$reply_message_id = $reply_message_id ? $reply_message_id : null;
}
else if ( preg_match('/^\/(\S+)\/(\S+)$/',@$_REQUEST['pathParam'] ,$matches) )
{
	$receiver_user_id = JWUser::GetUserInfo($matches[1],'idUser');
}
else if ( isset($_REQUEST['user']['id']) )
{
	$receiver_user_id = $_REQUEST['user']['id'];
}
else
{
	JWTemplate::RedirectTo404NotFound();
}

$receiver_user_row = JWUser::GetUserInfo($receiver_user_id);
if ( empty($receiver_user_row) )
{
	JWTemplate::RedirectTo404NotFound();
	exit(0);
}
$receiver_user_id = $receiver_user_row['id'];

/**
 *	如果提交了消息内容
 *
 */
if ( isset($_REQUEST['jw_status']) )
{
	$message = $_REQUEST['jw_status'];
	$message = trim($message);

	if ( false==empty($message) )
	{
		$is_succ = JWSns::CreateMessage( $current_user_id, $receiver_user_id, $message, 'web', array(
			'reply_id' => $reply_message_id,
		));

		if( $is_succ )
		{
			JWSession::SetInfo('notice', '悄悄话发送成功。'); 
			JWTemplate::RedirectToUrl('/wo/direct_messages/sent');
		}
	}
	else
	{
		JWSession::SetInfo('哎呀！请不要发送空悄悄话！');
	}

	JWTemplate::RedirectToLastUrl('/');
}
?>

<head>
<?php JWTemplate::html_head() ?>
</head>

<body class="normal">

<?php JWTemplate::header() ?>

<div id="container">
	<div id="content">
		<div id="wrapper">
<?php
JWTemplate::ShowActionResultTips();
JWTemplate::updater(array(
	'title' => '发送悄悄话',
	'mode' => 2,
	'friends' => array( $receiver_user_id => $receiver_user_row ),
));
?>

		</div><!-- wrapper -->
	</div><!-- content -->

<?php
$arr_count_param = JWSns::GetUserState($current_user_id);
$arr_friend_list = JWFollower::GetFollowingIds($current_user_id);

$arr_menu = array(
	array ('status', array($current_user_info)),
	array ('count', array($arr_count_param)),
	array ('friend', array($arr_friend_list)),
);
	
JWTemplate::sidebar( $arr_menu );
JWTemplate::container_ending();
?>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
