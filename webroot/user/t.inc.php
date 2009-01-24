<?php
require_once( '../../jiwai.inc.php' );
$element = JWElement::Instance();

$tag_id_counts = JWDB_Cache_Status::GetTagIdsTopicByIdUser($g_page_user_id);
$tag_ids = array_keys($tag_id_counts);
$tags = JWDB_Cache_Tag::GetDbRowsByIds( $tag_ids );

$param_main = array(
	'tags' => $tags,
	'title' => "{$g_page_user['nameScreen']}的" .count($tags) ."个话题",
);

$param_tab = array(
	'now' => 'ut_owner',
	'tab' => array(
		'owner' =>  array('此人话题', "/{$g_page_user['nameUrl']}/t/"),
		'followings' => array('此人关注', "/{$g_page_user['nameUrl']}/tfollowings/"),
	),
);
?>
<?php $element->html_header();?>
<?php $element->common_header();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div class="f">
		<?php $element->block_headline_minwo();?>
		<?php $element->block_tab($param_tab);?>
		<?php $element->block_tag_user($param_main);?>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div>
<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_tag_user($param_side);?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div>

<div class="clear"></div>
</div>

<?php $element->common_footer();?>
<?php $element->html_footer();?>
