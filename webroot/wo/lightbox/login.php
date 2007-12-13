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
			echo '-用户名与密码不匹配 -- 请重试';
			exit(0);
		}

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
		echo '-用户名与密码不匹配 -- 请重试';
	}
	exit(0);
}
?>

<div id="wtLightbox">
<h2 class="red" id="loginTips">您还没有登录，请先登录</h2>
<form id="f" method="POST" action="/wo/lightbox/login">
	<p class="lightbox_login">用户名：<input name="username_or_email" id="username_or_email" type="text" class="inputStyle" /></p>
	<p class="lightbox_login">密<span class="mar">码</span>：<input name="password" type="password" id="password" class="inputStyle" onkeydown="if(event.keyCode==13) return JWAction.login();"/></p>

	<p class="po"><a href="/wo/account/create">新用户注册</a></p>

	<ul>
		<li class="box3"><input id="remember_none" type="radio" name="radiobutton" value="radiobutton" /></li>
		<li class="box4"><label for="remember_none">每次都重新登录</label></li>
	</ul>
	<ul>
		<li class="box3"><input id="remember_month" type="radio" name="radiobutton" checked value="radiobutton" /></li>
		<li class="box4"><label for="remember_month">一个月内自动登录</label></li>
	</ul>

	<p class="butt">
	  <input id="jwsubmit" name="jwsubmit" type="button" class="submitbutton" value="登录" onclick="return JWAction.login();"/>&nbsp;&nbsp;<input type="button" class="closebutton" value="取消" onclick="TB_remove();"/>
	</p>
</form>
</div>

<!-- #container -->
<script type="text/javascript"> $('username_or_email').focus(); </script>
