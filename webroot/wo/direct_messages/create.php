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
哎呀！请不要发送空消息！
_HTML_;
	}else if ( JWSns::CreateMessage(	 $logined_user_id
										,$receiver_user_id
										,$message
									)
			)
	{
		$notice_html = <<<_HTML_
您的悄悄话已经发送给<a href="/$receiver_user_row[nameScreen]/">$receiver_user_row[nameFull]</a>了，耶！
_HTML_;
	}
	else
	{
		$error_html = <<<_HTML_
哎呀！由于系统临时故障，您的悄悄话未能成功的发送给<a href="/$receiver_user_row[nameScreen]/">$receiver_user_row[nameFull]</a>，请稍后再试吧。
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

<?php JWTemplate::html_head() ?>


<body class="direct_messages" id="create">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div class="separator"></div>

<div id="container">
	<div id="content">
		<div id="wrapper">

<style type="text/css">
#content #doingForm .bar {
line-height:2.5em;
padding:0pt 10px;
position:relative;
}
</style>




<form action="/wo/direct_messages/create/<?php echo $receiver_user_id?>" id="doingForm" method="post" name="f">
	<fieldset>
		<div class="bar odd">
			<h3><label for="doing">发送给 <a href="/<?php echo $receiver_user_row['nameScreen']?>"><?php echo $receiver_user_row['nameScreen']?></a> 悄悄话。</label></h3>

			<span>
				还可以输入：<strong id="status-field-char-counter"></strong>个字符
			</span>
		</div>
		<div class="info">
		<textarea cols="15" id="text" name="text" onkeypress="return (event.keyCode == 8) || (this.value.length &lt; 140);" onkeyup="updateStatusTextCharCounter(this.value)" rows="3"></textarea>
		</div>


		<div class="submit">
			<input id="submit" name="commit" class="buttonSubmit" value="送出悄悄话" type="submit">
		</div>
	</fieldset>
</form>

<script type="text/javascript">
//<![CDATA[
$('submit').onmouseover = function(){
    this.className += "Hovered"; 
}

$('submit').onmouseout = function(){
    this.className = this.className.replace(/Hovered/g, "");
}

//]]>
</script>

<script type="text/javascript">
//<![CDATA[
$('text').focus()
//]]>
</script>
<script type="text/javascript">
//<![CDATA[

	function updateStatusTextCharCounter(value) {
		$('status-field-char-counter').innerHTML = 140 - value.length;
	};

//]]>
</script>
<script type="text/javascript">
//<![CDATA[
$('status-field-char-counter').innerHTML = 140 - $('text').value.length;
//]]>
</script>


		</div><!-- wrapper -->
	</div><!-- content -->

<?php
$arr_count_param	= JWSns::GetUserState($logined_user_id);

$arr_friend_list	= JWFriend::GetFriendIds($logined_user_id);

$arr_menu 			= array(	array ('status'			, array($logined_user_info))
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


