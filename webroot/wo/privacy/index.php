<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$user_info = JWUser::GetCurrentUserInfo();
$new_user_info = @$_POST['user'];
$outInfo = $user_info;
$photo_url = JWPicture::GetUserIconUrl($user_info['id'],'thumb96');
if ( !empty($_POST) )
{
	$new_user_info['protected'] = isset($new_user_info['protected'])?'Y':'N';
	$new_user_info['messageFriendOnly'] = isset($new_user_info['messageFriendOnly'])?'Y':'N';
	if( $new_user_info['protected'] != $outInfo['protected'] ) {
		$array_changed['protected'] = $new_user_info['protected'];
	}
	if( $new_user_info['messageFriendOnly'] != $outInfo['messageFriendOnly'] ) {
		$array_changed['messageFriendOnly'] = $new_user_info['messageFriendOnly'];
	}

	if( 0<count( $array_changed )) {
		JWUser::Modify( $user_info['id'], $array_changed );
		JWSession::SetInfo('notice', '修改个人资料成功');
	}
	JWTemplate::RedirectBackToLastUrl("/");
}

$element = JWElement::Instance();
$param_tab = array( 'tabtitle' => '保护设置' );
$param_side = array( 'sindex' => 'privacy' );
?>

<?php $element->html_header();?>
<?php $element->common_header_wo();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div id="leftBar" >
		<?php $element->block_headline_minwo();?>
		<?php $element->block_tab($param_tab);?>
		<div class="f">
			<?php $element->block_privacy();?>
		</div>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- lefter end -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_setting($param_side);?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter end -->

<div class="clear"></div>
</div><!-- container end -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
