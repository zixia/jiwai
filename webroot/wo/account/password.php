<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

/*
@header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
@header("Cache-Control: no-store, no-cache, must-revalidate");
@header("Pragma: no-cache");
*/


JWLogin::MustLogined();


$user_info		= JWUser::GetCurrentUserInfo();
$is_web_user	= JWUser::IsWebUser($user_info['idUser']);

$is_reset_password	= JWSession::GetInfo('reset_password', false);

if ( $is_web_user && !$is_reset_password )
{
	$verify_corrent_password = true;
}
else
{
	$verify_corrent_password = false;
}


//die(var_dump(JWUser::IsWebUser($user_info['idUser'])));

if ( isset($_REQUEST['password']) )
{
	$current_password 		= trim( @$_REQUEST['current_password'] );
	$password 				= trim( @$_REQUEST['password'] );
	$password_confirmation 	= trim( @$_REQUEST['password_confirmation'] );

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
<li>当前密码输入错误，清除新输入</li>
_HTML_;
	}


	/*
	 * Update User Databse
	 */
	if ( empty($error_html) )
	{
		if ( ! JWUser::ChangePassword($user_info['id'], $password_confirmation) )
		{
			$error_html = <<<_HTML_
<li>密码修改失败，请稍后再试。</li>
_HTML_;
			JWSession::SetInfo('error', $error_html);
		}
		else
		{
			$notice_html = <<<_HTML_
<li>密码修改成功！</li>
_HTML_;
			if ( !$is_web_user )
				JWUser::SetWebUser($user_info['idUser']);

			// 重设密码成功，现在清理掉重设密码的标志
			if ( $is_reset_password	)
				JWSession::GetInfo('reset_password');

			JWSession::SetInfo('notice', $notice_html);
		}

		header ( "Location: /wo/account/password" );
	}
}


?>
<html>

<head>
<?php JWTemplate::html_head() ?>
</head>

<body class="account" id="password">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">


			<h2> <?php echo $user_info['nameScreen']?> </h2>

<?php JWTemplate::UserSettingNav('password'); ?>


<?php

if ( empty($error_html) )
	$error_html	= JWSession::GetInfo('error');

if ( empty($notice_html) )
{
	$notice_html	= JWSession::GetInfo('notice');
}

if ( !empty($error_html) )
{
		echo <<<_HTML_
			<div class="notice">密码未能修改：<ul> $error_html </ul></div>
_HTML_;
}


if ( !empty($notice_html) )
{
	echo <<<_HTML_
			<div class="notice"><ul>$notice_html</ul></div>
_HTML_;
}
?>

			<form action="/wo/account/password" method="post" name="f">
				<fieldset>
					<table cellspacing="0">
<?php if ( $verify_corrent_password ) { ?>
						<tr>
							<th><label for="current_password">当前密码：</label></th>

							<td><input id="current_password" name="current_password" type="password" /></td>
						</tr>
<?php } ?>
						<tr>
							<th><label for="password">新密码：</label></th>
	 						<td><input id="password" name="password" type="password" /></td>
						</tr>
						<tr>
							<th><label for="password_confirmation">重复输入新密码：</label></th>

							<td><input id="password_confirmation" name="password_confirmation" type="password" /></td>
						</tr>
						<tr>
							<th></th>
							<td><input name="commit" type="submit" value="更改" /></td>
						</tr>
					</table>
				</fieldset>
			</form>

			<script type="text/javascript">
//<![CDATA[
$('current_password').focus()
//]]>
			</script>


		</div><!-- wrapper -->
	</div><!-- content -->

</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
