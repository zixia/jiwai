<?php
require_once ('../../../jiwai.inc.php');
header('Content-Type: text/html;charset=UTF-8');

if ( array_key_exists('username_or_email', $_POST) )
{
	$username_or_email = $_POST['username_or_email'];
	$password = $_POST['password'];

	if ( JWOpenID::IsPossibleOpenID($username_or_email) )
	{
		JWOpenID::AuthRedirect($username_or_email);
	}

	$user_id = null;
	if( true == JWUser::IsValidName($username_or_email) )
	{
		$user_id = JWUser::GetUserFromPassword($username_or_email, $password);
	}

	if ( null==$user_id )
	{
		if (true == JWDevice::IsValid($username_or_email, 'all'))
		{
			$deviceUserId_rows    = JWUser::GetSearchDeviceUserIds($username_or_email, array('sms','qq','msn','skype','newsmth','facebook','yahoo'));
			if ( !empty($deviceUserId_rows) )
			{
				foreach($deviceUserId_rows as $deviceUserId)
				{
					if(JWUser::VerifyPassword($deviceUserId, $password)) 
					{
						$user_id = $deviceUserId;
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
	}

	if ( $user_id )
	{
		echo '+'.$user_id;
		if ( isset($_REQUEST['remember_me']) 
			&& $_REQUEST['remember_me'] ) 
		{
			$remember_me = true;
		}
		else
		{
			$remember_me = false;
		}
		JWLogin::Login($user_id, $remember_me);
	}
	else
	{
		echo '-用户名与密码不匹配 -- 请重试';
	}
	exit(0);
}
?>

<div id="login" class="free reg_wid">
	<div class="mar_b20">
		<div class="pagetitle mar_b20">
			<div class="rt"><a href="javascript:void();" onclick="return JWSeekbox.remove();" class="close">X</a></div>
			<h3 id="loginTips">登陆到叽歪</h3>
		</div>
		<dl class="pad_t8">
			<dt>用户名</dt>
			<dd class="inp"><div><input type="text" id="username_or_email" name="username_or_email" mission="JWAction.login();" onKeyDown="JWAction.onEnterSubmit(event,this);"/></div><br /></dd>
			<dt>密　码</dt>
			<dd class="inp"><div><input type="password" id="password" name="password" mission="JWAction.login();" onKeyDown="JWAction.onEnterSubmit(event,this);" /></div></dd>
			<dt></dt>
			<dd><div><input id="jz" type="checkbox" id="remember_me" name="remember_me" value="1" checked /> <label for="jz">在这台电脑上记住我</label></div></dd>
			<dt></dt>
			<dd>
				<div><input type="button" value="登陆" onclick="JWAction.login();"/></div>
			</dd>
		</dl>
		<div class="clear"></div>
	</div>
	<div class="dot_line mar_b20"></div>
	<div class="mar_b20">
		<div id="openRegBtn" onMouseOver="this.className += 'bg_gra';" onMouseOut="this.className = this.className.replace('bg_gra', '')" onClick="this.style.display='none';$('regbor').className='';JWSeekbox.adjust();"><span class="f_gra">还没有叽歪过?</span></div>
		<div id="regbor" class="no">
			<div class="pagetitle">
				<h3 id="registerTips">快速注册</h3>
			</div>
			<dl class="pad_t8">
				<dt>用户名</dt>
				<dd class="inp"><div><input type="text" id="username" name="username_or_email" onKeyDown="JWAction.onEnterSubmit(event,this);" mission="JWAction.register();" /></div><div class="f_gra">中文两个字以上<br />英文或数字四个字符以上</div></dd>
				<dt>密　码</dt>
				<dd class="inp"><div><input type="password" id="password_one" name="password_one" onKeyDown="JWAction.onEnterSubmit(event,this);" mission="JWAction.register();" /></div></dd>
				<dt>确认密码</dt>
				<dd class="inp"><div><input type="password" id="password_confirm" name="password_confirm" onKeyDown="JWAction.onEnterSubmit(event,this);" mission="JWAction.register();" /></div></dd>
				<dt></dt>
				<dd>
					<div><input type="button" value="注册" onclick="JWAction.register();" /></div>
				</dd>
			</dl>
		</div>
		<div class="clear"></div>
	</div>
</div>
