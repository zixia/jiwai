<?php
require_once('../../jiwai.inc.php');
JWTemplate::html_doctype();

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

	$user_id = JWUser::GetUserFromPassword($username_or_email, $password);

	if ( $user_id )
	{
		if ( isset($_REQUEST['remember_me']) && $_REQUEST['remember_me'] )
			$remember_me = true;
		else
			$remember_me = false;
		JWLogin::Login($user_id, $remember_me);


		$invitation_id = @$_REQUEST['invitation_id'];

		if ( isset($invitation_id) )
		{
			JWInvitation::LogRegister($invitation_id, $user_id);


			$invitation_rows		= JWInvitation::GetInvitationDbRowsByIds(array($invitation_id));
			$inviter_id				= $invitation_rows[$invitation_id]['idUser'];

			$reciprocal_user_ids	= JWInvitation::GetReciprocalUserIds($invitation_id);
			array_push( $reciprocal_user_ids, $inviter_id );

			// 互相加为好友
			JWSns::CreateFollower( $user_id, $reciprocal_user_ids, true );
		}

		if ( isset($_SESSION['login_redirect_url']) )
        {
			header("Location: " . $_SESSION['login_redirect_url']);
			unset($_SESSION['login_redirect_url']);
		}
        else
        {
			header("Location: /wo/");
		}
		exit(0);
	}
    else
    {
		$err = '用户名/Email 与密码不匹配。<a href="/wo/account/resend_password">忘记密码？</a>';
	}

	JWSession::SetInfo('notice', $err);
}


?>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
));?>
</head>

<body class="account" id="create">

<?php JWTemplate::header('/wo/login') ?>
<?php JWTemplate::ShowActionResultTipsMain(); ?>

<div id="container">
    <p class="top">登录到叽歪de</p>
    <div id="wtMainBlock">
        <div class="leftdiv">
            <span class="bluebold16">是否已经用手机、MSN、QQ或Gtalk叽歪过了呢？</span>
            <p>如果是，请发送<span class="orange12">gm+空格+想要用户名</span>，到相应的短信号码或者机器人上来设置用户名<br />例如：gm 阿朱</p>

            <p>再发送<span class="orange12">pass+空格+密码</span>，来设置密码<br />例如：pass abc123 </p>
        </div><!-- leftdiv -->
        <div class="rightdiv">
            <div class="login">
                <form id="f" action="/wo/login" enctype="multipart/form-data" method="post">
                    <p class="black14">用户名<input id="username_or_email" name="username_or_email" type="text" class="inputStyle" style="width:270px " /></p>
                    <div style="overflow: hidden; clear: both; height:5px; line-height: 1px; font-size: 1px;"></div>
                    <p class="black14">密<span class="mar">码</span><input id="password" type="password" name="password" alt="密码" minlength="6" maxlength="16" class="inputStyle" style="width:270px "/></p>

                    <p style="padding-left:50px;"><a href="/wo/account/resend_password">忘记密码？</a></p>
                    <p class="po"><input type="radio" id="every_re" name="remember_me" value="0" />每次都重新登录</p>
                    <p class="po"><input type="radio" id="month_re" name="remember_me" value="1" checked="checked" />一个月内自动登录</p>
                    <p style="padding:5px 0 0 50px;"><input name="Submit" type="submit" class="submitbutton" value="登 录" />
                </form>
                <div style="overflow: hidden; clear: both; height: 70px; line-height: 1px; font-size: 1px;"></div>
            </div><!-- login end -->

        </div><!-- rightdiv end -->
    </div><!-- #wtMainBlock end -->
    <div style="overflow: hidden; clear: both; height: 10px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container end -->
<script type="text/javascript">
    document.getElementById('username_or_email').focus();
</script>
<?php JWTemplate::footer(); ?>
</body>
</html>

