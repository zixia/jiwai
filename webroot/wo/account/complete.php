<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once('../../../jiwai.inc.php');

define('IS_WEB_USER'	,1);	//用户以前在 Web 上登录过
define('NOT_WEB_USER'	,2);	//用户没有在 Web 上登录过
define('NO_SUCH_USER'	,3);	//帐号与地址不匹配，或不存在

$logined_user_info	= JWUser::GetCurrentUserInfo();


$address 	= @$_REQUEST['address'];
$nameScreen	= @$_REQUEST['nameScreen'];

if ( !empty($nameScreen) )
{
	if ( IsAddressBelongsToName($address,$nameScreen) )
	{
		$user_row = JWUser::GetUserInfo($nameScreen);
		
		JWUser::IsWebUser($user_row['idUser'])
			$user_state = IS_WEB_USER;
		else
			$user_state = NOT_WEB_USER;
	}
	else
	{
		$user_state = NO_SUCH_USER;
	}
}

if ( isset($user_state) && NOT_WEB_USER==$user_state )
{
	// IM / SMS 用户第一次来，设置好登录状态后，送到用户信息修改页面
	JWLogin::Login($user_row['idUser'], false);
	header('Location: /wo/account/password');
	exit(0);
}
/*
 *	错误信息下面处理
 */
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

<br>

<form action="/wo/account/complete" method="post" name="f">
<fieldset>
<table>
	<tr>
		<th><nobr><label for="address">手机号码或者聊天软件帐号<small>(邮件地址)</small>：</label></nobr></th>
		<td><input id="address" name="address" type="text" /></td>
	</tr>
	<tr>
		<th><label for="screen_name">帐号名</small>(忘记？发送whoami或woshishui查询)</small>：</label></th>
		<td><input id="screen_name" name="nameScreen" type="text" /></td>
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

<?php
function IsAddressBelongsToName($address,$name)
{
	if ( empty($address) || empty($name) )
		return false;

	if ( preg_match('/^\d/',$name) )
		return false;

	$user_row	 	= JWUser::GetUserInfo($name);

	if ( empty($user_row) )
		return false;

	$device_row		= JWDevice::GetDeviceRowByUserId($user_row['idUser']);

	if ( empty($device_row) )
		return false;

	if ( $address!=@$device_row['sms']['address'] 
			&& $address!=@$device_row['im']['address'] )
		return false;

	return true;
}
?>
