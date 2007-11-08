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
		JWSession::SetInfo('error', '通知设置由于系统故障未能保存成功，请稍后再试。');
	}
	else
	{
		JWSession::SetInfo('notice', '通知设置保存成功！');
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
<?php JWTemplate::ShowActionResultTipsMain() ?>

<div id="container" class="subpage">

<?php JWTemplate::SettingTab(); ?>

<div class="tabbody">
<h2>系统通知</h2> 

<div style="width:500px; margin:30px auto; font-size:14px;">

<form id="f" method="post" action="/wo/account/notification">
<input type="hidden" name="commit_x" value="commit"/>
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
<p>悄悄话通知：
    <input <?php if ( 'Y'==$user_setting['send_new_direct_text_email'] ) echo ' checked="checked" ';?>
            id="user_send_new_direct_text_email" name="user[send_new_direct_text_email]" type="checkbox" value="Y" />
    <label for="user_send_new_direct_text_email">等我接收到新悄悄话的时候发邮件给我</label>
</p>
</div>
    <div style=" padding:20px 0 0 160px; height:50px;">
		<input onclick="if(JWValidator.validate('f'))$('f').submit();return false;" type="button" class="submitbutton" href="javascript:void(0);" value="保存"/>
    </div>            
</form>

</div>
<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>         
</div>
<!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
