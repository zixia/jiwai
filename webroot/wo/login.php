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
		JWOpenid_Consumer::AuthRedirect($username_or_email);
		// if it return, mean $username_or_email is not a valid openid url.
	}

	$idUser = JWUser::GetUserFromPassword($username_or_email, $password);

	if ( $idUser )
	{
		if ( isset($_REQUEST['remember_me']) && $_REQUEST['remember_me'] )
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
		$err = '用户名/Email 与密码不匹配。<small><a href="/wo/account/resend_password">忘记密码？</a>.';
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="create">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div id="container">

<h2>登陆到叽歪de</h2>

<?php
if ( !empty($err) ){
            echo "<p class='notice'> $err </p>\n";
}
?>

<form id="f" method="POST" action="/wo/login">
<table width="550" border="0" cellspacing="15" cellpadding="0">
    <tr>
        <td width="70" align="right">用户名：</td>
        <td width="240"><input type="text" name="username_or_email" /></td>
        <td>
            <a href="/wo/account/create"><img src="<?php echo JWTemplate::GetAssetUrl('/images/org-frist.gif'); ?>" width="156" height="68" border="0" class="regnow" /></a>
        </td>
    </tr>
    <tr>
        <td align="right">密　码：</td>
        <td><input type="password" name="password" /></td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td><a href="/wo/account/resend_password">忘记密码了？</a></td>
        <td>&nbsp;</td>
    </tr>
</table>
<ul class="choise">
    <li>
        <input id="every_re" name="remember_me" type="radio" value="0" /> <label for="every_re">每次都重新登陆<label>
    </li>
    <li>
        <input id="month_re" name="remember_me" type="radio" value="1" /> <label for="month_re">一个月内自动登陆</label>
    </li>
    <!--li>
        <input id="never_re" name="remember_me" type="radio" value="2" checked/> <label for="never_re">永久自动登陆</label>
    </li-->
    <li style="margin-top:20px;">
        <div>
            <a onclick="$('f').submit();return false;" class="button" href="#"><img src="<?php echo JWTemplate::GetAssetUrl('/images/org-text-login.gif'); ?>" alt="登录" /></a>
        </div>            
    </li>
</ul>
</form>

<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>          
</div>
<!-- #container -->

<script type="text/javascript">
  document.getElementById('username_or_email').focus();
</script>


<?php JWTemplate::footer() ?>

</body>
</html>
