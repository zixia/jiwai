<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$user_info = JWUser::GetCurrentUserInfo();

if (!empty($_POST['token']) && strstr($_SERVER['HTTP_REFERER'], 'jiwai.de/')) {
//FIXME: CSRF HOLE!
	if (empty($_POST['grant'])) {
		JWOAuth::RevokeToken($user_info['id'], $_POST['token']);
		$error_html = '授权已否决';
	} else {
		JWOAuth::AuthorizeToken($user_info['id'], $_POST['token']);
		$error_html = '应用程序授权成功！';
	}
	if (empty($_POST['callback'])) {
		JWSession::SetInfo('notice', $error_html);
		JWTemplate::RedirectToUrl('/wo/oauth/');
	} else {
		JWTemplate::RedirectToUrl($_POST['callback']);
	}
}
if (empty($_GET['oauth_token'])) {
	JWTemplate::RedirectToUrl('/wo/oauth/');
}
$token = JWOAuth::GetToken($_GET['oauth_token']);
if (!$token) {
	JWSession::SetInfo('error', '无效的请求令牌');
	JWTemplate::RedirectToUrl('/wo/oauth/');
}
$consumer = JWOAuth::GetConsumer($token->consumer_key);
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
以下应用程序请求操作您的叽歪帐户 <br />
<span><?php echo htmlentities($consumer['title']); ?></span> <br />
确认吗？
<form method="post" action="authorize">
<input type="hidden" name="token" value="<?php echo htmlentities($_GET['oauth_token']); ?>" />
<input type="hidden" name="callback" value="<?php echo empty($_GET['oauth_callback']) ? '' : htmlentities($_GET['oauth_callback']); ?>" />
	<p class="po2">
<input type="submit" name="grant" class="submitbutton" value="是"/>
<input type="submit" name="revoke" class="submitbutton" value="否" onClick="window"/>
	</p>
</form>
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
