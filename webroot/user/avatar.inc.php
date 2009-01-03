<?php
$element = JWElement::Instance();
$param_main = array(
	'thread_id' => $status_id,
	'noupdater' => true,
);
?>
<?php $element->html_header($param_head);?>
<?php $element->common_header();?>
<div id="container">
<?php $element->wide_notice();?>
<div class="wht">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div class="f">
		<?php $element->block_user_avatar();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div>
<div class="clear"></div>
</div>

<?php $element->common_footer();?>
<?php $element->html_footer();?>
