<?php
require_once(dirname(__file__) . '/../../../jiwai.inc.php');

$current_user_id = JWLogin::GetCurrentUserId();
$inviter_user_id = $inviter_user = null;

if ( preg_match('#^/([\w\d=]+)$#',@$_REQUEST['pathParam'],$matches) ) {
	$invite_code = $matches[1];
	$inviter_id = JWUser::GetIdUserFromIdEncoded( $invite_code ) ;
		
	if(!$inviter_id){
		$invitation_info = JWInvitation::GetInvitationinfoByCode($invite_code);
		$inviter_id = abs(intval(@$invitation_info['idUser']));
	}

	if (!$inviter_id) {
		JWTemplate::RedirectToUrl('/wo/account/create');
	}
	$inviter_user = JWUser::GetUserInfo($inviter_id);
}

if (!$inviter_user) {
	JWTemplate::RedirectToUrl( '/wo/account/create' );
}

JWSession::SetInfo('inviter_id', $inviter_id);

// 有效邀请代码
$inviter_user_id = $inviter_id;

$friend_ids = JWFollower::GetFollowingIds($inviter_user_id);
$friend_users = JWDB_Cache_User::GetDbRowsByIds($friend_ids);
$picture_ids = JWUtility::GetColumn($friend_users, 'idPicture');
$picture_urls = JWPicture::GetUrlRowByIds($picture_ids);

$element = JWElement::Instance();
$param_main = array(
	'g_page_user_id' => $inviter_user_id,
	'g_page_user' => $inviter_user,
	'friend_users' => $friend_users,
	'picture_urls' => $picture_urls,
	'invite_code' => $invite_code,
);
?>
<?php $element->html_header();?>
<?php $element->common_header();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div class="f">
		<?php $element->block_invite_i($param_main);?>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- lefter end -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_searchuser();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter end -->

<div class="clear"></div>
</div><!-- container end -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
