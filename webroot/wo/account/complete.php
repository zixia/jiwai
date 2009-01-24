<?php
require_once('../../../jiwai.inc.php');
if ( JWLogin::IsLogined(false) ) {
	JWLogin::Logout();
}

$address 	= @$_REQUEST['address'];
$nameScreen	= @$_REQUEST['nameScreen'];

function IsAddressBelongsToName($address, $name)
{
	if ( empty($address) || empty($name) )
		return false;

	if ( preg_match('/^\d/',$name) )
		return false;

	$user_row = JWUser::GetUserInfo($name); //from email,nameScreen

	if ( empty($user_row) )
		return false;

	$device_row = JWDevice::GetDeviceRowByUserId($user_row['idUser']);

	if ( empty($device_row) )
		return false;

	$ims = array_keys($device_row);

	foreach ( $ims as $im )
	{
		if ( $address==$device_row[$im]['address'] )
			return true;
	}
	return false;
}

if ( !empty($nameScreen) )
{
	if ( IsAddressBelongsToName($address, $nameScreen) )
	{
		$user_row = JWUser::GetUserInfo($nameScreen);
		
		if ( JWUser::IsWebUser($user_row['idUser']) )
		{
			$notice_html = "你以前曾来过这里！为什么不登录呢？";
			JWSession::SetInfo('notice',$notice_html);
			header("Location: /wo/login");
			exit(0);
		}
		else
		{
			JWLogin::Login($user_row['idUser'], false);
			header('Location: /wo/account/settings');
			exit(0);
		}
	}
}

$element = JWElement::Instance();
$param_tab = array( 'tabtitle' => '完成你的帐号设置', );
?>
<?php $element->html_header();?>
<?php $element->common_header_no();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div class="f">
		<?php $element->block_headline_tips();?>
		<?php $element->block_tab($param_tab);?>
		<?php $element->block_account_complete();?>
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
