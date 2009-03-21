<?php
require_once('../../../jiwai.inc.php');
if ( false==JWLogin::IsAnonymousLogined() ) {
	JWLogin::Logout();
}

$element = JWElement::Instance();
$user = array();
if ( $_POST ) 
{
	$user = $read_and_accept = $error_string = null;
	extract( $_POST, EXTR_IF_EXISTS );

	if ( null == $read_and_accept )
	{
		$error_string .= '<LI>使用叽歪服务必须接受叽歪服务条款</LI>';
	}

	$validate_item = array(
		array( 'Email', $user['email'] ),
		array( 'NameScreen', $user['name_screen'] ),
		array( 'Compare', $user['pass'], $user['pass_confirm'] ),
	);

	$validate_result = JWFormValidate::Validate($validate_item);

	if ( is_array($validate_result) )
	{
		foreach ($validate_result AS $item)
		{
			$error_string .= "<LI>$item</LI>";
		}
	}

	if ( null == $error_string ) 
	{
		$user['nameFull'] = $user['name_screen'];
		$user['ip'] = JWRequest::GetIpRegister();
		$user['nameScreen'] = $user['name_screen'];

		if ( $user_id = JWSns::CreateUser($user) )
		{
			JWLogin::Login( $user_id );
			
			/* for invitation */
			$invitation_id	= JWSession::GetInfo('invitation_id');
			if ( isset($invitation_id) )
				JWSns::FinishInvitation($user_id, $invitation_id);

			$inviter_id = JWSession::GetInfo('inviter_id');
			if ( isset($inviter_id) )
				JWSns::FinishInvite($user_id, $inviter_id);
			/* end invitation */

			JWTemplate::RedirectToUrl( '/wo/account/regok' );
		}
		else
		{
			$error_string = "<li>系统出现故障、注册用户失败，请稍后再来</LI>";
		}
	}

	if ( $error_string )
	{
		JWSession::SetInfo( 'notice', "<B>提交表单时发生以下错误：</B><OL>$error_string</OL>" );
	}
}
?>
<?php $element->html_header();?>
<?php $element->common_header();?>
<div id="container">
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div class="f">
		<?php $element->block_headline_tips();?>
		<div class="tag">
			<ul>
				<h2><b>快速注册</b></h2>
			</ul>
		</div>
		<?php $element->block_account_create();?>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- lefter end -->

<div id="righter">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_create();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter end -->

<div class="clear"></div>
</div><!-- container end -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
