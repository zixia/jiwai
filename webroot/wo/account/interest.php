<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$user_info = JWUser::GetCurrentUserInfo();
$new_user_info = @$_POST['user'];
$outInfo = $user_info;

if( $new_user_info )
{
	$notice_html = null;
	$error_html = null;

	$array_changed = array();

	if( $new_user_info['interest'] != @$outInfo['interest'] ) {
		$array_changed['interest'] = $new_user_info['interest'];
	}

	if( $new_user_info['bookWriter'] != @$outInfo['bookWriter'] ) {
		$array_changed['bookWriter'] = $new_user_info['bookWriter'];
	}

	if( $new_user_info['player'] != @$outInfo['player'] ) {
		$array_changed['player'] = $new_user_info['player'];
	}

	if( $new_user_info['music'] != @$outInfo['music'] ) {
		$array_changed['music'] = $new_user_info['music'];
	}

	if( $new_user_info['place'] != @$outInfo['place'] ) {
		$array_changed['place'] = $new_user_info['place'];
	}

	if( count( $array_changed ) ) {
		if( count( $array_changed ) ) {
			JWUser::Modify( $user_info['id'], $array_changed );
			JWSession::SetInfo('notice', '修改个人资料成功');
		}
		JWTemplate::RedirectToUrl();
	}
}

$element = JWElement::Instance();
$param_tab = array( 'now' => 'account_interest' );
$param_side = array( 'sindex' => 'account' );
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
			<?php $element->block_account_interest();?>
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
