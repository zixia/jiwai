<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

define('DEFAULT_GADGET_COUNT', 3);
$user_info   = JWUser::GetCurrentUserInfo();
$user_id = $user_info['id'];
$name_screen = $user_info['nameScreen'];
$name_url = $user_info['nameUrl'];

if( $user_info['protected'] == 'Y')
{
    $sub_menu = 'image';
    require_once( './noperm.php' );
    exit(0);
}

$param_tab = array( 'tabtitle' => '图片窗口贴' );
$param_side = array( 'gadget' => 'image' );
$element = JWElement::Instance();
?>
<?php $element->html_header();?>
<?php $element->common_header();?>

<div id="container">
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div class="f">
		<?php $element->block_headline_minwo();?>
		<?php $element->block_tab($param_tab);?>
		<?php $element->block_gadget_image();?>
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
