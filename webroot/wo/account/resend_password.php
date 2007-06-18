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
重新设置您密码的说明已经发送到您的邮箱，请查收。
_HTML_;
		JWSession::SetInfo('notice', $notice_html);

		header("Location: " . JWTemplate::GetConst('UrlLogin') );
		exit(0);
	}

	$notice_html = <<<_HTML_
哎呀！我们没有找到您的邮件地址！
_HTML_;

}

?>
<html>

<head>
<?php JWTemplate::html_head() ?>
</head>

<body class="account" id="resend_password">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<div class="separator"></div>

<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">

<?php
if ( !empty($notice_html) )
	echo <<<_HTML_
<p class="notice">
$notice_html;
</p>
_HTML_;
?>

			<h2>忘记了？</h2>

<p>请输入您的 Email 地址，我们将把密码重设的链接发给您。</p>

<form action="/wo/account/resend_password" method="post" name="f">
<fieldset>
<table>
	<tr>
		<th><label for="email">Email 地址：</label></th>
		<td><input id="email" name="email" type="text" /></td>
	</tr>
	<tr><th></th><td><input name="commit" type="submit" value="将重设密码地址发给我！" /></td></tr>
</table>
</fieldset>
</form>
<script type="text/javascript">
//<![CDATA[
$('email').focus();
//]]>
</script>
		

		</div><!-- wrapper -->
	</div><!-- content -->

</div><!-- #container -->

<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
