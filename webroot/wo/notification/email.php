<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$user_info = JWUser::GetCurrentUserInfo();
$user_setting = JWUser::GetNotification($user_info['id']);

if ( isset($_POST['user']) )
{
	$user_new_setting = isset($_POST['user']) ? $_POST['user'] : array();
	$user_setting['send_new_direct_text_email'] = isset($user_new_setting['send_new_direct_text_email']) ? $user_new_setting['send_new_direct_text_email'] : 'N';
	$user_setting['allow_system_mail'] = isset($user_new_setting['allow_system_mail']) ? $user_new_setting['allow_system_mail'] : 'N';

	$user_setting['send_new_friend_email'] = isset($user_new_setting['send_new_friend_email']) ? $user_new_setting['send_new_friend_email'] : 'N';

	if ( ! JWUser::SetNotification($user_info['id'], $user_setting) )
	{
		JWSession::SetInfo('error', '通知设置由于系统故障未能保存成功，请稍后再试。');
	}
	else
	{
		JWSession::SetInfo('notice', '通知设置保存成功！');
	}

	JWTemplate::RedirectToUrl();
}

$element = JWElement::Instance();
$param_tab = array( 'now' => 'notice_email' );
$param_side = array( 'sindex' => 'notice' );
$param_main = array( 'user_setting' => $user_setting );
?>

<?php $element->html_header();?>
<?php $element->common_header_wo();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div id="leftBar" >
		<?php $element->block_headline_minwo();?>
		<?php $element->block_tab($param_tab);?>
		<div class="f">
			<?php $element->block_notification_email($param_main);?>
		</div>
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
