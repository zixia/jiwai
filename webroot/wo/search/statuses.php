<?php
require_once( '../../../jiwai.inc.php');

//for search
$value = $extra = $result = array();
$_GET['q'] = $value['q'] = isset($_GET['q']) ? $_GET['q'] : $_POST['q'];
$extra = array();
if ( $_REQUEST) {
	$no_guess = false;
	if ( $_REQUEST['s'] ) { 
		$extra['in_device'] = $_REQUEST['s']; 
		$value['s'] = $_GET['s'] = $_REQUEST['s'];
		$no_guess = true;
	}
	if ( $_REQUEST['u'] ) { 
		$extra['in_user'] = $_REQUEST['u']; 
		$value['u'] = $_GET['u'] = $_REQUEST['u'];
		$no_guess = true;
	}
	if ( $_REQUEST['t'] ) {
		$extra['in_type'] = $_REQUEST['t'];
		$value['t'] = $_GET['t'] = $_REQUEST['t'];
		$no_guess = true;
	}
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
}
if ( true ) { 
	$q = isset($_GET['q']) ? $_GET['q'] : null;
	$page = isset($_GET['page']) ? abs(intval($_GET['page'])) : 1;
	if ( !$q ) { 
		$result = array( 'count' => 0, 'error' => 0, );
	} else {
		$result = JWSearch::SearchStatus($q, $page, 20, $extra);
	}
	$guessword = ( $no_guess==false && $page==1 ) ? 
		JWSearchWord::GuessWord($q, $result['count']) : null;
}

//end search

$element = JWElement::Instance();
$sourl = "?q=" . urlEncode($_GET['q']) ."&u=" . urlEncode($_GET['u']) ."&s=" . urlEncode($_GET['s']);
$block_tab = array(
		'all' => array('全部', "{$sourl}"),
		'mms' => array('照片', "{$sourl}&t=mms"),
		'sig' => array('签名', "{$sourl}&t=sig"),
		'vote' => array('投票', "{$sourl}&t=vote"),
		);
$now = in_array(strtolower(@$_GET['t']), array('all','mms','sig','vote')) ? strtolower($_GET['t']) : 'all';
$sourl = $block_tab[$now][1];
$param_tab = array( 
		'now' => "search_{$now}",
		'tab' => $block_tab,
		);
$param_head = array(
		'count' => $result['count'],
		'sourl' => $sourl,
		'sovalue' => $value,
		'guessword' => $guessword,
		);
$param_search = array(
		'extra' => $extra,
		'value' => $value,
		'result' => $result,
		);
?>
<?php $element->html_header();?>
<?php $element->common_header();?>

<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div class="f">
		<?php $element->block_headline_search($param_head);?>
		<?php $element->block_tab($param_tab);?>
		<?php $element->block_statuses_search($param_search);?>
		<?php $element->block_rsslink();?>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- end lefter -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_searchadvance();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter -->

<div class="clear"></div>
</div><!-- container -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
