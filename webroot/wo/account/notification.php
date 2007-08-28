<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();


$user_info		= JWUser::GetCurrentUserInfo();

$user_setting	= JWUser::GetNotification($user_info['id']);


//echo "<pre>";(var_dump($user_setting));
if ( isset($_REQUEST['commit_x']) )
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

	header('Location: ' . $_SERVER['REQUEST_URI']);
	exit(0);
}

?>
<html>

<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="settings">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header('/wo/account/settings') ?>

<div id="container" class="subpage">
<?php JWTemplate::SettingTab(); ?>

<div class="tabbody">
<h2>系统通知</h2> 

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
			<div class="notice">系统通知修改：<ul> $error_html </ul></div>
_HTML_;
}

if ( !empty($notice_html) )
{
	echo <<<_HTML_
			<div class="notice"><ul>$notice_html</ul></div>
_HTML_;
}
?>

<div style="width:500px; margin:30px auto; font-size:14px;">

<form id="f" method="post" action="/wo/account/notification">
<p>更新通知：
    <input <?php if ( 'Y'==$user_setting['auto_nudge_me'] ) echo ' checked="checked" ';?> 
            id="user_auto_nudge_me" name="user[auto_nudge_me]" type="checkbox" value="Y" />
    <label for="user_auto_nudge_me">如果我在24小时内没有更新，请提醒我</label>
</p>
<p style="color:#989898; text-indent:76px; font-size:12px;">提醒消息将会发送到你的手机或聊天软件上</p>
<p>&nbsp;</p>
<p>好友通知：
    <input <?php if ( 'Y'==$user_setting['send_new_friend_email'] ) echo ' checked="checked" ';?>
            id="user_send_new_friend_email" name="user[send_new_friend_email]" type="checkbox" value="Y" />
    <label for="user_send_new_friend_email">当我被别人加为好友时发邮件给我</label>
</p>
<p>&nbsp;</p>
<p>消息通知：
    <input <?php if ( 'Y'==$user_setting['send_new_direct_text_email'] ) echo ' checked="checked" ';?>
            id="user_send_new_direct_text_email" name="user[send_new_direct_text_email]" type="checkbox" value="Y" />
    <label for="user_send_new_direct_text_email">等我接收到新消息的时候发邮件给我</label>
</p>
</div>
    <div style=" padding:20px 0 0 160px; height:50px;">
    	<a onclick="$('f').submit();return false;" class="button" href="#"><img src="<?php echo JWTemplate::GetAssetUrl('/images/org-text-save.gif'); ?>" alt="保存" /></a>
    </div>            
</form>

</div>
<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>         
</div>
<!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
