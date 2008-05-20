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
$b = JWVender::Query($user_info['id'], 'yiqi');
if (empty($b)) {
?>
Not bind yet
<?php
} else { 
echo <<<__HTML__
<a href="{$b['venderMeta']['profile_url']}">{$b['venderMeta']['display_name']}</a>
__HTML__;
?>
<?php
}
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
