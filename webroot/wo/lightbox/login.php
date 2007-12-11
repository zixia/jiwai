<?php
require_once('../../../jiwai.inc.php');

if ( array_key_exists('username_or_email',$_POST) )
{
	$username_or_email  = $_POST['username_or_email'];
	$password			= $_POST['password'];

	if ( JWOpenid::IsPossibleOpenid($username_or_email) )
	{
		JWOpenid_Consumer::AuthRedirect($username_or_email);
		// if it return, mean $username_or_email is not a valid openid url.
	}

	if( true == JWUser::IsValidName($username_or_email) )
		$idUser = JWUser::GetUserFromPassword($username_or_email, $password);
	//$idUser = $username_or_email;

	if ( !$idUser )
		if (true == JWDevice::IsValid($username_or_email, 'all'))
		{
			$deviceUserId_rows    = JWUser::GetSearchDeviceUserIds($username_or_email, array('sms','qq','msn','skype','newsmth','facebook','yahoo'));

			if ( !empty($deviceUserId_rows) )
			{
				foreach($deviceUserId_rows as $deviceUserId)
				{
					if(JWUser::VerifyPassword($deviceUserId, $password)) 
					{
						$idUser = $deviceUserId;
						break;
					}
				}
			}
		}
		else
		{
			echo "用户名 或 号码 含有不合法字符。";
			exit(0);
		}
//var_dump($idUser);
	if ( $idUser )
	{
		echo '+'.$idUser;
		if ( isset($_REQUEST['remember_me']) && $_REQUEST['remember_me'] )
			$remember_me = true;
		else
			$remember_me = false;
		JWLogin::Login($idUser, $remember_me);
	}
	else
	{
		echo '用户名、号码或密码不正确，请重新登陆。';//var_dump($_REQUEST);exit;
	}
	exit(0);
}
?>

<?php JWTemplate::html_doctype(); ?>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php JWTemplate::html_head() ?>
</head>
<body bgcolor="#999999">
<div id="wtLightbox">
<h2 class="red" style="margin-left:0px;" id="loginTips" name="loginTips">你没有登陆或者登陆已经超时，请重新登陆。
<?php 
?>
</h2>
<form id="f" method="POST" action="/wo/minilogin">
	<p class="lightbox_login">用户名：<input type="text" class="inputStyle" id="username_or_email" name="username_or_email"/></p>
	<p class="lightbox_login">密<span class="mar">码</span>：<input type="password" class="inputStyle" id="password" name="password" onkeydown="if(event.keyCode == 13) return JWCheckLogined();"/></p>
<div class="lightbox_login" style="margin-left:50px;">
        <input id="every_re" name="remember_me" type="radio" value="0" tabindex="3" /> <label for="every_re">每次重新登录</label>
        <input id="month_re" name="remember_me" type="radio" value="1" checked="checked" tabindex="4" /> <label for="month_re">一月内自动登录</label>
    <!--li>
        <input id="never_re" name="remember_me" type="radio" value="2" checked/> <label for="never_re">永久自动登录</label>
    </li-->
</div>
	<p class="po8" style="margin:0pt 0px 15px 50px;">
	  <input id="jwsubmit" name="jwsubmit" type="button" class="submitbutton" value="登录" onclick="return JWAction.login();"/>&nbsp;&nbsp;<a href="<?php echo JW_SRVNAME .'/wo/account/create';?>" class="normLink" target="_blank">新用户注册</a>&nbsp;&nbsp;<a href="<?php echo JW_SRVNAME .'/wo/account/resend_password';?>" class="normLink" target="_blank">忘记密码了？</a>
	</p>
  </form>
</div>

<!-- #container -->

<script type="text/javascript">
//  document.getElementById('username_or_email').focus();
$('username_or_email').focus();
</script>

</body>
</html>
