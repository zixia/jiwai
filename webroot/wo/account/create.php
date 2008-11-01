<?php
require_once('../../../jiwai.inc.php');
if ( false==JWLogin::IsAnonymousLogined() )
{
	JWLogin::Logout();
}

$user = array();
if ( $_POST ) 
{
	$user = $read_and_accept = $error_string = null;
	extract( $_POST, EXTR_IF_EXISTS );

	if ( null == $read_and_accept )
	{
		$error_string .= '<LI>使用叽歪服务必须接受叽歪服务条款</LI>';
	}

	$validate_item = array(
		array( 'Email', $user['email'] ),
		array( 'NameScreen', $user['name_screen'] ),
		array( 'Compare', $user['pass'], $user['pass_confirm'] ),
	);

	$validate_result = JWFormValidate::Validate($validate_item);

	if ( is_array($validate_result) )
	{
		foreach ($validate_result AS $item)
		{
			$error_string .= "<LI>$item</LI>";
		}
	}

	/**
	$location = intval(@$_REQUEST['province'])."-".intval(@$_REQUEST['city']);
	$location = trim($location);
	$reg_user_info['location'] = $location;
	*/

	if ( null == $error_string ) 
	{
		$user['nameFull'] = $user['name_screen'];
		$user['ip'] = JWRequest::GetIpRegister();
		$user['nameScreen'] = $user['name_screen'];

		if ( $user_id = JWSns::CreateUser($user) )
		{
			JWLogin::Login( $user_id );
			
			/* for invitation */
			$invitation_id	= JWSession::GetInfo('invitation_id');
			if ( isset($invitation_id) )
				JWSns::FinishInvitation($user_id, $invitation_id);

			$inviter_id = JWSession::GetInfo('inviter_id');
			if ( isset($inviter_id) )
				JWSns::FinishInvite($user_id, $inviter_id);
			/* end invitation */

			JWTemplate::RedirectToUrl( '/wo/account/regok' );
		}
		else
		{
			$error_string = "<li>系统出现故障、注册用户失败，请稍后再来</LI>";
		}
	}

	if ( $error_string )
	{
		JWSession::SetInfo( 'notice', "<B>提交表单时发生以下错误：</B><OL>$error_string</OL>" );
	}
}
?>

<?php JWTemplate::html_doctype(); ?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php 
JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
)); 
?>
</head>

<body class="account" id="create">

<?php JWTemplate::header('/wo/account/create') ?>
<?php JWTemplate::ShowActionResultTipsMain(); ?>

<div id="container">
<p class="top">快速注册</p>
<div id="wtMainBlock">
<div class="leftdiv">
<p class="bluebold16">是否已经用手机、MSN、QQ或Gtalk叽歪过了呢？</p>

<p>如果是，请发送<span class="orange12">gm+空格+新用户名</span>，到相应的短信号码或者机器人上来设置用户名<br />例如：gm 阿朱</p>
<p>再发送<span class="orange12">mima+空格+新密码</span>，来设置密码<br />例如：mima abc123 </p>
</div><!-- leftdiv -->
<div class="rightdiv">
<fieldset>
<form id="f" action="/wo/account/create" method="post" class="validator">
<div id="wtRegist">

<ul>
	<!-- email -->
	<li class="box1">Email</li>
	<li class="box2">
		<input id="user_email" type="text" name="user[email]" value="<?php echo @$user['email'];?>" ajax="Email" alt="Email"/>
		<i></i>
	</li>
	<li class="box3">用于接收通知和找回密码，我们不会对外公开</li>

	<!-- pass -->
	<li class="box1">密码</li>
	<li class="box2">
		<input id="user_pass" type="password" name="user[pass]" alt="密码" minlength="6" maxlength="16" />
		<i></i>
	</li>
	<li class="box3" style="height:0px;"></li>

	<!-- confirm pass -->
	<li class="box1">确认密码</li>
	<li class="box2">
		<input id="user_pass_confirm" type="password" name="user[pass_confirm]" alt="确认密码" compare="user_pass" minlength="6" maxlength="16" onblur="JWValidator.onPassBlur('user_pass_confirm');"/>
		<i></i>
	</li>
	<li class="box3">至少6个字符</li>

	<!-- username -->
	<li class="box1">用户名</li>
	<li class="box2">
		<input id="user_name_screen" style="display:inline;" name="user[name_screen]" size="30" type="text" minlength="2" maxlength="16" value="<?php echo @$user['name_screen'];?>" alt="用户名" ajax="NameScreen"/>
		<i></i>
	</li>
	<li class="box3">中文两字以上，英文或数字至少4个字符</li>

	<!-- button submit -->
	<li class="box3">
		<input type="submit" id="reg" class="submitbutton" value="完成注册" />
	</li>

	<!-- tos -->
	<li class="box4">
		<input type="checkbox" name="read_and_accept" value="true" checked="true" />已阅读并接受<span class="orange12"><a href="http://help.jiwai.de/Tos">服务条款</a></span>
	</li>
	<div style="clear:both;"></div>
</ul>
</div>
</form>
</fieldset>
</div>
<!-- rightdiv -->
</div><!-- #wtMainBlock -->

<?php JWTemplate::container_ending(); ?>
</div><!-- #container -->

<?php JWTemplate::footer(); ?>
</body>
</html>
