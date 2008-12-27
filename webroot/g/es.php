<?php
require_once('../../jiwai.inc.php');

$esuser_id = 165657;

$total = JWStatus::GetStatusNum($esuser_id);
$pager = new JWPager(array('rowCount'=>$total, 'pageSize'=>10,));
$status_data = JWDB_Cache_Status::GetStatusIdsFromUser( $esuser_id, $pager->pageSize, $pager->offset);
$status_rows = JWDB_Cache_Status::GetDbRowsByIds($status_data['status_ids']);

$events = array();
$picture_ids = array();
foreach( $status_rows AS $one ) {
	list($e, $u, $t, $a) = preg_split('/(:|：)/', $one['status'], 4);
	$u = JWUser::GetUserInfo($u);
	$events[] = array(
		'event' => $e,
		'user' => $u,
		'time' => $t,
		'address' => $a,
	);
	$picture_ids[] = $u['idPicture'];
}
$picture_urls = JWPicture::GetUrlRowByIds($picture_ids);

$element = JWElement::Instance();
$param_main = array(
		'events' => $events,
		'picture_urls' => $picture_urls,
		);
$param_pager = array(
		'pager' => $pager,
		);
?>

<?php $element->html_header();?>
<?php $element->common_header();?>

<div id="container">
<?php $element->wide_notice();?>
<div id="lefter_g">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div id="leftBar" >
		<div class="f">
			<?php $element->block_g_es($param_main);?>
			<div class="block">
				<?php $element->block_pager($param_pager);?>
			</div>
		</div>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- end lefter -->

<div id="righter_g">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_g_es();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter -->

<div class="clear"></div>
</div><!-- container -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
