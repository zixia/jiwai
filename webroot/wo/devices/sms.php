<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$current_user_info		= JWUser::GetCurrentUserInfo();
$current_user_id = $current_user_info['id'];
$device_row 	= JWDevice::GetDeviceRowByUserId( $current_user_id );
$notice = JWSession::GetInfo('notice');
$type = 'sms';
$typename = JWDevice::GetNameFromType($type);
?>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php 
JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
)); 
?>
</head>


<body class="account">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<?php //JWTemplate::ShowActionResultTipsMain() ?>

<div id="container">
<p class="top">设置</p>
<div id="wtMainBlock">
<div class="leftdiv">
<ul class="leftmenu">
<li><a href="/wo/account/settings">基本资料</a></li>
<li><a href="/wo/privacy/">保护设置</a></li>
<li><a href="/wo/devices/sms" class="now">绑定设置</a></li>
<li><a href="/wo/notification/email">系统通知</a></li>
<li><a href="/wo/account/profile_settings">个性化界面</a></li>
<li><a href="/wo/openid/">Open ID</a></li>
</ul>
</div><!-- leftdiv -->
<div class="rightdiv">
<div class="lookfriend">
<p class="right14"><a href="/wo/devices/sms" class="now">手机</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/devices/im">聊天软件</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/bindother/">其他网站</a></p>
       <div class="binding">
<?php if( false == isset($device_row['sms']) ) { ?>
	   <p>绑定手机号码后你可以通过手机短信发送悄悄话、接收好友叽歪,随时随地记录生活、和好友交流！</p>
		<form id="f" action="/wo/devices/create" method="post">
	   <p>输入手机号码<input name="device[type]" type="hidden" value="sms" style="display:none;"/>
		<input name="device[address]" type="text" value="" class="inputStyle" />&nbsp;&nbsp;
	   <input type="submit" id="save" class="submitbutton" value="绑定" /></p>
	  </form>
	   <p class="bindingbox">发短信给叽歪网，与发短信给普通手机费用完全一样</p>
<?php if( !empty($notice)) echo $notice;?>

<? } else if( false==$device_row['sms']['verified'] ){ ?>
	   <div class="bindingMobile">
	   <p>你想要绑定的<b class="black14">手机号码为&nbsp;<? echo $device_row['sms']['address'];?></b>（<a style="font-size:14px;" href="/wo/devices/destroy/<?php echo $device_row['sms']['idDevice'];?>" onClick="if (true || confirm('你真的要删除 <?echo $typename;?> 绑定吗？')) { var f = document.createElement('form'); f.style.display = 'none'; this.parentNode.appendChild(f); f.method = 'POST'; f.action = this.href; var m = document.createElement('input'); m.setAttribute('type', 'hidden'); m.setAttribute('name', '_method'); m.setAttribute('value', 'delete'); f.appendChild(m); f.submit(); }; return false;">删除</a>），没错吧？那就请按以下步骤操作：</p>
	   	   <p class="bindingblack">1. 将【叽歪小弟：<?php echo JWDevice::GetMobileSpNo($device_row['sms']['address']); ?>】 加入你的通讯簿</p>
		   	   <p class="bindingblack">2. 发送以下验证信息给叽歪小弟，完成绑定</p>
			          <p class="bindingblack">&nbsp;&nbsp;&nbsp;&nbsp;验证码：<input id="secret_sms" type="text" value="<? echo $device_row['sms']['secret'];?>" readonly class="inputStyle3" onclick="JiWai.copyToClipboard(this);"/><span class="copytips" id="secret_sms_tip">验证码复制成功</span></p>
					  	   <p class="bindinggray12">用手机发送验证码或发短信给叽歪网，与发短信给普通手机费用完全一样</p>
		</div>
<? } else if( true==$device_row['sms']['verified'] ){ ?>
	   <p>你绑定的<b class="black14">手机号码为&nbsp;<?php echo $device_row['sms']['address']; ?></b>（<a href="/wo/devices/destroy/<?php echo $device_row['sms']['idDevice'];?>" onClick="if(confirm('你真的要删除 <?echo $typename;?> 绑定吗？')) { var f = document.createElement('form'); f.style.display = 'none'; this.parentNode.appendChild(f); f.method = 'POST'; f.action = this.href; var m = document.createElement('input'); m.setAttribute('type', 'hidden'); m.setAttribute('name', '_method'); m.setAttribute('value', 'delete'); f.appendChild(m); f.submit(); }; return false;">删除并重设</a>）</p>
	   <p class="bindinggray">你可以使用短信发送消息到<span class="black12"><?php echo JWDevice::GetMobileSpNo($device_row['sms']['address']); ?></span>更新你的叽歪，发送<span class="black12">help</span>获得帮助信息。<br/>*发短信给叽歪，与发短信给普通手机费用完全一样</p>
	   <p><label for="direct"><input id="direct" type="checkbox" value="Y" onclick="var val;val=!this.checked?'everything':'direct_messages';JiWai.EnableDevice(<?php echo $device_row['sms']['id'];?>, 'device[enabled_for]='+val);" <?php if($device_row['sms']['enabledFor']=='direct_messages') echo "checked";?>/>只接收悄悄话&nbsp;&nbsp;</label><span class="copytips" style="display:inline;" id="tips_<? echo $device_row['sms']['id'];?>" ></span></p>
	   <p class="bindinggray">发送短信<span class="black12">ON</span>或<span class="black12">OFF</span>至<span class="black12"><?php echo JWDevice::GetMobileSpNo($device_row['sms']['address']);?></span>开启或关闭短信接收。</p>
	   <div style="overflow: hidden; clear: both; height: 10px; line-height: 1px; font-size: 1px;"></div>
<? } ?>
	   </div>
	   <div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
</div><!-- lookfriend -->
<div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
</div>
<!-- rightdiv -->
</div><!-- #wtMainBlock -->
<div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
