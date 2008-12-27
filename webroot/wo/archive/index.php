<?php
require_once( '../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$element = JWElement::Instance();
$param_tab = array( 'now' => 'wo_my' );
?>
<?php $element->html_header();?>
<?php $element->common_header_wo();?>

<div id="container">
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div id="leftBar" >
		<?php $element->block_headline_wo();?>
		<?php $element->block_tab($param_tab);?>
		<div class="f">
			<?php $element->block_statuses_wo_archive();?>
			<?php $element->block_rsslink();?>
		</div>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- end lefter -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_wo_request_in();?>
		<?php $element->side_wo_hi();?>
		<?php $element->side_announcement();?>
		<div class="line mar_b8"></div>
		<?php $element->side_recent_vistor();?>
		<?php $element->side_whom_me_follow(array('url'=>'wo'));?>
		<?php $element->side_block_user();?>
		<div class="gra_input"><input type="text" value="QQ  MSN  Emai l id..." onFocus="clearValue(this)" onBlur="searchValue(this,'QQ  MSN  Emai l id...')" /> <input type="button" value="谁在叽歪" class="def_btn" /></div>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter -->

<div class="clear"></div>
</div><!-- container -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
