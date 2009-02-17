<?php
$element = JWElement::Instance();
$param_tab = array( 'now' => 'user_my' );
?>
<?php $element->html_header();?>
<?php $element->common_header();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<?php $element->block_headline_user();?>
	<div>
		<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
		<div class="f">
			<?php $element->block_tab($param_tab);?>
			<?php $element->block_statuses_user();?>
			<?php $element->block_rsslink();?>
		</div>
		<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
	</div>
</div>

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_user();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div>

<div class="clear"></div>
</div>

<?php $element->common_footer();?>
<?php $element->html_footer();?>
