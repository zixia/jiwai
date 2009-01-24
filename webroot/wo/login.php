<?php
require_once('../../jiwai.inc.php');
$err = null;
if ( array_key_exists('username_or_email',$_REQUEST) )
{
	$username_or_email  = $_REQUEST['username_or_email'];
	$password			= $_REQUEST['password'];

	if ( JWOpenID::IsPossibleOpenID($username_or_email) )
	{
		JWOpenID::AuthRedirect($username_or_email);
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

			$invitation_rows = JWInvitation::GetInvitationDbRowsByIds(array($invitation_id));
			$inviter_id = $invitation_rows[$invitation_id]['idUser'];
			$reciprocal_user_ids = JWInvitation::GetReciprocalUserIds($invitation_id);
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

$element = JWElement::Instance();
$param_tab = array( 'tabtitle' => '登录到叽歪' );
?>

<?php $element->html_header();?>
<?php $element->common_header();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div class="f">
		<?php $element->block_headline_tips();?>
		<?php $element->block_tab($param_tab);?>
		<?php $element->block_login();?>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- lefter end -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_login();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter end -->

<div class="clear"></div>
</div><!-- container end -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
