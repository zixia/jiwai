<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();

$logined_user_info 	= JWUser::GetCurrentUserInfo();

if ( preg_match('#^/([\w\d]+)$#',@$_REQUEST['pathParam'],$matches) )
{
	$invite_code	= $matches[1];

	$invitation_info	= JWInvitation::GetInvitationInfoByCode($invite_code);


	if ( empty($inviter_id) )
	{
		header('Location: /wo/account/create');
		exit(0);
	}

	$inviter_id = $invitation_info['idUser'];
	$inviter_user_info	= JWUser::GetUserInfo($inviter_id);
}
?>

<html>

<?php JWTemplate::html_head() ?>

<body class="invitations" id="show">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div class="separator"></div>

<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">
		
		
<?php
if ( ! isset($inviter_user_info) )
{
		header('Location: /wo/account/create');
		exit(0);
}

	// 有效邀请代码
	$inviter_name_full 	= $inviter_user_info['nameFull'];
	$inviter_icon_url 	= JWPicture::GetUserIconUrl($inviter_user_info['id']);
	echo <<<_HTML_
<h2><img alt="$inviter_name_full" src="$inviter_icon_url" /> ${inviter_name_full}希望和您成为叽歪de好友</h2>
_HTML_;

?>

<!--p>
生活中每一时刻都有意义。对关心你的人们来说，你生活中的每个瞬间都有趣而重要。叽歪de是基于此的一项交流服务。 
</p-->
<p>
在关心你的人们看来，你生活中的每个瞬间都有趣、重要而精彩。叽歪de相信生命中每个时刻都有意义，为您提供免费的，通过短消息记录瞬间的服务。 
</p>

<style type="text/css">
input[type="button"] {
font-size:1.5em;
padding:4px 2px;
vertical-align:middle;
width:12em;
}

td {
padding:7px 3px;
vertical-align:top;
}
</style>

<input type="button" value="接受邀请！" onclick="document.location = '/wo/invitations/accept/<?php echo $invite_code?>'">
<input type="button" value="不了，谢谢" onclick="document.location = '/wo/invitations/destroy/<?php echo $invite_code?>'">


  <h3>已经有叽歪de帐号了？请直接登录（同时接受这份邀请）</h3>

  <form action="/wo/login" method="post" name="f">

      <fieldset>
      	<table cellspacing="0">
      		<tr>
      			<th><label for="username_or_email">帐号 / Email</label></th>
      			<td><input id="username_or_email" name="username_or_email" type="text" /></td>
      		</tr>
      		<tr>
      			<th><label for="password">密码</label></th>

      			<td><input id="password" name="password" type="password" /> <small><a href="/wo/account/resend_password">忘记？</a></small></td>
      		</tr>
      		<tr>
      			<th></th>
      			<td><input id="remember_me" name="remember_me" type="checkbox" "checked" value="1" /> <label for="remember_me" class="inline">记住我</label></td>
      		</tr>
      		<tr>

      			<th></th>
      			<td>	
					<input name="invitation_id" type="hidden" value="<?php echo $invitation_info['id']?>" />
					<input name="commit" type="submit" value="登录" />
				</td>
      		</tr>
      	</table>
      </fieldset>
  </form>



<?php
	$friend_ids = JWFriend::GetFriendIds($inviter_user_info['id']);

	if ( $friend_ids )
	{
  		echo <<<_HTML_
<h3>${inviter_name_full}的好友们：</h3>
  <ul class="friends">
_HTML_;

		foreach ( $friend_ids as $friend_id )
		{
			$friend_info = JWUser::GetUserInfo($friend_id);
			$icon_url	= JWPicture::GetUserIconUrl($friend_id);

			echo <<<_HTML_
  	<li id="user_$friend_info[id]">
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td class="icon">
					<a href="http://JiWai.de/$friend_info[nameScreen]/"><img alt="$friend_info[nameFull]" border="0" height="24" src="$icon_url" style="vertical-align:middle" width="24" /></a>
				</td>
				<td>
					<a href="http://JiWai.de/$friend_info[nameScreen]/">$friend_info[nameFull]</a>
				</td>
			</tr>
		</table>
	</li>

_HTML_;
		}
		echo "\n  </ul>\n";
	}
	else
	{
  		echo "<h3>您是${inviter_name_full}的第一位好友！</h3>";
	}

  // end 有效邀请代码
?>

			</div><!-- wrapper -->
	</div><!-- content -->
</div><!-- #container -->

<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>

