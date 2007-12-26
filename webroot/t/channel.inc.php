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

$follower_show_num = 12;

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
	{
		JWSession::SetInfo('error', '对不起，发送失败。');
	}
	else
	{
		JWSession::SetInfo('notice', '你的请求发送成功。');
		JWTemplate::RedirectBackToLastUrl('/');
	}
}

//get followers
$follower_ids = JWTagFollower::GetFollowerIds( $tag_row['id'] );
$follower_rows = JWDB_Cache_User::GetDbRowsByIds( $follower_ids );
$picture_ids = JWFunction::GetColArrayFromRows($follower_rows, 'idPicture');
$picture_url_rows = JWPicture::GetUrlRowByIds($picture_ids);
$follower_num = JWTagFollower::GetFollowerNum($tag_row['id']);
?>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php 
$tag_status_num = JWDB_Cache_Status::GetCountTopicByIdTag( $tag_row['id'] );

$pagination = new JWPagination($tag_status_num, $page);
$status_data = JWDB_Cache_Status::GetStatusIdsTopicByIdTag( $tag_row['id'], $pagination->GetNumPerPage(), $pagination->GetStartPos());
$status_rows = JWDB_Cache_Status::GetDbRowsByIds( $status_data['status_ids'] );

//get user info
$user_rows = JWDB_Cache_User::GetDbRowsByIds( $status_data['user_ids'] );

$keywords = $tag_row['name'];
$user_showed = array();
foreach ( $user_rows  as $user_id=>$one )
{
	if ( isset($user_showed[$user_id]) )
		continue;
	else
		$user_showed[$user_id] = true;

	$keywords .= "$one[nameScreen]($one[nameFull]) ";
}

$description = $tag_row['name'];
foreach ( $status_data['status_ids'] as $status_id )
{
	$description .= $status_rows[$status_id]['status'];
	if ( mb_strlen($description,'UTF-8') > 140 )
	{
			$description = mb_substr($description,0,140,'UTF-8');
			break;
	}
}

$options = array (
	'ui_user_id' => $current_user_id,
	'keywords' => $keywords,
	'description' => $description,
);
JWTemplate::html_head($options);
?>
</head>
<body class="normal"> 

<?php JWTemplate::accessibility() ?>
<?php JWTemplate::header('/t/'.$tag_row['name'].'/') ?> 
<?php
$has_following = JWTagFollower::IsFollower( $tag_row['id'], $current_user_id );
$follow_string = $has_following ? '已关注' : '关注此#';
?>

<div id="container">
	<div id="content">
	<?php JWTemplate::ShowActionResultTips(); ?>
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

JWTemplate::Timeline( $status_data['status_ids'],$user_rows,$status_rows, array(
'pagination' => $pagination,
'isInTag' => true,
));
?>

		</div><!-- wrapper end -->
	</div><!-- content end -->

<?php 
if ( $tag_row['name'] != '帮助留言板' )
{
	require_once(dirname(__FILE__).'/channel_sidebar.php');
}
else
{
	require_once(dirname(__FILE__).'/channel_help_sidebar.php');
}
?>

		<div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
  </div><!-- wtsidebar -->
        <div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->
<?php  JWTemplate::footer(); ?>          
</body>
</html>
