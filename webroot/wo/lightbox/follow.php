<?php
header('Content-Type: text/html;charset=UTF-8');
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined(false);
$current_user_id = JWLogin::GetCurrentUserId();

$param = $_REQUEST['pathParam'];
$follower_user_id = $follower_user = null;
if ( preg_match('/^\/(\d+)$/',$param,$match) ) {
	$follower_user_id = intval($match[1]);
	$follower_user = JWDB_Cache_User::GetDbRowById($follower_user_id);
}

if( empty( $follower_user ) ) {
	JWTemplate::RedirectToUrl('/');
}

if ( $_POST ) {
	if ( $current_user_id && $follower_user_id)
	{
		if( false == empty( $follower_user ) )
		{
			switch ($_POST['notification'])
			{
				case 'Y':
					JWSns::ExecWeb($current_user_id, "on $follower_user[nameScreen]", '接收更新通知');
				break;
				default:
					JWSns::ExecWeb($current_user_id, "follow $follower_user[nameScreen]", "关注$follower_user[nameScreen]");
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

$need_request = ( $follower_user['protected'] == 'Y'
		&& $follower_user_id != $current_user_id
		&& false == JWFollower::IsFollower( $current_user_id, $follower_user_id ) );
?>
<div id="login" class="free reg_wid">
<form id="followForm" name="followForm" method="post" action="/wo/lightbox/follow/<?php echo $follower_user_id; ?>">
<div class="usermsg" style="padding:0">
	<div class="bline">
		<div class="one">
			<div class="hd lt">
				<a href="/<?php echo $follower_user['nameUrl'];?>/"><img src="<?php echo JWPicture::GetUrlById($follower_user['idPicture'],'thumb48');?>" title="<?php echo $follower_user['nameScreen'];?>" /></a>
			</div>
		</div>
		<ul class="msg">
			<li class="rt"><a href="javascript:;" onClick="return JWSeekbox.remove();" class="close">X</a></li>
			<li class="pad_t8"><h3><b>要开始关注<?php echo $follower_user['nameScreen'];?>吗？</b></h3></li>
		</ul>
		<div class="clear"></div>
	</div>

<?php if ($need_request) { ?>

	<div class="pagetitle"><h2>输入你的请求附言：</h2></div>
	<div>
		<textarea style="width:310px; height:50px; overflow:hidden;" name="request_follow" mission="$('followForm').submit();" onKeyDown="JWAction.onEnterSubmit(event,this);"></textarea>
	</div>

<?php } else { ?>

	<div class="pagetitle"><h2>是否同时打开通知呢？</h2></div>
	<ul>
		<li class="lt">
			<input id="followoption1" type="radio" checked="checked" value="Y" name="notification" /> &nbsp;
		</li>
		<li>
			<label for="followoption1">是，在手机或者QQ、MSN、Skype等聊天软件上接收此人的消息</label>
		</li>
	</ul>
	<ul>
		<li class="lt">
			<input id="followoption2" type="radio" value="N" name="notification" /> &nbsp;
		</li>
		<li>
			<label for="followoption2">否，在网页上看到更新就好</label>
		</li>
	</ul>
	<div class="pad_t8 f_gra">如果你改了主意，你可以随时修改是否要接受通知</div>

<?php } ?>

	<div align="center" class="pad_t8"><input type="submit" value="&nbsp; 确定 &nbsp;" /> &nbsp; <input type="button" onclick="JWSeekbox.remove();" value="&nbsp; 取消 &nbsp;" /></div>
</div>
</form>
</div>
