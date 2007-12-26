<?php
$page = isset( $_REQUEST['page'] ) ? intval( $_REQUEST['page'] ) : 1;
$page = ( $page < 1 ) ? 1 : $page;
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
			'idTag' => $status_row['idTag'],
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
			JWSession::SetInfo('error', '对不起，回复失败。');

		return $is_succ;
	}

	return false;
}

function user_status($page_user_id, $idStatus, $idStatusReply = null, $idTag = null)
{
	$tag_row = JWTag::GetDbRowById( $idTag );

	$status_info    = JWStatus::GetDbRowById( $idStatus );
	//$idTag = $status_info['idTag'];
	$sideInfo = JWStatus::GetStatusByIdTagAndIdStatus($idTag, $idStatus,0,20 );

	$countPost = JWDB_Cache_Status::GetCountPostByIdTag( $idTag );

	//Do reply

	if ( reply_status( $idStatus ) )
	{
		$redirect_to = "/t/".urlEncode($tag_row['name']). "/thread/$idStatus";
		JWTemplate::RedirectToUrl( $redirect_to );
	}

	JWTemplate::html_doctype();
	if( empty( $status_info) || $status_info['idUser'] != $page_user_id ) {
		JWTemplate::RedirectTo404NotFound();
	}

	$user_row = JWDB_Cache_User::GetDbRowById( $status_info['idUser'] );
	$page_user_info = $user_row;

	$current_user_id = JWLogin::GetCurrentUserId();
	$formated_status = JWStatus::FormatStatus($status_info,false);

	$pettyDevice = JWDevice::GetNameFromType( $status_info['device'], $status_info['idPartner'] );

	$protected = JWSns::IsProtected( $page_user_info, $current_user_id );

?>

<html>
<head>
<?php
/* move to top for meta-seo */
$countReply = JWDB_Cache_Status::GetCountReply( $status_info['id'] );
$replies_data = JWDB_Cache_Status::GetStatusIdsByIdThread($status_info['id'], $countReply);

$status_rows = $user_rows = array();
if( false == empty( $replies_data ) ) 
{
	$replies_info = JWDB_Cache_Status::GetDbRowsByIds( @$replies_data['status_ids'] );
	$user_rows = JWDB_Cache_User::GetDbRowsByIds( @$replies_data['user_ids'] );
}

/* meta-seo content */
$keywords = $tag_row['name'] . $user_row['nameScreen'];
$user_showed = array();
foreach ( $user_rows  as $user_id=>$one )
{
	if ( isset($user_showed[$user_id]) )
		continue;
	else
		$user_showed[$user_id] = true;

	$keywords .= " $one[nameScreen]($one[nameFull])";
}

$description = $tag_row['name'] . $status_info['status'];
foreach ( $replies_info as $one )
{
	$description .= $one['status'];
	if ( mb_strlen($description,'UTF-8') > 140 )
	{
			$description = mb_substr($description,0,140,'UTF-8');
			break;
	}
}

$head_options = array(
	'ui_user_id' => $page_user_id,
	'keywords' => $keywords,
	'description' => $description,
);
JWTemplate::html_head($head_options);
?>
</head>
<body class="normal">
<?php JWTemplate::header() ?>

 <div id="container">
   <div id="content">
       <div id="wrapper">


<?php
JWTemplate::ShowActionResultTips();
JWTemplate::StatusHead( $user_row, $status_info, $options = array('isMyPages' => false) );
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

<div id="wtchannelsidebar">
<div class="sidediv">
<div style="margin:15px 0 0 10px;"><a href="<?php echo JW_SRVNAME .'/t/' .$tag_row['name'] .'/';?>" class="pad1">回到#<?php echo $tag_row['name']; ?></a></div>
<div class="line2"><div></div></div>
<h2 class="forul">其他话题</h2>

<?php 
    foreach( $sideInfo as $key => $value)
    {
        $user_info = JWUser::GetUserInfo($value['idUser']);
?>
<div class="content"><a href="<?php echo JW_SRVNAME .'/t/' .$tag_row['name'], '/thread/'. $value['id']. '/'.$value['id']; ?>" class="pad3"><?php echo  mb_substr($value['status'],0,14 ); ?>...</a>
<div><?php echo $user_info['nameScreen']; ?></div></div>
<?php
    }
?>

</div><!-- sidediv -->
</div><!-- wtsidebar -->

<div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div>
<!-- #container -->

<?php JWTemplate::footer(); ?>

</body>
</html>
<?php } ?>
