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
<li><a href="/wo/devices/sms">绑定设置</a></li>
<li><a href="/wo/notification/email">系统通知</a></li>
<li><a href="/wo/account/profile_settings">个性化界面</a></li>
<li><a href="/wo/openid/">Open ID</a></li>
<li><a href="/wo/oauth/" class="now">OAuth</a></li>
</ul>
</div><!-- leftdiv -->
<div class="rightdiv">
<div class="lookfriend">
<p class="right14"><a href="/wo/oauth/" class="now">授权应用程序</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/oauth/app">开发者</a></p>
       <div class="binding">
<?php
$tks = JWOAuth::ListToken($user_info['id']);
if (empty($tks)) {
?>
<p>尚未授权任何第三方应用程序</p>
<?php
} else {
?>
<table>
<tbody>
<?php
foreach ($tks as $a) {
$c = JWOAuth::GetConsumer($a['consumer_key']);
?>
<tr><td width="400px"><a href="<?php echo $c['url']; ?>"><?php echo $c['title']; ?></a></td><td><a onclick="return confirm('真的要删除吗？');" href="/wo/oauth/revoke?token=<?php echo $a['key']; ?>">删除</a></td></tr>
<?php
}
?>
</tbody>
</table>
<?php
}
?>
<p><a href="#" onclick="f.toggle();return false;">认证应用程序令牌</a></p>
<div id="f" style="height:100px;">
<form method="post" action="authorize">
	<p class="po2">
请输入请求令牌：
<input type="text" name="token" size="32" />
	</p>
	<p class="po2">
<input name="close" type="submit" class="submitbutton" value="确 定"/>
<input name="close" type="button" class="submitbutton" value="取 消" onClick="f.toggle();return false;"/>
	</p>
</form>
</div>
<script type="text/javascript">
f = new Fx.Slide('f');
f.hide();
</script>
       </div>
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
