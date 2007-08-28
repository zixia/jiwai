<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();


$user_info		= JWUser::GetCurrentUserInfo();
$device_row 	= JWDevice::GetDeviceRowByUserId( JWLogin::GetCurrentUserId() );

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
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="settings">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header('/wo/account/settings') ?>

<div id="container" class="subpage">
<?php JWTemplate::SettingTab(); ?>

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

<div class="tabbody">

<?php if( false == isset($device_row['sms'])) { ?>


<h2>设定手机号码</h2> 
<p>设定手机号码后你可以通过手机短信发送悄悄话、接收好友叽歪，随时随地记录生活、和好友交流！</p>

<form id="f" action="/wo/devices/create" method="POST">
    <fieldset>
        <table width="100%" cellspacing="5">
        <tr>
            <th>输入手机号码:</th>
            <td><input name="device[type]" type="hidden" value="sms" style="display:none;"/><input name="device[address]" type="text" value="" class="input" /></td>
        </tr>
        <tr>
            <th>&nbsp;</th>
            <td class="note">支持中国移动或联通手机，本服务不收取信息费。</td>
        </tr>
        </table>
    </fieldset>
    <div style=" padding:20px 0 0 160px; height:50px;">
    	<a onclick="$('f').submit();return false;" class="button" href="#"><img src="<?php echo JWTemplate::GetAssetUrl('/images/org-text-save.gif'); ?>" alt="保存" /></a>
    </div>            

</form>

<? } else if( false == $device_row['sms']['verified'] ){ ?>

<h2>设定手机号码</h2> 
<p>你的手机号码为：<?php echo $device_row['sms']['address']; ?> （<a style="font-size:14px;" href="/wo/devices/destroy/<?php echo $device_row['sms']['idDevice'];?>" onClick="if (confirm('请确认操作：删除后将永远无法恢复！')) { var f = document.createElement('form'); f.style.display = 'none'; this.parentNode.appendChild(f); f.method = 'POST'; f.action = this.href; var m = document.createElement('input'); m.setAttribute('type', 'hidden'); m.setAttribute('name', '_method'); m.setAttribute('value', 'delete'); f.appendChild(m); f.submit(); }; return false;">删除</a>）</p>
<p>请将以下验证码发送到 <?php echo JWDevice::GetMobileSpNo($device_row['sms']['address']); ?> 完成</p>

    <fieldset>
        <table width="100%" cellspacing="5">
        <tr>
            <td width="50">&nbsp;</td>
            <td>
                <strong>验证码:</strong>
                <input name="textfield" type="text" readonly value="<?php echo $device_row['sms']['secret']; ?>" class="input" style="display:inline;" />
            </td>
        </tr>
        <tr>
            <td class="note">&nbsp;</td>
            <td class="note" style="line-height:35px;">注意：用手机发送验证码或通过手机短信更新叽歪de内容，只需支付短信发送费用不收取任何信息费用。</td>
        </tr>
        </table>
    </fieldset>

<? } else { ?>

<script type="text/javascript">
</script>

<h2>设定手机号码</h2> 
<form id="f1"action="/wo/devices/sms" method="POST">

    <fieldset>
        <table width="100%" cellspacing="5">
        <tr>
            <th>当前设定的手机号为:</th>
            <td>
                <strong><?php echo $device_row['sms']['address']; ?></strong>
                （<a style="font-size:14px;" href="/wo/devices/destroy/<?php echo $device_row['sms']['idDevice'];?>" onClick="if (confirm('请确认操作：删除后将永远无法恢复！')) { var f = document.createElement('form'); f.style.display = 'none'; this.parentNode.appendChild(f); f.method = 'POST'; f.action = this.href; var m = document.createElement('input'); m.setAttribute('type', 'hidden'); m.setAttribute('name', '_method'); m.setAttribute('value', 'delete'); f.appendChild(m); f.submit(); }; return false;">删除</a>）
            </td>
        </tr>
        <tr>
            <th>&nbsp;</th>
            <td class="note">你可以使用短信发送消息到 <span class="c_black"><?php echo JWDevice::GetMobileSpNo($device_row['sms']['address']); ?></span> 更新你的叽歪，发送help获得帮助信息。 </td>
        </tr>
        <tr>
            <th>短信接收设置:</th>
            <td><input name="sendVia" type="checkbox" style="width:14px; display:inline; border:none;" id="send_via"/> <label for="send_via">使用手机短信接受通知信息</label></td>
        </tr>
        <tr>
            <th>&nbsp;</th>
            <td class="note">发送短信 <span class="c_black">ON</span> 或 <span class="c_black">OFF</span> 至 <span class="c_black"><?php echo JWDevice::GetMobileSpNo($device_row['sms']['address']); ?></span> 开启或关闭短信接收。 </td>
        </tr>
        <tr>
            <td colspan="2" class="note" style="line-height:35px; padding-left:30px;">注意：用手机发送验证码或通过手机短信更新叽歪de内容，只需支付短信发送费用不收取任何信息费用。</td>
        </tr>
        </table>
    </fieldset>

    <div style=" padding:20px 0 0 160px; height:50px;">
    	<a onclick="$('f').submit();return false;" class="button" href="#"><img src="<?php echo JWTemplate::GetAssetUrl('/images/org-text-save.gif'); ?>" alt="保存" /></a>
    </div>            

</form>

<? } ?>

</div>
<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>         
</div>
<!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
