<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();


$user_info = JWUser::GetCurrentUserInfo();

$param = $_REQUEST['pathParam'];
if ( ! preg_match('#^/(.+)$#',$param,$match) )
{
	$error_html = '哎呀！系统路径好像不太正确……';

	die($error_html);
}

$confirm_url = $match[1];

if ( isset($_REQUEST['action']) )
{
	$action = array_keys($_REQUEST['action']);
	$action = $action[0];

	$info = JWOpenid_Server::GetRequestInfo();

	switch ( $action )
	{
		case 'accept_always':
			JWOpenid_TrustSite::Create($user_info['idUser'], $confirm_url);
			//fall to accept once after save.
		case 'accept_once':
			// DoAuth should not return
			JWOpenid_Server::DoAuth($info);
		break;

		default: // fall to deny
		case 'deny':
			JWOpenid_Server::AuthCancel($info);
		break;
	}
}
?>
<html>

<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="settings">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<?php JWTemplate::ShowActionResultTips(); ?>

<div id="container" class="subpage">
<?php JWTemplate::SettingTab('/wo/openid/'); ?>
<div class="tabbody">
	<h2> <?php echo $user_info['nameScreen']?> </h2>

<?php $request = JWOpenid_Server::GetRequestInfo(); ?>

<div>

<ul>
<li>网站 <strong><?php echo $confirm_url?></strong> 希望使用你的OpenID进行登录</li>
<li>你的OpenID是： <strong><?php echo $request->identity?></strong></li>

<li>
<form method="POST">
<label>你的意见是：</label>
<input type="submit" name="action[accept_once]" value="只同意这次" /> 
<input type="submit" name="action[accept_always]" value="一直同意" /> 
<input type="submit" name="action[deny]" value="拒绝" /> 
</form>
</li>
</ul>

<ul class="list_ji">
<li><a href="http://openids.cn/openid-introduction/" target="_blank">什么是 OpenID？</a></li>
<li><a href="http://openids.cn/how-to-use-openid/" target="_blank">OpenID如何使用？</a></li>
</ul>

</div>
</div><!-- #tabbody -->
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
