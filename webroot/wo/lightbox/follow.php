<?php
require_once ('../../../jiwai.inc.php');

//JWLogin::MustLogined();
$current_user_id = JWLogin::GetCurrentUserId();

$param = $_REQUEST['pathParam'];
$follower_user_id = $follower_user_row = null;
if ( preg_match('/^\/(\d+)$/',$param,$match) )
{
	$follower_user_id = intval($match[1]);
	$follower_user_row = JWDB_Cache_User::GetDbRowById( $follower_user_id );
}

if( empty( $follower_user_row ) )
{
	JWTemplate::RedirectToUrl('/');
}

if ( $_POST && isset($_POST['notification']) )
{
	if ( $current_user_id && $follower_user_id)
	{
		if( false == empty( $follower_user_row ) )
		{
			switch ($_POST['notification'])
			{
				case 'Y':
					JWSns::ExecWeb($current_user_id, "on $follower_user_row[nameScreen]", '接收更新通知');
				break;
				default:
					JWSns::ExecWeb($current_user_id, "follow $follower_user_row[nameScreen]", "关注$follower_user_row[nameScreen]");
				break;
			}
			
			//Update FollowerRequest for request
			if( isset($_POST['request_follow']) )
			{ 
				if( $exist_id = JWFollowerRequest::IsExist( $follower_user_id, $current_user_id ) ) { 
					$up_array = array( 'note' => $_POST['request_follow'], );
					JWDB::UpdateTableRow( 'FollowerRequest', $exist_id, $up_array );
				}   
			}  
		}

		JWTemplate::RedirectBackToLastUrl('/');
	}
}
?>

<?php JWTemplate::html_doctype(); ?>
<?php
$need_request = ( $follower_user_row['protected'] == 'Y'
			&& $follower_user_id != $current_user_id
			&& false == JWFollower::IsFollower( $current_user_id, $follower_user_id ) );
?>

<form id="followForm" name="followForm" method="post" action="/wo/lightbox/follow/<?php echo $follower_user_id; ?>">
<div id="wtLightbox">
	<div class="top">
		<div class="head"><a href="/<?php echo urlEncode($follower_user_row['nameUrl']);?>/"><img class="buddy_icon" icon="<?php echo $follower_user_id;?>" width="48" height="48" title="<?php echo $follower_user_row['nameScreen'];?>" alt="<?php echo $follower_user_row['nameScreen'];?>" src="<?php echo JWPicture::GetUserIconUrl($follower_user_id);?>"/></a></div>

<?php if( $need_request ) { ?>
		<div class="pad"><?php echo $follower_user_row['nameScreen'];?>&nbsp;需要验证你的身份。</div>  
<?php } else  { ?>
		<div class="pad">要开始关注&nbsp;<?php echo $follower_user_row['nameScreen'];?>&nbsp;吗？</div>  
<?php } ?>
	</div><!-- top-->

<?php if( $need_request ) { ?>
	<p class="pad1">输入你的请求附言：</p>
	<p class="pad2" style="padding-top:0px;"><textarea id="request_follow" name="request_follow" class="requestFriend" mission="$('followForm').submit(); return false;" onKeyDown="JWAction.onEnterSubmit(event,this);" ></textarea></p>
<? } else { ?>
	<p class="pad1">是否同时打开通知呢？</p>
	<ul>
		<li class="box1"><input type="radio" id="followoption1" name="notification" value="Y" checked="true"/></li>
		<li class="box2"><label for="followoption1">是，在手机或者QQ、MSN、Skype等聊天软件上接收此人的消息</label></li>
	</ul>
	<ul>
		<li class="box1"><input type="radio" id="followoption2" name="notification" value="N"/></li>
		<li class="box2"><label for="followoption2">否，在网页上看到更新就好</label></li>
	</ul>

	<p class="pad2">如果你改了主意，你可以随时修改是否要接受通知</p>
<?php } ?>

	<p class="butt">
	  <input id="jwbutton" name="jwbutton" type="submit" class="submitbutton" value="确定" onclick="$('followForm').submit(); return false;"/>&nbsp;&nbsp;<input type="button" class="closebutton" value="取消" onclick="TB_remove();"/>
	</p>
</div><!-- wtLetterbox -->
</form>
</body>
</html>
