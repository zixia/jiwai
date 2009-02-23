<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
$page = $q = null;
extract($_GET, EXTR_IF_EXISTS);
$_GET['q'] = $q = preg_replace('/\\\\/', '', $q);
$curpage = abs(intval($page)) ? abs(intval($page)) : 1;
$perpage = 60;

$current_user_info = JWUser::GetCurrentUserInfo();
$page_user_info	= $current_user_info;

$searched_result = JWSearch::SearchUser($q, $curpage, $perpage);
$searched_ids = $searched_result['list'];
$searched_num = $searched_result['count'];
$pager = new JWPager(array(
			'rowCount' => $searched_num,
			'pageSize' => $perpage,
			'pageNo' => $curpage,
			));

$searched_users = JWDB_Cache_User::GetDbRowsByIds($searched_ids);
$picture_ids = JWFunction::GetColArrayFromRows($searched_users, 'idPicture');
$picture_urls = JWPicture::GetUrlRowByIds($picture_ids);


$element = JWElement::Instance();
$param_tab = array( 'tabtitle' => '叽歪成员搜索结果' );
$param_main = array(
	'searched_count' => $searched_count,
	'searched_users' => $searched_users,
	'picture_urls' => $picture_urls,
);
$param_pager = array( 'pager' => $pager,); 
?>

<?php $element->html_header();?>
<?php $element->common_header();?>

<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div class="f">
		<?php $element->block_headline_minwo();?>
		<?php if($current_user_info)$element->block_tab($param_tab);?>
		<?php $element->block_search_user($param_main);?>
		<?php $element->block_pager($param_pager);?>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- end lefter -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_search_user();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter -->

<div class="clear"></div>
</div><!-- container -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
