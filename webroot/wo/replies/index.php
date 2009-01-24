<?php
require_once( '../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$element = JWElement::Instance();
$param_tab = array( 'now' => 'wo_reply' );
?>
<?php $element->html_header();?>
<?php $element->common_header_wo();?>

<div id="container">
<div id="lefter">

	<div class="mar_b20">
		<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
		<div class="f">
			<?php $element->block_headline_wo();?>
		</div>
		<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
	</div>

	<div>
		<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
		<div class="f">
			<?php $element->block_tab($param_tab);?>
			<?php $element->block_statuses_wo_replies();?>
			<div style="clear"></div>
			<?php $element->block_rsslink();?>
		</div>
		<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
	</div>
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
		<?php $element->side_searchuser();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter -->

<div class="clear"></div>
</div><!-- container -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
