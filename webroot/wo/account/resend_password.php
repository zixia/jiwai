<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

if ( isset($_REQUEST['email']) )
{
	$email = $_REQUEST['email'];

	if ( JWUser::IsValidEmail($email, true) )
		$user_db_row = JWUser::GetUserInfo($email);


	if ( !empty($user_db_row) )
	{
		JWSns::ResendPassword($user_db_row['idUser']);

		$notice_html = <<<_HTML_
重新设置你密码的说明已经发送到你的邮箱，请查收。
_HTML_;
		JWSession::SetInfo('notice', $notice_html);

		header("Location: " . JWTemplate::GetConst('UrlLogin') );
		exit(0);
	}

	$notice_html = <<<_HTML_
哎呀！我们没有找到你的邮件地址！
_HTML_;

}

?>
<html>

<head>
<?php JWTemplate::html_head() ?>
</head>

<body class="account">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<?php JWTemplate::ShowActionResultTips(); ?>

<div id="container" class="subpage">
<h2>忘记了？</h2>

<p style="margin:15px;">请输入你的 Email 地址，我们将把密码重设的链接发给你。</p>

<form action="/wo/account/resend_password" method="post" name="f">
<fieldset>
<table>
	<tr>
		<th><label for="email">Email 地址：</label></th>
		<td><input id="email" name="email" type="text" /></td>
	</tr>
	<tr height="100">
		<th></th>
		<td><input name="commit" style="width:50px;margin:15px 0;" type="submit" value="确定" /></td>
	</tr>
</table>
</fieldset>
</form>
<script type="text/javascript">
//<![CDATA[
$('email').focus();
//]]>
</script>
		

</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
