<?php
function reply_status($idStatus)
{
	if( isset($_REQUEST['jw_status']))
	{
		JWLogin::MustLogined();

		$message = $_REQUEST['jw_status'];
		$message = trim($message);

		$status_row = JWStatus::GetDbRowById( $idStatus );
		
		$current_user_id  = JWLogin::GetCurrentUserId();
		$status = $_REQUEST['jw_status'] ;

		$options_info = array(
			'idThread' => $status_row['id'],
			'idConference' => $status_row['idConference'],
		);

		if ( false == empty($_REQUEST['idUserReplyTo']) && false==empty($_REQUEST['idStatusReplyTo']) )
		{
			$options_info['idUserReplyTo'] = $_REQUEST['idUserReplyTo'];
			$options_info['idStatusReplyTo'] = $_REQUEST['idStatusReplyTo'];
		}
		else
		{
			if ( ( false == preg_match('/^@\s*([\w\.\-\_]+)/',$status, $matches) ) &&
					( false == preg_match('/^@\s*(\S+)\s+(.+)$/',$status, $matches) ) )
			{
				$options_info['idUserReplyTo'] = $status_row['idUser'];
				$options_info['idStatusReplyTo'] = $status_row['id'];
			}
		}

		$is_succ = JWSns::UpdateStatus($current_user_id, $message, 'web', null, 'N', 'web@jiwai.de', $options_info);
		if( false == $is_succ )
		{
			JWSession::SetInfo('error', '对不起，回复失败。');
		}
		else
		{
			JWSession::SetInfo('notice', '你的回复发送成功。');
		}

		return $is_succ;
	}
	return false;
}

function user_status($page_user_id, $idStatus, $idStatusReply = null)
{
	//Do reply
	$page_user_info = JWUser::GetDbRowById( $page_user_id );
	if ( reply_status( $idStatus ) )
	{
		$redirect_to = "/".urlEncode($page_user_info['nameUrl'])."/thread/$idStatus";
		JWTemplate::RedirectToUrl( $redirect_to );
	}

	JWTemplate::html_doctype();

	$status_info    = JWStatus::GetDbRowById( $idStatus );
	if( empty( $status_info) || $status_info['idUser'] != $page_user_id ) {
		JWTemplate::RedirectTo404NotFound();
	}

	$user_row = JWUser::GetDbRowById( $status_info['idUser'] );
	$page_user_info = $user_row;

	$current_user_id = JWLogin::GetCurrentUserId();

	$formated_status = JWStatus::FormatStatus($status_info,false);

	$pettyDevice = JWDevice::GetNameFromType( $status_info['device'], $status_info['idPartner'] );

	$protected = JWSns::IsProtected( $page_user_info, $current_user_id );
?>
<html>
<head>
<?php
$head_options = array('ui_user_id' => $page_user_id);
JWTemplate::html_head($head_options);
?>
</head>
<body class="normal">
<?php //JWTemplate::accessibility() ?>
<?php JWTemplate::header() ?>

 <div id="container">
   <div id="content">
	<?php echo JWTemplate::ShowActionResultTips(); ?>
       <div id="wrapper">


<?php
JWTemplate::ShowActionResultTips();
JWTemplate::StatusHead( $user_row, $status_info, $options = array('isMyPages' => false) );

$countReply = JWDB_Cache_Status::GetCountReply( $status_info['id'] );
$replies_data = JWDB_Cache_Status::GetStatusIdsByIdThread($status_info['id'], $countReply);

$status_rows = $user_rows = array();
if( false == empty( $replies_data ) ) 
{
	$replies_info = JWDB_Cache_Status::GetDbRowsByIds( @$replies_data['status_ids'] );
	$user_rows = JWUser::GetDbRowsByIds( @$replies_data['user_ids'] );
}
?>

 <!-- wtTimeline start -->
 <div id="wtTimeline">
<?php
    echo '<div class="top">目前有&nbsp;<span id="countReply" name="countReply">'.$countReply.'</span>&nbsp;条回复</div>';
?>

<?php

   foreach($replies_info as  $k =>$n)
   {
        $reply_info = $n;
        $reply_user_info = @$user_rows[ $reply_info['idUser'] ];
	$reply_to_user_info = JWUser::GetUserInfo( $reply_info['idUserReplyTo'] );
        $photo_url = JWPicture::GetUrlById($reply_info['idPicture'], 'thumb48');
	$protected = JWSns::IsProtected( $reply_user_info, $current_user_id );
?>

	<div class="odd" id="status_<?php echo $reply_info['id']; ?>">
		<div class="head">
			<a href="/<?php echo $reply_user_info['nameUrl'] ?>/"><img icon="<?php echo $reply_info['idUser'];?>" class="buddy_icon" width="48" height="48" title="<?php echo $reply_user_info['nameScreen']; ?>" src="<?php echo $photo_url  ?> "/></a>
		</div>
		<div class="cont"><div class="bg"></div>

	<?php 
		if( false == $protected )
		{
			$formated_status = JWStatus::FormatStatus( $reply_info, false);
			echo $formated_status['status'];
			$reply_user_row = JWUser::GetUserInfo( $reply_info['idUser'] );
			if ( $reply_info['idUser'] != $current_user_id )
				$reply_user_nameScreen_txt = '@' .$reply_user_row['nameScreen']. ' ';
			else
				$reply_user_nameScreen_txt = '';

			JWTemplate::ShowStatusMetaInfo($reply_info, array(
				'replyLinkClick' => 'javascript:scroll(0, screen.height);$("idUserReplyTo").value=' .$reply_info['idUser']. ';$("idStatusReplyTo").value=' .$reply_info['id']. ';$("jw_status").focus();$("jw_status").value="' .$reply_user_nameScreen_txt. '";return false;',
			));
		}else{
			echo "我只和我的好友分享叽歪";
		}
        ?>
       </div><!-- cont -->
   </div><!-- odd -->
   <?php 
   }
?>
   <div class="spacing"></div>
</div><!-- wtTimeline end -->
</div><!-- wrapper -->

<?php 
//显示输入框
$options = array(
	'title' => '添加回复',
	'mode' => 2,
);
JWTemplate::updater( $options );

if( !empty($idStatusReply) )
{
	$reply_status_info = JWStatus::GetDbRowById( $idStatusReply );
	if ( !empty($reply_status_info ) )
	{
		$reply_user_info = JWUser::GetUserInfo( $reply_status_info['idUser'] );
		if ( $current_user_id = $reply_status_info['idUser'] )
			$reply_user_nameScreen_txt = '@' .$reply_user_info['nameScreen']. ' ';
		else
			$reply_user_nameScreen_txt = '';
		echo '<script>scroll(0, screen.height);$("idUserReplyTo").value=' .$reply_status_info['idUser']. ';$("idStatusReplyTo").value=' .$reply_status_info['id']. ';$("jw_status").focus();$("jw_status").value="' .$reply_user_nameScreen_txt. '";</script>';
	}
}
?>

</div><!-- content -->

<div id="wtsidebar" class="static">
<div class="sidediv">
	 <div style="margin-left:8px;"><h2 class="forul">欢迎使用叽歪回复功能</h2></div>
	 <div>
	 	<ul class="actions">
			<li>1. 你可以点击每条叽歪的“回复”按钮来回复对方</li>
			<li>2. 你的回复将只发送给既能收到楼主消息的人，又关注你的人</li>
			<li style="display:none;">3. 在你回复之后，你将接收到之后陆续的回复</li>
		 </ul>
	 </div>
	 <div class="sidediv2">在使用手机和QQ，Skype等时：</div>
	 <div class="msg">
	 	<ul class="actions">
			<li class="pad">你可以使用<span class="pad2">@指令</span>来发布回复</li>
			<li class="pad">方法是：<span class="pad2">@+用户名+空格+内容</span></li>
			<li class="pad">例如你收到：zixia:我爱北京天安门</li>
			<li class="pad">你可以发送：<span class="pad2">@zixia 我也爱北京天安门</span></li>
			<li class="pad">这样就可以回复你关注的人了。</li>
		 </ul>
	 </div>
</div><!-- sidediv -->
</div>

<div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->

<?php  JWTemplate::footer(); ?>          

</body>
</html>
<?php } ?>
