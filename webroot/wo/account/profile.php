<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$user_info = JWUser::GetCurrentUserInfo();
$new_user_info = @$_POST['user'];
$birthday_info = @$_POST['birthday'];
$outInfo = $user_info;
@list($birthday_year, $birthday_month, $birthday_day) = @explode("-", $outInfo['birthday'], 3);

if ( $new_user_info )
{
	$notice_html = null;
	$error_html = null;

	$array_changed = array();
	if( $new_user_info['nameFull'] != $outInfo['nameFull'] ) {
		if ($new_user_info['nameFull'] === '')
			$array_changed['nameFull'] = $outInfo['nameScreen'];
		elseif (JWUser::IsValidFullName($new_user_info['nameFull']))
			$array_changed['nameFull'] = $new_user_info['nameFull'];
	}
	if( $new_user_info['nameFull'] != $outInfo['nameFull'] ) {
		if( $new_user_info['nameFull'] === '' )
			$array_changed['nameFull'] = $outInfo['nameScreen'];
		else
			$array_changed['nameFull'] = $new_user_info['nameFull'];
	}

	if( $new_user_info['url'] != $outInfo['url'] ) {
		$new_user_info['url'] = ltrim( $new_user_info['url'], '/' );
		if( $new_user_info['url'] && false == preg_match( '/^(http:|https:)/', strtolower($new_user_info['url']) ) ) {
			$new_user_info['url'] = 'http://' . $new_user_info['url'];
		}
		$array_changed['url'] = $new_user_info['url'];
	}

	if( $new_user_info['address'] != @$outInfo['address'] ) {
		$array_changed['address'] = $new_user_info['address'];
	}

	if( $new_user_info['zipcode'] != @$outInfo['zipcode'] ) {
		$array_changed['zipcode'] = $new_user_info['zipcode'];
	}

	if( $new_user_info['current'] != @$outInfo['current'] ) {
		$array_changed['current'] = $new_user_info['current'];
	}

	if( $new_user_info['gender'] != @$outInfo['gender'] ) {
		$array_changed['gender'] = $new_user_info['gender'];
	}

	if( $new_user_info['marriage'] != @$outInfo['marriage'] ) {
		$array_changed['marriage'] = $new_user_info['marriage'];
	}

	$new_user_info['birthday'] = "$birthday_info[year]-$birthday_info[month]-$birthday_info[day]";
	if( $new_user_info['birthday'] != @$outInfo['birthday'] ) {
		$array_changed['birthday'] = $new_user_info['birthday'];
	}

	if( $new_user_info['bio'] != $outInfo['bio'] ) {
		if( $new_user_info['bio'] === '' ) {
			$array_changed['bio'] = $outInfo['nameFull'];
		} else {
			$array_changed['bio'] = $new_user_info['bio'];
		}
	}

	$new_location = intval(@$_POST['province'])."-".intval(@$_POST['city']);
	$new_location = trim($new_location);
	if( $new_location != $outInfo['location'] ) {
		$array_changed['location'] = $new_location;
	}

	$new_native = intval(@$_POST['native_province'])."-".intval(@$_POST['native_city']);
	$new_native = trim($new_native);
	if( $new_native != $outInfo['native'] ) {
		$array_changed['native'] = $new_native;
	}

	if( count( $array_changed ) ) {
		if( count( $array_changed ) ) {
			JWUser::Modify( $user_info['id'], $array_changed );
			JWSession::SetInfo('notice', '修改个人资料成功');
		}
		JWTemplate::RedirectToUrl();
	}
}

/*Procince and city id */
$pid = $cid =0;
@list($pid, $cid) = explode('-', $outInfo['location']);

/*Native Procince and city id */
$native_pid = $native_cid =0;
@list($native_pid, $native_cid) = explode('-', $outInfo['native']);

//Template render
$element = JWElement::Instance();
$param_main = array(
	'pid' => $pid,
	'cid' => $cid,
	'native_pid' => $native_pid,
	'native_cid' => $native_cid,
	'birthday_year' => $birthday_year,
	'birthday_month' => $birthday_month,
	'birthday_day' => $birthday_day,
	'ryear' => range(2008,1930),
	'rmonth' => range(1,12),
	'rday' => range(1,31),
);

$param_tab = array( 'now' => 'account_profile' );
$param_side = array( 'sindex' => 'account' );
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
		<?php $element->block_account_profile($param_main);?>
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
