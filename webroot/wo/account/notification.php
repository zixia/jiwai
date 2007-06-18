<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();


$user_info		= JWUser::GetCurrentUserInfo();

$user_setting	= JWUser::GetNotification($user_info['id']);


//echo "<pre>";(var_dump($user_setting));
if ( isset($_REQUEST['commit']) )
{
	$user_new_setting	= $_REQUEST['user'];


	if ( ! JWUser::SetNotification($user_info['id'], $user_new_setting) )
	{
		$error_html = <<<_HTML_
<li>通知设置由于系统故障未能保存成功，请稍后再试。</li>
_HTML_;
		JWSession::SetInfo('error', $error_html);
	}
	else
	{
		$notice_html = <<<_HTML_
<li>通知设置保存成功！</li>
_HTML_;
		JWSession::SetInfo('notice', $notice_html);
	}


	header('Location: ' . $_SERVER['PHP_SELF']);
	exit(0);
}

?>
<html>

<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="notification">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<div class="separator"></div>

<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">


			<h2> <?php echo $user_info['nameScreen']?> </h2>

<?php JWTemplate::UserSettingNav('notification'); ?>


<?php

if ( empty($error_html) )
	$error_html	= JWSession::GetInfo('error');

if ( empty($notice_html) )
{
	$notice_html	= JWSession::GetInfo('notice');
}

if ( !empty($error_html) )
{
		echo <<<_HTML_
			<div class="notice">密码未能修改：<ul> $error_html </ul></div>
_HTML_;
}


if ( !empty($notice_html) )
{
	echo <<<_HTML_
			<div class="notice"><ul>$notice_html</ul></div>
_HTML_;
}
?>



<form method="post">
	<fieldset>
		<table>
			<tr>
				<th>自动提醒:</th>
				<td>
					<input <?php if ( 'Y'==$user_setting['auto_nudge_me'] ) echo ' checked="checked" ';?> 
							id="user_auto_nudge_me" name="user[auto_nudge_me]" type="checkbox" value="Y" />
					<label for="user_auto_nudge_me">如果我在24小时内没有更新，请提醒我</label>
					<p><small>提醒消息将会发送到您的手机或聊天软件上</small></p>
				</td>
			</tr>
			<tr>
	    		<th>好友通知:</th>
				<td>
					<input <?php if ( 'Y'==$user_setting['send_new_friend_email'] ) echo ' checked="checked" ';?>
							id="user_send_new_friend_email" name="user[send_new_friend_email]" type="checkbox" value="Y" />
					<label for="user_send_new_friend_email">当我被别人加为好友时发邮件给我</label>
				</td>
	  		</tr>
			<tr>
	    		<th>消息通知:</th>
				<td>
					<input <?php if ( 'Y'==$user_setting['send_new_direct_text_email'] ) echo ' checked="checked" ';?>
							id="user_send_new_direct_text_email" name="user[send_new_direct_text_email]" type="checkbox" value="Y" />
					<label for="user_send_new_direct_text_email">等我接收到新消息的时候发邮件给我</label>
				</td>
	  		</tr>
			<tr>
				<th></th>
  		
				<td>
					<input id="siv" name="siv" type="hidden" value="" />
					<input name="commit" type="submit" value="保存" />
				</td>
			</tr>
		</table>
	</fieldset>
</form>


		</div><!-- wrapper -->
	</div><!-- content -->

</div><!-- #container -->
<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
