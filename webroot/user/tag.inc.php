<?php
$element = JWElement::Instance();

$param_main = array(
	'author_user_id' => $g_page_user_id,
	'tag_ids' => $tag_row['id'],
);
$param_tab = array(
	'tabtitle' => "此人的[{$tag_name}]话题",
);

$param_side = array(
	'tag' => $tag_row,
);
?>

<?php $element->html_header();?>
<?php $element->common_header();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div id="leftBar" >
		<?php $element->block_headline_minwo();?>
		<?php $element->block_tab($param_tab);?>
		<div class="f">
			<?php $element->block_statuses_tag($param_main);?>
			<?php $element->block_rsslink();?>
		</div>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div>
<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_taghead($param_side);?>
		<?php $element->side_usertaglist();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div>

<div class="clear"></div>
</div>

<?php $element->common_footer();?>
<?php $element->html_footer();?>
