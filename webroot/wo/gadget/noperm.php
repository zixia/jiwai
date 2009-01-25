<?php
require_once('../../../jiwai.inc.php');
if( isset($_POST) && isset($_POST['protected']) ) 
{
	$protected = $_POST['protected'];
	$array_info = array();
	$array_info['protected'] = $protected;
	JWUser::Modify($user_info['id'],$array_info);
	JWTemplate::RedirectBackToLastUrl();
}
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
		<?php $element->block_gadget_noperm();?>
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
