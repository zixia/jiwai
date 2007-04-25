<?php
require_once('../jiwai.inc.php');

//JWDebug::instance()->trace($_REQUEST);

$err = '';

if ( array_key_exists('username_or_email',$_REQUEST) ){
	if (JWUser::Login($_REQUEST['username_or_email'],$_REQUEST['password'],@$_REQUEST['remember_me'])){
		if ( isset($_SESSION['login_redirect_url']) ){
			header("Location: " . $_SESSION['login_redirect_url']);
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

<?php JWTemplate::html_head() ?>

<body class="account" id="login">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<div class="separator"></div>

<div id="container">
	<div id="content">
		<div id="wrapper">

			<h2>Sign in to JiWai.de</h2>

<?php	
if ( !empty($err) ){
	echo "<p class='notice'> $err </p>\n";
}
?>

<p>
  If you&rsquo;ve been using JiWai.de from your phone,
	<a href="/wo/account/complete">click here</a>
	and we&rsquo;ll get you signed up on the web.
</p>

<form action="/wo/login" method="post" name="f">
  <fieldset>
  	<table cellspacing="0">
  		<tr>
  			<th><label for="username_or_email">Username or Email</label></th>
  			<td><input id="username_or_email" name="username_or_email" type="text" /></td>
  		</tr>
  		<tr>
  			<th><label for="password">Password</label></th>
  			<td><input id="password" name="password" type="password" /> <small><a href="/account/resend_password">Forgot?</a></small></td>
  		</tr>
  		<tr>
  			<th></th>
  			<td><input id="remember_me" name="remember_me" type="checkbox" value="1" /> <label for="remember_me" class="inline">Remember me</label></td>
  		</tr>
  		<tr>
  			<th></th>
  			<td><input name="commit" type="submit" value="Sign In" /></td>
  		</tr>
  	</table>
  </fieldset>
</form>

<script type="text/javascript">
  document.getElementById('username_or_email').focus();
</script>


		</div><!-- wrapper -->
	</div><!-- content -->

<?php JWTemplate::sidebar( array('login', 'register', 'featured') ) ?>
	
</div><!-- #container -->
<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
