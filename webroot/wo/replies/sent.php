<?php
require_once( '../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$element = JWElement::Instance();
$param_tab = array( 'now' => 'wo_sent' );
?>
<?php $element->html_header();?>
<div id="main">
	<?php $element->common_header_wo();?>
	<div id="content">
		<div id="left">
		<?php $element->block_headline_wo();?>
		<?php $element->block_updater();?>
		<?php $element->block_tab($param_tab);?>
		<?php $element->block_statuses_wo_replies();?>
		</div>
		<div id="right">
		<?php $element->side_wo_request_in();?>
		<?php $element->side_wo_hi();?>
		<?php $element->side_wo_leadlink();?>
		<?php $element->side_announcement();?>
		<?php $element->side_recent_vistor();?>
		<?php $element->side_whom_follow_me();?>
		<?php $element->side_block_user();?>
		</div>
		<div class="clear"></div>
	</div>
	<?php $element->common_footer();?>
</div>
<?php $element->html_footer();?>
