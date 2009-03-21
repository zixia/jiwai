<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$current_user_info = JWUser::GetCurrentUserInfo();
$current_user_id = $current_user_info['id'];
$user_devices = JWDevice::GetDeviceRowByUserId( $current_user_id );
unset($user_devices['sms']);
unset($user_devices['facebook']);

$supported_devices = array('qq', 'msn', 'gtalk', 'fetion', 'skype', 'yahoo', 'aol', 'newsmth', 'jabber', 'xiaonei');

if ( isset($_REQUEST['_shortcut']) ) {
    list($type, $address) = explode($_REQUEST['_shortcut'], ':');
}

$element = JWElement::Instance();
$param_tab = array( 'now' => 'devices_im' );
$param_side = array( 'sindex' => 'bind' );
$param_main = array(
	'supported_devices' => $supported_devices,
	'user_devices' => $user_devices,
	'sig_devices' => JWDevice::$signatureRecordArray,
);
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
		<?php $element->block_devices_im($param_main);?>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- end lefter -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_setting($param_side);?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter -->

<div class="clear"></div>
</div><!-- container -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
