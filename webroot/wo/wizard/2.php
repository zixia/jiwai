<?php
require_once('../../../jiwai.inc.php');
$element = JWElement::Instance();
$param_tab = array('tabtitle' => 'QQ与MSN,Gtalk聊天');
$param_side = array('windex' => 2 );
?>

<?php $element->html_header();?>
<?php $element->common_header();?>
<div id="container">
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div id="leftBar" >
		<?php $element->block_headline_tips();?>
		<?php $element->block_tab($param_tab);?>
		<div class="f">
			<?php $element->block_wizard_2();?>
		</div>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- lefter end -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_wizard_in($param_side);?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter end -->

<div class="clear"></div>
</div><!-- container end -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
