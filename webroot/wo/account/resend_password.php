<?php
require_once('../../../jiwai.inc.php');

if ( isset($_REQUEST['email']) )
{
	$email = $_REQUEST['email'];

	if ( JWUser::IsValidEmail($email, true) )
	{
		$user_db_row = JWUser::GetUserInfo($email);
	}
	else
	{
		$notice_html = '哎呀！您输入的邮件地址不合法！';
	}

	if ( false==empty($user_db_row) )
	{
		JWSns::ResendPassword($user_db_row['idUser']);

		$notice_html = "<li>重新设置你密码的说明已经发送到你的邮箱{$email}，请查收。</li>";
		JWSession::SetInfo('notice', $notice_html);

		header("Location: " . JWTemplate::GetConst('UrlLogin') );
		exit(0);
	}

	if (empty($notice_html))
	{
		$notice_html = '哎呀！我们没有找到你的邮件地址！';
	}

	JWSession::SetInfo('notice', $notice_html);
	JWTemplate::RedirectBackToLastUrl('/');
}

$element = JWElement::Instance();
$param_tab = array( 'tabtitle' => '忘记密码了' );
?>

<?php $element->html_header();?>
<?php $element->common_header();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div id="leftBar" >
		<?php $element->block_headline_tips();?>
		<?php $element->block_tab($param_tab);?>
		<div class="f">
			<?php $element->block_resendpassword();?>
		</div>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- lefter end -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_resendpassword();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter end -->

<div class="clear"></div>
</div><!-- container end -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
