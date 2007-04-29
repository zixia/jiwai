<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once('../../../jiwai.inc.php');
?>
<html>

<?php JWTemplate::html_head() ?>

<body class="account" id="resend_password">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<div class="separator"></div>

<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">


			<h2>忘记了？</h2>

<p>请输入您的 Email 地址，我们将把密码重设的链接发给您。</p>

<form action="/account/resend_password" method="post" name="f">
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
		</div></div><hr />

			
		

		</div><!-- wrapper -->
	</div><!-- content -->

</div><!-- #container -->
<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
