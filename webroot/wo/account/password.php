<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$user_info = JWUser::GetCurrentUserInfo();
$new_user_info = @$_POST['user'];

$is_reset_password = JWSession::GetInfo('reset_password', false);
$is_web_user = JWUser::IsWebUser($user_info['idUser']);

$outInfo = $user_info;

/** check if reset_password */

if ( $is_web_user && !$is_reset_password )
{
	$verify_corrent_password = true;
}
else
{
	$verify_corrent_password = false;
}

if ( isset($_POST['password']) ) {
	if ( isset($_POST['password']) )
	{
		$current_password = trim( @$_POST['current_password'] );
		$password = trim( @$_POST['password'] );
		$password_confrim = trim( @$_POST['password_confrim'] );
		if ( $verify_corrent_password && (	
					empty($current_password) 
					|| empty($password)
					|| empty($password_confrim) 
					))
		{
			$error_html = "<li>请完整填写三处密码输入框</li";
		}

		if ( $password !== $password_confrim )
		{
			$error_html .= "<li>两次输入密码不一致！请重新输入</li>";
		}

		if ( $verify_corrent_password && ! JWUser::VerifyPassword($user_info['id'], $current_password) )
		{
			$error_html .= "<li>当前密码输入错误，请重新输入</li>";
		}
	}

	/*
	 * Update User Databse
	 */
	if ( empty($error_html) )
	{
		if ( ! JWUser::ChangePassword($user_info['id'], $password_confrim) )
		{
			JWSession::SetInfo('error', '密码修改失败，请稍后再试。');
		}
		else
		{
			if ( !$is_web_user )
				JWUser::SetWebUser($user_info['idUser']);

			// 重设密码成功，现在清理掉重设密码的标志
			if ( $is_reset_password	)
				JWSession::GetInfo('reset_password');

			JWSession::SetInfo('notice', '密码修改成功！');
		}
	}
	else
	{
		JWSession::SetInfo('notice', $error_html);
	}
	JWTemplate::RedirectToUrl();
}

$element = JWElement::Instance();
$param_tab = array( 'now' => 'account_password' );
$param_side = array( 'sindex' => 'account' );
$param_main = array( 'reset_password' => $is_reset_password );
?>
<?php $element->html_header();?>
<?php $element->common_header();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div class="f">
		<?php $element->block_headline_minwo();?>
		<?php $element->block_tab($param_tab);?>
		<?php $element->block_account_password($param_main);?>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- lefter end -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_setting($param_side);?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter end -->

<div class="clear"></div>
</div><!-- container end -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
