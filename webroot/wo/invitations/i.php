<?php
require_once(dirname(__file__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();

$current_user_id = JWLogin::GetCurrentUserId();

if ( preg_match('#^/([\w\d=]+)$#',@$_REQUEST['pathParam'],$matches) )
{
	$invite_code	= $matches[1];
	$inviter_id = JWUser::GetIdUserFromIdEncoded( $invite_code ) ;
		
	if( null==$inviter_id ){
		$invitation_info = JWInvitation::GetInvitationinfoByCode($invite_code);
		$inviter_id = empty($invitation_info) ? null : $invitation_info['iduser'];
	}

	if ( empty($inviter_id) )
	{
		header('location: /wo/account/create');
		exit(0);
	}

	$inviter_user_info	= JWUser::GetUserInfo($inviter_id);
}
?>

<html>

<head>
<?php JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
)); ?>
</head>

<body class="normal">
<?php JWTemplate::header(); ?>

<div id="container">
<div id="wtFollow"><!-- wtFollow start -->

<?php
if ( ! isset($inviter_user_info) )
{
	JWTemplate::RedirectToUrl( '/wo/account/create' );
}

// 有效邀请代码
$inviter_name_full = $inviter_user_info['nameFull'];
$inviter_name_screen = $inviter_user_info['nameScreen'];
$inviter_icon_url = JWPicture::GetUserIconUrl($inviter_user_info['id']);

echo  <<<_TIPS_
<p class="title"><IMG alt="$inviter_name_full" src="$inviter_icon_url">${inviter_name_screen}希望在叽歪上关注你</p>
<p class="follow" style="padding-right:15px; ">在关心你的人们看来，你生活中的每个瞬间都有趣、重要而精彩。叽歪de相信生命中每个时刻都有意义，为你提供免费的，通过短消息记录瞬间的服务。</p>
<p style="padding-left:30px; "><span style="margin-right:20px; "><input type="button" class="submitbutton" value="接收邀请！" onClick="document.location='/wo/invitations/accept/$invite_code';"></span><input type="button" class="submitbutton" value="不了，谢谢！" onClick="document.location='/wo/invitations/destroy/$invite_code';"></p>
_TIPS_;

if ( null==$current_user_id || JWUser::IsAnonymous($current_user_id) ) 
	echo <<<_LOGIN_
<p style="font-size:14px; padding-left:30px; padding-top:20px; font-weight:bold; color:#ff6600; ">已经有叽歪de帐号了？请直接登录（同时接受这份邀请）</p>
<form action="/wo/login" method="post" name="f">
	<input name="invitation_id" type="hidden" value="$inviter_id" />
	<p style="padding-left:30px; font-size:14px;">用户名 <input id="username_or_email" name="username_or_email" class="inputStyle" style="width: 270px;" type="text"></p>
<p style="padding-left:30px; font-size:14px;">密<span style="padding-left:1em; ">码</span>  <input id="password" name="password" alt="密码" minlength="6" maxlength="16" class="inputStyle" style="width: 270px;" type="password" /></p>
	<p style="padding-left: 80px;"><input id="remember_me" name="remember_me" type="checkbox" checked>在这台电脑上记住我</p>
	<p style="padding: 0px 0pt 0pt 80px; padding-bottom:20px;"><input name="Submit" class="submitbutton" value="登 录" type="submit"></p>
</form>
_LOGIN_
?>

<?php
$friend_ids = JWFollower::GetFollowingIds($inviter_user_info['id']);
$friend_count = count($friend_ids);
echo <<<_HTML_
<p class="title">${inviter_name_screen}关注的人（${friend_count}）</p>
<div class="follow">
_HTML_;

if ( $friend_ids )
{
	echo <<<_HTML_
	<ul class="followlist">
_HTML_;

	foreach ( $friend_ids as $friend_id )
	{
		$friend_info = JWUser::GetUserInfo($friend_id);
		$icon_url	= JWPicture::GetUserIconUrl($friend_id);

		echo <<<_HTML_
			<li id="user_$friend_info[id]"><a href="http://JiWai.de/$friend_info[nameUrl]/" rel="contact"><img icon="$friend_info[id]" class="buddy_icon" alt="$friend_info[nameFull]" src="$icon_url"/>$friend_info[nameScreen]</a></li>
_HTML_;
	}
	echo "\n  </ul>\n";
}
else
{
	echo "<h3>你是${inviter_name_full}关注的第一人！</h3>";
}
?>

</div><!-- follow -->
<div style="overflow: hidden; clear: both; height:16px; line-height: 1px; font-size: 1px;"></div>
</div><!-- wtFollow end -->

<div id="wtchannelsidebar">
<div class="sidediv">
<form id="f3" action="/wo/search/users">
	<P class="title">成员搜索</P>
	<p><input name="q" type="text" class="inputStyle" /></p>
	<p class="sidediv3"><input name="Submit" type="submit" class="submitbutton" value="搜索成员" /></p>
	<div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</form>
<div class="line"></div>
<?php if ( JWLogin::IsLogined() ) { ?>
<P style="padding:10px 0 10px 10px "><a href="/wo/invitations/invite">&gt;&gt;&nbsp;邀请我们的朋友加入叽歪</a></P>
<P style="padding:5px 0 10px 10px "><a href="/wo/followings/">&gt;&gt;&nbsp;你关注的人 </a></P>
<?php } ?>
<br />
<div style="clear: both;height: 7px;"></div>
</div><!-- sidediv -->
</div><!-- wtsidebar -->

</div>
<?php JWTemplate::container_ending(); ?>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
