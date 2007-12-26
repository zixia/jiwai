<?php 
header('Content-Type: text/html;charset=UTF-8');

require_once( '../../../jiwai.inc.php' );

$pathParam = null;
extract( $_REQUEST, EXTR_IF_EXISTS );
$user_id = intval( trim($pathParam, '/') );
if( ! $user_id ) 
{
    die( "Wrong Request!" );
}

$user_row = JWUser::GetUserInfo( $user_id );

$user_name_screen = $user_row['nameScreen'];
$user_name_url = $user_row['nameUrl'];

$current_user_id = JWLogin::GetCurrentUserId();

$show_protected = true;
if( JWSns::IsProtected($user_row, $current_user_id) )
{
	$show_protected = false;
	$status = $user_name_screen .'设置了隐私保护，只和互相关注的人分享叽歪。';
}else
{
	if( $user_row['idConference'] ) 
	{
		$user_status_data = JWDB_Cache_Status::GetStatusIdsFromConferenceUser( $user_id, 1 );
	}
	else
	{
		$user_status_data = JWStatus::GetStatusIdsFromUser( $user_id, 1 );
	}
	$user_status_rows = JWDB_Cache_Status::GetDbRowsByIds( $user_status_data['status_ids'] );

	$status_id = intval( $user_status_data['status_ids'][0] );
	$thread_id = $user_status_rows[$status_id]['idThread'];

	$formated_status = JWStatus::FormatStatus( $user_status_rows[$status_id] );
	$status = $formated_status['status'];

	$timeCreate = $user_status_rows[$status_id]['timeCreate'];
	$duration = JWStatus::GetTimeDesc( $timeCreate );

	$replyto = $formated_status['replyto'];
	
	$thread_user = null;
	if ( $thread_id ) 
	{
		$thread_status = JWDB_Cache_Status::GetDbRowById( $thread_id );
		if ( $thread_status['idUser'] )
			$thread_user = JWDB_Cache_User::GetDbRowById( $thread_status['idUser'] );
	}

	$reply_link_string = "回复";
	$reply_status_id = ( $thread_id ) ? $thread_id : $status_id;
	if ( false == empty($thread_user) )
	{
		$replyto_link = "/$thread_user[nameUrl]/thread/$reply_status_id/$status_id";
	}
	else
	{
		$replyto_link = "/$user_name_url/thread/$reply_status_id/$status_id";
	}
}

$actions = JWSns::GetUserAction( $current_user_id, $user_id );
?>
<div id="wtTimelineLaunch">
   <div class="entry" id="status_<?php echo $status_id;?>">
     <div class="content">
        <div class="bg"></div>
        <?php echo $status;?><br />
		<?php if( $show_protected ) 
		{
		?>
        <div class="meta">
          <span class="floatright">
           <span class="reply"><a href=<?php echo $replyto_link;?>><?php echo $reply_link_string;?></a></span>
          </span>
        </div><!-- meta -->
		<?php } ?>
     </div><!-- content -->
   </div><!-- entry -->                                                                                                      <div class="Concerndiv">
     <ul class="Concern">
	 <?php 
	 if( true == $actions['follow']) 
	 {
		echo <<<_HTML_
		<li><a href="/wo/followings/follow/$user_id" onClick="return JWAction.follow($user_id);" class="Concern">关注$user_name_screen</a></li>
_HTML_;
     }
	 if( true == $actions['leave'] ) 
	 {
	 echo <<<_HTML_
	  <li><a href="/wo/followings/leave/$user_id" class="Concern" onclick="return confirm('请确认取消对 $user_row[nameScreen] 的关注');">取消关注</a></li>
_HTML_;
	 }
	if( true == $actions['on'])
	{
	 echo <<<_HTML_
        <li><a href="/wo/followings/on/$user_id" class="Concern">接收更新通知</a></li>
_HTML_;
	}
	if( true == $actions['off'] )
	{
		echo <<<_HTML_
		<li><a href="/wo/followings/off/$user_id" class="Concern">取消更新通知</a></li>
_HTML_;
	}
	if( true == $actions['d'] )
	{
	 echo <<<_HTML_
        <li><a href="/wo/direct_messages/create/$user_id" class="Concern">发送悄悄话</a></li>
_HTML_;
	}
	if( true == $actions['nudge']) 
	{
	 echo <<<_HTML_
		<li><a href="/wo/followings/nudge/$user_id" class="Concern">挠挠</a></li>
_HTML_;
	}
	if ( $current_user_id )
	{
		if ( $request_row_in = JWFollowerRequest::GetTableRow( $current_user_id, $user_id ) )
		{
			echo <<<_HTML_
			<li><a href="/wo/friend_requests/accept/$user_id" title="附言：$request_row_in[note]">接受关注请求</a></li>
			<li><a href="/wo/friend_requests/deny/$user_id">拒绝关注请求</a></li>
_HTML_;
		}
		if ( $request_row_out = JWFollowerRequest::GetTableRow( $user_id, $current_user_id ) )
		{
			echo <<<_HTML_
			<li><a href="/wo/friend_requests/cancel/$user_id" title="附言：$request_row_out[note]">取消请求关注</a></li>
_HTML_;
		}
	}
	?>
     </ul>
   </div>
</div>
