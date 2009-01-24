<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$user_info = JWUser::GetCurrentUserInfo();
$new_user_info = @$_POST['user'];
$is_reset_password = JWSession::GetInfo('reset_password', false);
$is_web_user = JWUser::IsWebUser($user_info['idUser']);
$outInfo = $user_info;

if ( isset($new_user_info) )
{
	$nameScreen	= trim(@$new_user_info['nameScreen']);
	$email		= trim(@$new_user_info['email']);

	// compatible the twitter url param: name & description
	if ( empty($nameScreen) )
		$nameScreen	= trim(@$new_user_info['name']);

	$arr_changed 	= array();
	$error_html 	= null;
	$notice_html	= null;

	if ( isset($nameScreen) && $nameScreen!=$user_info['nameScreen'] )
	{
		$arr_changed['nameScreen'] = $nameScreen;
	}
	
	if ( isset($email) && $email!=$user_info['email'] )
	{
		$arr_changed['email'] = $email;
	}
	
	$nameUrl = isset($_POST['nameUrl']) ? $_POST['nameUrl'] : $user_info['nameUrl'];
	if ( !empty($nameUrl) && $nameUrl!=$user_info['nameUrl'] && 'N'==$user_info['isUrlFixed'])
	{
		$arr_changed['nameUrl'] = $nameUrl;
		$arr_changed['isUrlFixed'] = 'Y';
	}
	$nameUrl = empty($nameUrl) ? $user_info['nameUrl'] : $nameUrl;

	$validate_item = array(
		array( 'Email', $email),
		array( 'NameScreen', $nameScreen ),
		array( 'NameUrl', $nameUrl ),
	);

	$validate_result = JWFormValidate::Validate($validate_item);

	$error_string = null;
	if ( is_array($validate_result) )
	{
		foreach ($validate_result AS $item)
		{
			$error_string .= "<LI>$item</LI>";
		}
	}

	if ( $error_string )
		$error_html = "<B>提交表单时发生以下错误：</B><OL>$error_string</OL>" ;

	if ( empty($error_html) && false == empty($arr_changed) )
	{
		if ( ! JWUser::Modify($user_info['id'],$arr_changed) )
		{
			JWSession::SetInfo('notice', '用户信息更新失败，请稍后再试。');
		}

		JWSession::SetInfo('notice', '用户信息修改成功！');

	}else{
		JWSession::SetInfo('notice', $error_html);
	}

	JWTemplate::RedirectToUrl();
}

$element = JWElement::Instance();
$param_tab = array( 'now' => 'account_settings' );
$param_side = array( 'sindex' => 'account' );
?>
<?php $element->html_header();?>
<?php $element->common_header_wo();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div class="f">
		<?php $element->block_headline_minwo();?>
		<?php $element->block_tab($param_tab);?>
		<?php $element->block_account_settings();?>
		<?php $element->block_rsslink();?>
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
