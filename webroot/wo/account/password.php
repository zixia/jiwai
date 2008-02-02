<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();
/*
@header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
@header("Cache-Control: no-store, no-cache, must-revalidate");
@header("Pragma: no-cache");
*/

JWLogin::MustLogined();


$user_info = JWUser::GetCurrentUserInfo();
$new_user_info = @$_POST['user'];

$is_reset_password = JWSession::GetInfo('reset_password', false);
$is_web_user = JWUser::IsWebUser($user_info['idUser']);

$outInfo = $user_info;

/** check if reset_password */

if ( $is_web_user && !$is_reset_password )
{
	$verify_corrent_password = true;
}
else
{
	$verify_corrent_password = false;
}

if ( isset($_POST['commit_p']) ) {
	if ( isset($_POST['password']) )
	{
		$current_password 		= trim( @$_POST['current_password'] );
		$password 				= trim( @$_POST['password'] );
		$password_confirmation 	= trim( @$_POST['password_confirmation'] );

		if ( $verify_corrent_password
				&& (	empty($current_password) 
					|| empty($password)
					|| empty($password_confirmation) 
				   ) )
		{
			$error_html = <<<_HTML_
				<li>请完整填写三处密码输入框</li>
_HTML_;
		}

		if ( $password !== $password_confirmation )
		{
			$error_html .= <<<_HTML_
				<li>两次输入密码不一致！请重新输入</li>
_HTML_;
		}

		if ( $verify_corrent_password &&
				! JWUser::VerifyPassword($user_info['id'], $current_password) )
		{
			$error_html .= <<<_HTML_
				<li>当前密码输入错误，请重新输入</li>
_HTML_;
		}
	}

	/*
	 * Update User Databse
	 */
	if ( empty($error_html) )
	{
		if ( ! JWUser::ChangePassword($user_info['id'], $password_confirmation) )
		{
			JWSession::SetInfo('error', '密码修改失败，请稍后再试。');
		}
		else
		{
			if ( !$is_web_user )
				JWUser::SetWebUser($user_info['idUser']);

			// 重设密码成功，现在清理掉重设密码的标志
			if ( $is_reset_password	)
				JWSession::GetInfo('reset_password');

			JWSession::SetInfo('notice', '密码修改成功！');
		}

	}
	else
	{
		JWSession::SetInfo('notice', $error_html);
	}

		JWTemplate::RedirectToUrl();
}

?>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php 
JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
)); 
?>
</head>


<body class="account" id="settings">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<?php JWTemplate::ShowActionResultTipsMain() ?>
<?php 
if ( empty($error_html) )
	$error_html = JWSession::GetInfo('error');
if ( empty($notice_html) )
	$notice_html = JWSession::GetInfo('notice');
echo $notice_html;
echo $error_html;
?>

<div id="container">
<p class="top">设置</p>
<div id="wtMainBlock">
<div class="leftdiv">
<ul class="leftmenu">
<li><a href="/wo/account/settings" class="now">基本资料</a></li>
<li><a href="/wo/privacy/">保护设置</a></li>
<li><a href="/wo/devices/sms">绑定设置</a></li>
<li><a href="/wo/notification/email">系统通知</a></li>
<li><a href="/wo/account/profile_settings">个性化界面</a></li>
<li><a href="/wo/openid/">Open ID</a></li>
</ul>
</div><!-- leftdiv -->
<div class="rightdiv">
<div class="lookfriend">
<form id="f" action="" method="post" name="f">
<input type="hidden" name="commit_p" value="1"/>
<p class="right14"><a href="/wo/account/settings">帐户信息</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/account/password" class="now">修改密码</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/account/photos">头像</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/account/profile">个人资料</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/account/interest">兴趣爱好</a></p>
	<div id="wtRegist" style="margin:30px 0 0 0;width:520px">
        <ul>
		<?php if ( true == $verify_corrent_password ){ ?>
	    <li class="box5">当前密码</li>
		<li class="box6">
		<input id="current_password" name="current_password" type="password" minlength="6" maxlength="16" class="inputStyle" alt="当前密码" class="inputStyle"/>
		</li>
		<li><div style="overflow: hidden; clear: both; height: 40px; line-height: 1px; font-size: 1px;"></div></li>
		<?php } ?>
		<li class="box5">新密码</li>
		<li class="box6">
		<input id="password" name="password" type="password" alt="新密码" minlength="6" maxlength="16" class="inputStyle"/>
		</li>
		<li class="box7">密码至少6个字符。<br />叽歪建议你使用数字、符号、字母组合的复杂密码</li>
		<li class="box5">确认新密码</li>
		<li class="box6">
<input id="password_confirmation" name="password_confirmation" type="password" compare="password" alt="确认密码" minlength="6" maxlength="16" class="inputStyle"/>
		</li>
		<li><div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div></li>
		<li class="box7">
		<input type="submit" id="save" name="save" class="submitbutton" value="保存" />
		</li>
	    </ul>
       </div><!-- wtRegist -->
	   <div style="overflow: hidden; clear: both; height: 30px; line-height: 1px; font-size: 1px;"></div>
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
