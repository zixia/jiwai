<?php
require_once(dirname(__FILE__).'../../../../jiwai.inc.php');
JWLogin::MustLogined(false);
list($_, $oblockid) = @explode('/', @$_REQUEST['pathParam']);
$current_user_id = JWLogin::GetCurrentUserId();
$device_row = JWDevice::GetDeviceRowByUserId($current_user_id);

if (isset($_POST['commit_info'])) {
	$message = trim(@$_POST['message']);
	$type = @$_POST['type'];
	$device = @$_POST['device'];
	$date = @$_POST['date'];
	$time = @$_POST['time'];
	$time = "{$date} {$time}";
	$meta_info = array(
			'time' => strtotime($time),
			'number' => $device_row[$device]['address'],
			);

	$is_succ = JWFeedBack::Create($current_user_id, $device, $type, $message, $meta_info);

	if( false == $is_succ ) {
		JWSession::SetInfo('error', '对不起，举报失败。');
	} else {
		JWSession::SetInfo('notice', '你的反馈提交成功，我们会尽快处理。');
	}
	JWTemplate::RedirectToUrl( '/wo/feedback/#info' );
}

if (isset($_POST['commit_com'])) {
	$user = trim(@$_POST['feed_user']);
	$message = trim(@$_POST['message']);
	$type = 'COMPLAIN';
	$meta_info = array(
			'user' => $user,		   
			);
	$device=null;

	$is_succ = JWFeedBack::Create($current_user_id, $device, $type, $message, $meta_info);
	if( false == $is_succ ) {
		JWSession::SetInfo('error', '对不起，举报失败。');
	} else {
		JWSession::SetInfo('notice', '你的举报提交成功，我们会尽快处理。');
	}
	JWTemplate::RedirectToUrl( '/wo/feedback/#com' );
}

$element = JWElement::Instance();
$param_tab = array( 'tabtitle' => '叽歪的反馈' );
$param_main = array(
	'device_row' => $device_row,
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
			<?php $element->block_wo_feedback($param_main);?>
		</div>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- end lefter -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_help();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter -->

<div class="clear"></div>
</div><!-- container -->

<?php $element->common_footer();?>
<?php $element->html_footer(array('oblockid'=>$oblockid));?>
