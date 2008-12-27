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

function do_invite_friend_with_email( $emails, $user, $subject=null )
{
	$options = array(
		'content_type' => 'text/html',
		'template_file' => 'html/Invitation.html',
	);

	$friends_emails = isset($_POST['friends_emails']) ? $_POST['friends_emails'] : array();
	$subject = ($subject==null)
		? "你的朋友 $user[nameScreen]($user[nameFull]) 邀请你加入叽歪"
		: $subject;

	$count = 0;

	if (false==empty($emails))
	{
		foreach ( $emails as $email )
		{
			if( true == JWDevice::IsValid( $email, 'email' ) )
			{
				if ( JWMail::SendMailInvitation( $user, $email, $subject, $options ) )
					$count++;
			}
		}
	}

	return $count > 0;
}

if ( isset($_POST['invite_not_follow'] ) || isset($_POST['invite_all']))
{
	$friends_ids = $_POST['nofollow'];
	$count = 0;

	if ( 0<count($friends_ids) )
	{
		foreach ( $friends_ids as $friend_id )
		{
			$friend_info = JWUser::GetUserInfo( $friend_id );
			JWSns::ExecWeb($current_user_id, "on $friend_info[nameScreen]", '接收更新通知');
			$count++;
		}
		if ( 0<$count )
		{
			JWSession::GetInfo('notice', '已经帮你关注了你在叽歪上的朋友。');
		}
		else
		{
			JWSession::SetInfo('notice', '对不起，关注他们失败！');
		}
	}
	else
	{
		JWSession::SetInfo('notice', '对不起，你没有选择任何需要关注的朋友。');
	}
	JWTemplate::RedirectBackToLastUrl('/');
}

if ( isset($_POST['invite_not_reg'] ) || isset($_POST['invite_all']))
{
	$friends_emails = $_POST['noreg'];

	if ( 0<count($friends_emails) )
	{
		if ( do_invite_friend_with_email( $friends_emails, $current_user_info ) )
		{
			JWSession::SetInfo('notice', '已经帮你向你的朋友们发送了邮件邀请。');
		}
		else
		{
			JWSession::SetInfo('notice', '对不起，暂时无法用邮件邀请你的朋友。');
		}
	}
	else
	{
		JWSession::SetInfo('notice', '对不起，你没有选中任何待邀请的朋友。');
	}
	JWTemplate::RedirectBackToLastUrl('/');
}

//get buddies
$contact_list = $cache_object['contact_list'];
$buddy_list = JWBuddy_Import::GetFriendsByIdUserAndRows($current_user_id, $contact_list);

//elements begin;
$element = JWElement::Instance();
$param_step = array( 'buddies' => $buddy_list, 'cache_key' => $cache_key, );
$param_tab = array( 'tabtitle' => '在叽歪的联系人' );
?>

<?php $element->html_header();?>
<?php $element->common_header();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div id="leftBar" >
		<?php $element->block_headline_minwo();?>
		<?php $element->block_tab($param_tab);?>
		<div class="f">
			<?php $element->block_invite_step($param_step);?>
		</div>
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
