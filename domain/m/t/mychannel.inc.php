<?php 
require_once(dirname(__FILE__) . '/../../jiwai.inc.php');
JWTemplate::html_doctype();
//page and pagination
$page = null;
extract($_GET, EXTR_IF_EXISTS);
$current_user_info  = JWUser::GetCurrentUserInfo();
$current_user_id    = $current_user_info['id'];
$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page = ($page < 1 ) ? 1 : $page;

//jiwai sth
if( isset($_REQUEST['jw_status']))
{

	JWLogin::MustLogined();

	$message = $_REQUEST['jw_status'];
	$message = trim($message);

	$options_info = array(
		'idTag' => $tag_row['id'],
	);
	$is_status_id = JWSns::UpdateStatus($current_user_id, $message, 'web', null, 'N', 'web@jiwai.de', $options_info);	
	if( false == $is_status_id )
		JWSession::SetInfo('error', '对不起，发送失败。');
	else
		JWTemplate::RedirectBackToLastUrl('/');
}
?>

 <html xmlns="http://www.w3.org/1999/xhtml">

 <head>
 <?php 
 $options = array ('ui_user_id' => $current_user_id );
 JWTemplate::html_head($options);
 ?>
</head>
<body class="normal"> 

<?php JWTemplate::accessibility() ?>
<?php JWTemplate::header() ?> 
<?php
$has_following = JWTagFollower::IsFollower( $tag_row['id'], $current_user_id );
$follow_string = $has_following ? '已关注' : '关注此#';
?>

<div id="container">
	<div id="content">
	<div id="wtchannel">
	<div class="cha_tit"><span class="pad"><?php if($has_following) echo $follow_string; else { ?> <a href="<?php echo JW_SRVNAME .'/wo/followings/followchannel/' .$tag_row['id']; ?>"><?php echo $follow_string;?><?php } ?></a></span>#<?php echo $tag_row['name'];?></div>
	</div>
		<div id="wrapper">
<?php
JWTemplate::ShowActionResultTips();

$options=array(		
	'title' =>'你想发表新话题？',
	'mode'=>'2',
);
JWTemplate::updater( $options );

$user_status_num = JWDB_Cache_Status::GetCountTopicByIdTag( $tag_row['id'] );

$pagination = new JWPagination($user_status_num, $page);
$status_data = JWDB_Cache_Status::GetStatusIdsTopicByIdTag( $tag_row['id'], $pagination->GetNumPerPage(), $pagination->GetStartPos());
$status_rows = JWDB_Cache_Status::GetDbRowsByIds( $status_data['status_ids'] );

//get user info
$user_rows = JWDB_Cache_User::GetDbRowsByIds( $status_data['user_ids'] );
JWTemplate::Timeline( $status_data['status_ids'],$user_rows,$status_rows, array(
'pagination' => $pagination,
'isInTag' => true,
));
?>

		</div><!-- wrapper end -->
	</div><!-- content end -->
  <div id="wtchannelsidebar">
  <div class="sidediv">
      <h2 class="forul">最近加入关注</h2>
	  <div class="com" id="friend">
	  <?php
	  $current_num = 1;

	  $n = 0;
	  foreach($follower_ids as $follower_id ){
		  // foreach( $follower_ids as $follower_id ) {
		  $follower_info        = $follower_rows[$follower_id];
		  $picture_url        = JWTemplate::GetConst('UrlStrangerPicture');

		  $follower_picture_id  = @$follower_info['idPicture'];
		  if ( $follower_picture_id )
			  $picture_url    = $picture_url_rows[$follower_picture_id];

		  if( $n % 4==0 ) echo '<ul class="list">';
				 ?>
		<li><a href="/<?php echo $follower_info['nameScreen']?>/" title="<?php echo $follower_info['nameFull']?>" rel="contact"><img src="<?php echo $picture_url;?>" title="<?php echo $follower_info['nameFull']; ?>" border="0" /><span><?php echo $follower_info['nameScreen'];?></span></a></li>
	<?php  
	if( $n % 4 == 3 ) echo '</ul>';
				 if( $n >= $follower_show_num ) 
					 break;
				 $n++;
	}
	  if( $n % 4!=1 ) echo "</ul>";
	?>		
		</div><!-- sidediv -->
    <div class="sec" style="display:none;"><a href="#">浏览全部关注者(<?php echo $follower_num?>)</a></div>
		<div style="overflow: hidden; clear: both; height:16px; line-height: 1px; font-size: 1px;"></div>
<?php
		$action_row = JWSns::GetTagAction( $current_user_id, $tag_row['id'] );
		if( $action_row['follow'] )
		{
			echo '<div class="sidediv2"><a href="'.JW_SRVNAME .'/wo/followings/followchannel/' . $tag_row['id'].'" class="pad">关注此#</a></div>';
		}
		if( $action_row['leave'] ) 
		{
			echo '<div class="sidediv2"><a href="'.JW_SRVNAME .'/wo/followings/leavechannel/' . $tag_row['id'].'" class="pad">取消关注此#</a></div>';
		}
		if( $action_row['on'] ) 
		{
			echo '<div class="sidediv2"><a href="'.JW_SRVNAME .'/wo/followings/onchannel/' . $tag_row['id'].'" class="pad">接受此#更新通知</a></div>';
		}
		if( $action_row['off'] ) 
		{
			echo '<div class="sidediv2"><a href="'.JW_SRVNAME .'/wo/followings/offchannel/' . $tag_row['id'].'" class="pad">取消此#更新通知</a></div>';
		}
?>
		
        <div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
		<div class="line"><div></div></div>

<?php if ( $current_user_id ) { ?>
        <a href="<?php echo JW_SRVNAME .'/' .$current_user_info['nameScreen'] .'/t/' .$tag_row['name'].'/';?>" class="pad" style="margin-left:12px;">我在此#中的叽歪</a>
<?php } ?>

<a href="http://api.jiwai.de/statuses/channel_timeline/<?php echo $tag_row['id']; ?>.rss" class="rsshim">订阅#<?php echo $tag_row['name'];  ?>的消息</a>
		<div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
  </div><!-- wtsidebar -->
        <div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->
<?php  JWTemplate::footer(); ?>          
</body>
</html>
