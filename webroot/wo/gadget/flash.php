<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$user_id	= JWLogin::GetCurrentUserId();
$user_info = JWUser::GetUserInfo($user_id);

if( $user_info['protected'] == 'Y')
{
	$sub_menu = 'flash';
	require_once( './noperm.php' );
	exit;
}

$param_tab = array( 'tabtitle' => 'Flash窗口贴' );
$param_side = array( 'gadget' => 'flash' );
$element = JWElement::Instance();
?>

<?php $element->html_header();?>
<?php $element->common_header();?>

<div id="container">
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div id="leftBar" >
		<?php $element->block_headline_minwo();?>
		<?php $element->block_tab($param_tab);?>
		<div class="f">
			<?php $element->block_gadget_flash();?>
		</div>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- end lefter -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_wo_gadget($param_side);?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter -->

<div class="clear"></div>
</div><!-- container -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
