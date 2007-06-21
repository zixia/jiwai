<?php
require_once('../../jiwai.inc.php');
JWTemplate::html_doctype();

//JWDebug::instance()->trace($_REQUEST);

$err = '';

if ( array_key_exists('username_or_email',$_REQUEST) )
{
	$username_or_email  = $_REQUEST['username_or_email'];
	$password			= $_REQUEST['password'];

	if ( JWOpenid::IsPossibleOpenid($username_or_email) )
	{
		JWOpenidConsumer::AuthRedirect($username_or_email);
		// if it return, mean $username_or_email is not a valid openid url.
	}

	$idUser = JWUser::GetUserFromPassword($username_or_email, $password);

	if ( $idUser )
	{
		if ( isset($_REQUEST['remember_me']) )
			$remember_me = true;
		else
			$remember_me = false;
		JWLogin::Login($idUser, $remember_me);


		$invitation_id = @$_REQUEST['invitation_id'];

		if ( isset($invitation_id) )
		{
			JWInvitation::LogRegister($invitation_id, $idUser);


			$invitation_rows		= JWInvitation::GetInvitationDbRowsByIds(array($invitation_id));
			$inviter_id				= $invitation_rows[$invitation_id]['idUser'];

			$reciprocal_user_ids	= JWInvitation::GetReciprocalUserIds($invitation_id);
			array_push( $reciprocal_user_ids, $inviter_id );

			// 互相加为好友
			JWSns::CreateFriends( $idUser, $reciprocal_user_ids, true );
		}

		if ( isset($_SESSION['login_redirect_url']) ){
			header("Location: " . $_SESSION['login_redirect_url']);
			unset($_SESSION['login_redirect_url']);
		}else{
			header("Location: /wo/");
		}
		exit(0);
	}else{
		$err = '用户名/Email 与密码不匹配。<small><a href="/wo/account/resent_password">忘记密码？</a>.';
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="login">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<div class="separator"></div>

<div id="container">
	<div id="content">
		<div id="wrapper">

			<h2>登录到叽歪de</h2>

<?php	

JWTemplate::ShowActionResultTips();

if ( !empty($err) ){
	echo "<p class='notice'> $err </p>\n";
}
?>

<p>
	如果您已经通过手机使用了叽歪de服务，<a href="/wo/account/complete">请来这里</a>。我们将帮助您在网站上注册。
</p>

<form method="post" name="f">
  <fieldset>
  	<table cellspacing="0">
  		<tr>
  			<th><label for="username_or_email">帐号 / Email</label></th>
  			<td><input id="username_or_email" class="openid_login" name="username_or_email" type="text" /></td>
  		</tr>
  		<tr>
  			<th><label for="password">密码</label></th>
  			<td><input id="password" name="password" type="password" /> <small><a href="/wo/account/resend_password">忘记?</a></small></td>
  		</tr>
  		<tr>
  			<th></th>
  			<td><input id="remember_me" name="remember_me" type="checkbox" value="1" checked /> <label for="remember_me" class="inline">记住我</label></td>
  		</tr>
  		<tr>
  			<th></th>
  			<td><input name="commit" type="submit" value="登录" /></td>
  		</tr>
  	</table>
  </fieldset>
</form>

<script type="text/javascript">
  document.getElementById('username_or_email').focus();
</script>


		</div><!-- wrapper -->
	</div><!-- content -->

<?php 
$arr_menu = array (	array('register'	, null)
				);

JWTemplate::sidebar( $arr_menu );
?>
	
</div><!-- #container -->
<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
