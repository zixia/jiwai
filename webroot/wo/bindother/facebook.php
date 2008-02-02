<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$user_info = JWUser::GetCurrentUserInfo();
$notice = JWSession::GetInfo('notice');

?>
<html>
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
<?php JWTemplate::ShowActionResultTipsMain() ?>

<div id="container">
<p class="top">设置</p>
<div id="wtMainBlock">
<div class="leftdiv">
<ul class="leftmenu">
<li><a href="/wo/account/settings">基本资料</a></li>
<li><a href="/wo/privacy">保护设置</a></li>
<li><a href="/wo/devices/sms" class="now">绑定设置</a></li>
<li><a href="/wo/notification/email">系统通知</a></li>
<li><a href="/wo/account/profile_settings">个性化界面</a></li>
<li><a href="/wo/openid/">Open ID</a></li>
</ul>
</div><!-- leftdiv -->
<div class="rightdiv">
<div class="lookfriend">
<p class="right14"><a href="/wo/devices/sms">手机</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/devices/im">聊天软件</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/bindother/" class="now">其他网站</a></p>
       <div class="binding">
<?php
		$aDeviceInfo_rows = JWDevice::GetDeviceRowByUserId($user_info['id']);
		$bind = JWBindOther::GetBindOther($user_info['id']);
		$type = 'facebook';
		$device_row = $aDeviceInfo_rows[$type];
			if ( isset($device_row) && empty($device_row['secret']))
{
		$bind_login_name = JWFacebook::GetName($device_row['address']);
				echo <<<_HTML_
	   <p><span class="black15bold">已绑定你的${type}帐号${bind_login_name}</span> （<a  href="/wo/devices/destroy/${device_row[id]}" onClick="if ( confirm('你真的要删除 ${type} 绑定吗？')) { var f = document.createElement('form'); f.style.display = 'none'; this.parentNode.appendChild(f); f.method = 'POST'; f.action = this.href; var m = document.createElement('input'); m.setAttribute('type', 'hidden'); m.setAttribute('name', '_method'); m.setAttribute('value', 'delete'); f.appendChild(m); f.submit(); }; return false;">删除并重设</a>）</p>
	   <p>在<a href="http://apps.facebook.com/jiwaide/?verify">叽歪de Facebook Application</a>上即可更新你的信息</p>
_HTML_;
}
			else if (!empty($device_row['secret']))
				echo <<<_HTML_
	   <p class="black15bold">绑定facebook</p>
	   <p class="bindingblack">1. 请访问 <a href="http://apps.facebook.com/jiwaide/?verify">叽歪de Facebook Application</a> 并安装</p>
	   <p class="bindingblack">2. 输入你的叽歪网用户名和以下验证码</p>
       <p class="bindingblack">&nbsp;&nbsp;&nbsp;&nbsp;验证码：<input id="secret_${type}" type="text" value="${device_row['secret']}" readonly class="inputStyle3" onclick="JiWai.copyToClipboard(this);"/><span class="copytips" id="secret_${type}_tip">验证码复制成功</span></p>
	   <p class="bindingblack">3. 点击“关联”确定</p>
_HTML_;
			else
		echo <<<_HTML_
<form id="f" action="/wo/devices/create" method="post" name="f">
	   <p class="black15bold">绑定facebook</p>
	   <p class="bindingOtheredit">可以将你的叽歪同步到facebook，也可以在facebook上发布你的更新</p>
		<input name="device[address]" type="hidden" style="display:none;" id="device_facebook" value="" />
		<input name="device[type]" type="hidden" style="display:none;" id="device_facebook" value="facebook" />
	   <p><input type="submit" class="submitbutton" value="开始绑定" /></p>
	   $notice
</form>
_HTML_;
?>
	   </div><!-- binding -->
	   	   <div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
		     </form>
			 </div><!-- lookfriend -->
			 <div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
			 </div>
<!-- rightdiv -->
</div><!-- #wtMainBlock -->
<div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>
<script defer="true">
	JWValidator.init('f');
</script>
</body>
</html>
