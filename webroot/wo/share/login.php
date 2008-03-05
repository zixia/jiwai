<?php
require_once('../../../jiwai.inc.php');

$n = $p = null;
if( $_POST ) {
	extract( $_POST, EXTR_IF_EXISTS );
	$idUser = JWUser::GetUserFromPassword($n, $p);
	if ( $idUser ) {
		if ( isset($_REQUEST['remember_me']) && $_REQUEST['remember_me'] )
			$remember_me = true;
		else
			$remember_me = false;
		JWLogin::Login($idUser, $remember_me);

		if ( isset($_SESSION['login_redirect_url']) ){
			header("Location: " . $_SESSION['login_redirect_url']);
			unset($_SESSION['login_redirect_url']);
		}else{
			header("Location: /wo/");
		}
		
		exit;
	}
}

JWTemplate::html_doctype();
?>
<html>
<head>
<?php JWTemplate::html_head(); ?>
</head>

<body style="margin:0;">
<div id="wtCollectionMain">
<h1>登录到叽歪网</h1>
<div id="tips">
	<p style="margin-top:-14px;"><a target="_blank" href="/wo/account/create"><img src="<?php echo JWTemplate::GetAssetUrl('/images/org-frist.gif'); ?>" width="156" height="68" border="0" class="regnow" tabindex="6"/></a></p>
</div>
<form id="f" method="post">
	<p>用户名：<input name="n" type="text" alt="用户名" check="null" value="<?php echo $n; ?>"class="inputStyle" /></p>
	<p>密<span class="mar">码</span>：<input name="p" alt="密码" check="null" type="password" class="inputStyle" /></p>
	<p><a href="#" class="pad1">忘记密码了？</a></p>
	<p><span class="pad2"><input type="radio" name="remember_me" value="0" /> 每次都重新登录</span></p>
	<p><span class="pad2"><input type="radio" checked name="remember_me" value="1" /> 一个月内自动登录</span></p>
	<p class="po"><input name="Submit" type="button" onClick="if(JWValidator.validate('f')) $('f').submit();return false;" class="submitbutton" value="登 陆" /></p>
</form>
</div>

<script>
	JWValidator.init( 'f' );
</script>
</body>
</html>
