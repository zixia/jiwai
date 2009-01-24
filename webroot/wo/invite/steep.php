<?php
require_once( '../../../jiwai.inc.php');
JWLogin::MustLogined(false);
$current_user_id = JWLogin::GetCurrentUserId();

//get cache_key
$cache_key = trim(@$_REQUEST['pathParam'],'/');
if ( !$cache_key )
{
	JWTemplate::RedirectToUrl( '/wo/invite/' );
}

//get buddies and check own;
$memcache = JWMemcache::Instance();
$cache_object = $memcache->Get($cache_key);
if ( !$cache_object || $current_user_id != $cache_object['user_id'] )
{
	JWTemplate::RedirectToUrl( '/wo/invite/' );
}

//Do follow action
if (isset($_POST['not_follow'])) {
	$friends_ids = $_POST['not_follow'];
	$count = 0;
	if ( 0<count($friends_ids) ) {
		foreach ( $friends_ids as $friend_id ) {
			$friend_info = JWUser::GetUserInfo( $friend_id );
			JWSns::ExecWeb($current_user_id, "on $friend_info[nameScreen]", '接收更新通知');
			$count++;
		}
		if ( 0<$count ) {
			JWSession::GetInfo('notice', '已经帮你关注了你在叽歪上的朋友。');
		} else {
			JWSession::SetInfo('notice', '对不起，关注他们失败！');
		}
	} else {
		JWSession::SetInfo('notice', '对不起，你没有选择任何需要关注的朋友。');
	}
}

//get buddies
$contact_list = $cache_object['contact_list'];
$buddy_list = JWBuddy_Import::GetFriendsByIdUserAndRows($current_user_id, $contact_list);

//elements begin;
$element = JWElement::Instance();
$param_step = array( 'buddies' => $buddy_list, 'cache_key' => $cache_key, );
$param_tab = array( 'tabtitle' => '不在叽歪的联系人' );
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
		<?php $element->block_invite_steep($param_step);?>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- lefter end -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_invite_index();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter end -->

<div class="clear"></div>
</div><!-- container end -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
