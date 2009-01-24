<?php
$pattern = '/\/thread\/(\d+)\/?(\d*)$/i';
$script_uri = $_SERVER['SCRIPT_URI'];
if ( false==preg_match($pattern, $script_uri, $matches) )
	JWTemplate::RedirectTo404NotFound();

list($_, $thread_id, $status_id) = $matches;

$element = JWElement::Instance();
$count_reply = JWDB_Cache_Status::GetCountReply($thread_id);
$param_head = array(
		'thread_id' => $thread_id,
		'status_id' => $status_id,
		'nofollower' => true,
		);
$param_tab = array( 
		'tabtitle' => "目前已有{$count_reply}条回复",
		);
?>
<?php $element->html_header();?>
<?php $element->common_header();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="mar_b20">
		<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
		<div class="f">
			<?php $element->block_headline_thread($param_head);?>
		</div>
		<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
	</div>

	<div>
		<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
		<div class="f">
			<?php $element->block_tab($param_tab);?>
			<?php $element->block_statuses_thread($param_head);?>
		</div>
		<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
	</div>
</div>

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_thread();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div>

<div class="clear"></div>
</div>

<?php $element->common_footer();?>
<?php $element->html_footer();?>
