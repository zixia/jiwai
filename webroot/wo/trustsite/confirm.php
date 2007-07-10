<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();


$user_info		= JWUser::GetCurrentUserInfo();


$param = $_REQUEST['pathParam'];
if ( ! preg_match('#^/(.+)$#',$param,$match) )
{
	$error_html =<<<_HTML_
哎呀！系统路径好像不太正确……
_HTML_;
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

		default:
			// fall to deny
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
<div class="separator"></div>

<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">


			<h2> <?php echo $user_info['nameScreen']?> </h2>

<?php JWTemplate::UserSettingNav('openid'); ?>

<hr class="separator" />

<?php

JWTemplate::ShowActionResultTips();

?>


<ul>
<li><a href="http://openids.cn/openid-introduction/" target="_blank">什么是 OpenID？</a></li>
<li><a href="http://openids.cn/how-to-use-openid/" target="_blank">OpenID如何使用？</a></li>
</ul>

<hr class="separator" />

<?php
$request = JWOpenid_Server::GetRequestInfo();
?>

<div>

<ul>
<li>网站 <strong><?php echo $confirm_url?></strong> 希望使用您的OpenID进行登录</li>
<li>您的OpenID是： <strong><?php echo $request->identity?></strong></li>
</ul>

<h3>您的意见是：</h3>

<form method="POST">
<input type="submit" name="action[accept_once]" value="只同意这次" /> 
<input type="submit" name="action[accept_always]" value="一直同意" /> 
<input type="submit" name="action[deny]" value="拒绝" /> 
</form>

</div>

		</div><!-- wrapper -->
	</div><!-- content -->

</div><!-- #container -->
<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
