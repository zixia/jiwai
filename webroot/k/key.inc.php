<?php 
require_once( dirname(__FILE__) . '/../../jiwai.inc.php');
$element = JWElement::Instance();

$extra = array();
if ( in_array(strtolower($_REQUEST['f']), array('null'))) { 
	$extra['order_field'] = null;
	$value['f'] = $_REQUEST['f'];
}else{
	$extra['order_field'] = 'time';
	$value['f'] = 'time';
}

if ( $_REQUEST['o'] == 'asc' ) {
	$extra['order'] = false;
	$value['o'] = 'asc';
}

if ( true ) { 
	$q = $key;
	$page = isset($_GET['page']) ? abs(intval($_GET['page'])) : 1;
	if ( !$q ) { 
		$result = array( 'count' => 0, 'error' => 0, );
	} else {
		$result = JWSearch::SearchStatus($q, $page, 20, $extra);
	}
	$guessword = ( $page==1 ) ? 
		JWSearchWord::GuessWord($q, $result['count']) : null;
}

$param_tab = array( 
		'tab' => array( 'all' => array('大家的'), ),
		'now' => 'key_all',
		);
$param_main = array(
		'result' => $result,
		);
$param_head = array(
		'key' => $key,
		'guessword' => $guessword,
		'sovalue' => $value,
		'sourl' => "/k/{$key}/",
		'count' => $result['count'],
		);
$param_side = array(
		'key' => $key,
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
			<?php $element->block_headline_key($param_head);?>
			<?php $element->block_tab($param_tab);?>
			<?php $element->block_statuses_search($param_main);?>
		</div>
		<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
	</div>
</div>
<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_keyhead($param_side);?>
		<?php $element->side_whom_follow_key($param_side);?>
		<?php $element->side_keyfollowing($param_side);?>
		<?php $element->side_searchadvance();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div>

<div class="clear"></div>
</div>

<?php $element->common_footer();?>
<?php $element->html_footer();?>
