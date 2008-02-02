<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$user_info = JWUser::GetCurrentUserInfo();

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
<li><a href="/wo/privacy/">保护设置</a></li>
<li><a href="/wo/devices/sms" class="now">绑定设置</a></li>
<li><a href="/wo/notification/email">系统通知</a></li>
<li><a href="/wo/account/profile_settings">个性化界面</a></li>
<li><a href="/wo/openid/">Open ID</a></li>
</ul>
</div><!-- leftdiv -->
<div class="rightdiv">
<div class="lookfriend">
<form id="f" action="" method="post" name="f" class="validator">
<p class="right14"><a href="/wo/devices/sms">手机</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/devices/im">聊天软件</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/bindother/" class="now">其他网站</a></p>
       <div class="binding">
	   <p>通过MSN、QQ、GTalk、Skype等聊天软件就可以接收和更新你的叽歪，现在就来绑定吧。</p>
<?php
		$aDeviceInfo_rows = JWDevice::GetDeviceRowByUserId($user_info['id']);

		$isUserLogined = JWLogin::IsLogined() ;
		$imicoUrl = "http://blog.jiwai.de/images";
		$imicoUrlSms = "/wo/devices/sms";
		$imicoUrlIm = "/wo/devices/im";
		$imicoUrlHelpSms = "http://help.jiwai.de/VerifyYourPhone";
		$imicoUrlHelpIm = "http://help.jiwai.de/VerifyYourIM";
		$imicoUrlHref = "";

		$isUseNewSmth = false;

		$bindico = JWTemplate::GetAssetUrl('/images/binding_icon.gif');
		$devices = array('facebook', 'twitter');
		$bind = JWBindOther::GetBindOther($user_info['id']);
		foreach($devices as $type)
		{
	    echo <<<_HTML_
	    <div class="entry" onclick="location.href='/wo/bindother/${type}'">
_HTML_;
			$deviceico = JWTemplate::GetAssetUrl("/images/${type}.gif");
			if ( (isset($aDeviceInfo_rows[$type])&& empty($aDeviceInfo_rows[$type]['secret'])) || (isset($bind[$type]) && empty($bind[$type]['secret']) ))
			{
				echo <<<_HTML_
	    <div class="floatleft1"><img src="$bindico" width="26" height="17" /></div>
	    <div class="content">
		<a href="/wo/bindother/$type" class="floatright orange12">设置</a><img src="$deviceico" width="129" height="30" />
_HTML_;
			}
			else
		echo <<<_HTML_
	    <div class="contentOther">
		<a href="/wo/bindother/$type" class="floatright orange12">绑定</a><span class="content"><img src="$deviceico" width="129" height="30" /></span>
_HTML_;
		echo <<<_HTML_
		</div><!-- content -->
	    </div><!-- entry -->
_HTML_;
		}

?>
		</div>
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
