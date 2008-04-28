<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$user_info = JWUser::GetCurrentUserInfo();
$path = isset($_GET['pathParam']) ? $_GET['pathParam'] : '';
switch ($path) {
	case '/create':
        	$app = $_POST['app'];
		$app['idUser'] = $user_info['id'];
		try {
			JWOAuth::CreateConsumer($app);
			$error_html = '应用程序注册成功！';
			JWSession::SetInfo('notice', $error_html);
		} catch (Exception $e) {
			$error_html = '应用程序注册失败！'.$e->getMessage();
			JWSession::SetInfo('error', $error_html);
		}
		JWTemplate::RedirectToUrl('/wo/oauth/app');
	break;
	case '/destroy':
		JWOAuth::DestroyConsumer($user_info['id'], $_GET['key']);
		$error_html = '应用程序已删除';
		JWSession::SetInfo('notice', $error_html);
		JWTemplate::RedirectToUrl('/wo/oauth/app');
	break;
}
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
<p class="right14"><a href="/wo/oauth/">已授权的应用程序</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/oauth/app" class="now">开发者</a></p>
       <div class="binding">
<?php
$r = JWOAuth::ListConsumer($user_info['id']);
if (empty($r)) {
?>
<p>尚未注册第三方应用程序</p>
<?php
} else {
?>
<table>
<tbody>
<?php
foreach ($r as $a) {
?>
<tr><td width="400px"><a href="<?php echo $a['url']; ?>"><?php echo $a['title']; ?></a></td><td><a onclick="return confirm('API KEY和其它相关信息都将不再保留。真的要删除吗？');" href="/wo/oauth/app/destroy?key=<?php echo $a['key']; ?>">删除</a></td></tr>
<tr><td colspan="2">API KEY <?php echo $a['key']; ?><br/>SECRET <?php echo $a['secret']; ?></a></td></tr>
<?php
}
?>
</tbody>
</table>
<?php
}
?>
<p><a name="create"/><a href="#create" onclick="f.toggle();">注册应用程序并申请API KEY</a></p>
<div id="f" style="height:400px;">
<form method="post" action="/wo/oauth/app/create">
  <table>
    <tr><td>应用程序名称<br/> <small>不多于30字，勿使用特殊符号</small> 
        </td><td><input id="app_title" name="app[title]" size="30" type="text" /></td></tr>
    <tr><td>描述信息<br/> <small>向用户展示的描述信息</small>
        </td><td><textarea style="width:300px;" cols="20" id="app_description" name="app[description]" rows="4"></textarea></td></tr>
    <tr><td>内部说明信息<br/> <small>仅用于后台管理，不会公开</small>      
        </td><td><textarea style="width:300px;" cols="20" id="app_notes" name="app[notes]" rows="4"></textarea></td></tr>
    <tr><td>应用程序网址
        </td><td><input id="app_url" name="app[url]" size="30" type="text" /></td></tr>
    <tr><td>平台
        </td><td><select id="app_platform" name="app[platform]"><option value="web">Web</option>
<option value="desktop">桌面</option>
<option value="mobile">移动设备</option></select></td></tr>
    <tr><td>回调URL<br/> <small>用于OAuth回调，详见OAuth规范</small>
        </td><td><input id="app_callback_url" name="app[callback_url]" size="30" type="text" /></td></tr>
  </table>
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
<pre>
REQUEST TOKEN URL	http://api.jiwai.de/oauth/request_token
ACCESS TOKEN URL	http://api.jiwai.de/oauth/access_token
AUTHORIZE URL		http://jiwai.de/wo/oauth/authorize
</pre>
<p><!-- a href="http://help.jiwai.de/">更多开发者信息</a --> <a href="http://groups.google.com/group/jiwai-development-talk/">开发讨论区</a></p>
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
