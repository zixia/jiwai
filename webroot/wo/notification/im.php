 <?php
 require_once('../../../jiwai.inc.php');
 JWLogin::MustLogined(false);
 
 $user_info  = JWUser::GetCurrentUserInfo();
 $user_setting = JWUser::GetNotification($user_info['id']);

 if ( isset($_POST['user']) )
 {
	$user_new_setting = isset($_POST['user']) ? $_POST['user'] : array();
	$user_setting['auto_nudge_me'] = isset($user_new_setting['auto_nudge_me']) ? $user_new_setting['auto_nudge_me'] : 'N';
	$user_setting['is_receive_offline'] = isset($user_new_setting['is_receive_offline']) ? $user_new_setting['is_receive_offline'] : 'N';
	$user_setting['allowSystemSms'] = !empty($user_new_setting['allowSystemSms']) ? 'Y' : 'N';
	$user_setting['isNotReceiveNight'] = !empty($user_new_setting['isNotReceiveNight']) ? 'Y' : 'N';
	$user_setting['notReceiveTime1'] = $user_new_setting['notReceiveTime1'].':00:00';
	$user_setting['notReceiveTime2'] = $user_new_setting['notReceiveTime2'].':00:00';
	$user_setting['allowReplyType'] = $user_new_setting['allowReplyType'];
 
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
$param_tab = array( 'now' => 'notice_im' );
$param_side = array( 'sindex' => 'notice' );
$param_main = array( 
		'user_setting' => $user_setting,
		'time_split' => range(0,23),
		'time_one' => intval($user_setting['notReceiveTime1']),
		'time_two' => intval($user_setting['notReceiveTime2']),
		'replytype' => array(
			'everyone' => '接收我关注的人给任何人的回复',
			'each' => '接收我关注的人们相互之间的回复',
			'mine' => '只接收给我的回复',
			'none' => '不接收任何回复',
			),
		);
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
			<?php $element->block_notification_im($param_main);?>
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
