<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$current_user_id = JWLogin::GetCurrentUserId();
$bindother = JWBindOther::GetBindOther($current_user_id);
$user_devices = JWDevice::GetDeviceRowByUserId($current_user_id);
$facebook = @$user_devices['facebook'];

//binds
$others = array('facebook', 'twitter', 'fanfou', 'yiqi');
$mothers = array_keys($bindother);
if( $facebook ) $mothers[] = 'facebook';
$mothers = array_diff($other, $mothers);

$element = JWElement::Instance();
$param_tab = array( 'now' => 'devices_other' );
$param_side = array( 'sindex' => 'bind' );
$param_main = array(
	'facebook' => $facebook,
	'bindother' => $bindother,
	'others' => $others,
	'mothers' => $mothers,
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
			<?php $element->block_devices_other($param_main);?>
		</div>
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
