<?php
require_once( '../../jiwai.inc.php');
$is_logined = JWLogin::IsLogined();

$element = JWElement::Instance();
if ( $is_logined ) {
	$param_tab = array( 'now' => 'wo_public' );
} else {
	$param_tab = array( 'tabtitle' => '大家的叽歪' );
}
?>
<?php $element->html_header();?>
<?php $element->common_header();?>
<div id="container">
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div id="leftBar" >
	<?php 
		if ($is_logined)
			$element->block_headline_wo();
		else
			$element->block_headline_tips();
	?>
		<?php $element->block_tab($param_tab);?>
		<div class="f">
			<?php $element->block_statuses_public();?>
			<?php $element->block_rsslink();?>
		</div>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- end lefter -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_wo_hi();?>
		<?php $element->side_announcement();?>
		<?php $element->side_recent_vistor();?>
		<?php $element->side_searchuser();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter -->

<div class="clear"></div>
</div><!-- container -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
