<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
$element = JWElement::Instance();

if(empty($g_page_user_id)) {
	JWLogin::MustLogined(false);
	$g_page_user_id=JWLogin::GetCurrentUserId();
}

$g_page_user = JWUser::GetUserInfo($g_page_user_id);
$style = abs(intval(@$_GET['s']));
$now = $style ? 'square' : 'list';
$limit = isset($_GET['n']) ? min(50, abs(intval($_GET['n']))) : 20;

$page = !isset($_GET['page']) ? 1 : intval($_GET['page']) ;
$friend_ids = JWFollower::GetFollowerIds( $g_page_user_id );
$followers_num = count( $friend_ids );

if ( $limit ) {
	$pager = new JWPager(array('rowCount'=>$followers_num-1));
	$friend_ids = array_slice($friend_ids, ($page-1)*$limit, $limit);
}
$friend_user_rows = JWDB_Cache_User::GetDbRowsByIds($friend_ids);

$picture_ids = JWFunction::GetColArrayFromRows($friend_user_rows, 'idPicture');
$picture_url_row = JWPicture::GetUrlRowByIds($picture_ids);
$user_action_rows = JWSns::GetUserActions($g_page_user_id , $friend_ids );

$param_main = array(
		'friend_ids' => $friend_ids,
		'friend_user_rows' => $friend_user_rows,
		'picture_ids' => $picture_ids,
		'picture_url_row' => $picture_url_row,
		'user_action_rows' => $user_action_rows,
		);

$param_pager = array(
		'pager' => @$pager,
		);

$param_tab = array( 
		'now' => 'followers_' . $now,
		'tabtitle' => $followers_num.'人关注',
		'tab' => $tab,
		'wo' => true,
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
		<div>
			<?php
			if (0==$style)
			$element->block_follow_default($param_main);
			else
			$element->block_follow_list($param_main);
			?>
		</div>
		<?php $element->block_pager($param_pager);?>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- end lefter -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_invite_index();?>
		<?php $element->side_inviteuser();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter -->

<div class="clear"></div>
</div><!-- container -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
