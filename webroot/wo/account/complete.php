<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once('../../../jiwai.inc.php');


$logined_user_info	= JWUser::GetCurrentUserInfo();


$address 	= @$_REQUEST['address'];

if ( !empty($address) )
{
}

?>
<html>

<?php JWTemplate::html_head() ?>

<body class="account" id="profile_image">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div class="separator"></div>

<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper" class="wrapper">


<h2>除了手机短信、IM聊天软件以外，您还可以在网页上JiWai&hellip;</h2>

<br>

<p>请填写您使用JiWai时用的手机号码或聊天软件帐号。</p>

<form action="/wo/account/complete" method="post" name="f">
<fieldset>
<table>

	<tr>
		<th><label for="address">手机号码或者聊天软件帐号(邮件地址)：</label></th>
		<td><input id="address" name="address" type="text" /></td>
	</tr>
	<tr><th></th><td><input name="commit" type="submit" value="继续" /></td></tr>
</table>
</fieldset>
</form>
<script type="text/javascript">
//<![CDATA[
$('address').focus();
//]]>
</script>



		</div><!-- wrapper -->
	</div><!-- content -->
</div><!-- #container -->

<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>

