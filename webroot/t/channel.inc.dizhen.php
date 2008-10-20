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

$follower_show_num = 36;

//jiwai sth
if( isset($_REQUEST['jw_status']))
{

	JWLogin::MustLogined(true);

	$message = $_REQUEST['jw_status'];
	$message = trim($message);

	$options_info = array(
		'idTag' => $tag_row['id'],
	);
	$is_status_id = JWSns::UpdateStatus($current_user_id, $message, 'web', null, 'web@jiwai.de', $options_info);	
	if( false == $is_status_id )
	{
		JWSession::SetInfo('error', '对不起，发送失败。');
	}
	else
	{
		JWSession::SetInfo('notice', '你的消息发送成功。');
		JWTemplate::RedirectToUrl(null);
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
<script>
function send_dongzai(s)
{
	var caption = '告诉朋友';
	var url ='/wo/lightbox/topic_dizhen';
	var rel='';

	var options = {
		height : 280,
		 width : 350
	};

	TB_show(caption, url, rel, options);
	return false;
}
</script>
<?php 
$tag_status_num = JWStatus::GetCountFromDiZhen();
//$tag_status_num = JWDB_Cache_Status::GetCountTopicByIdTag( $tag_row['id'] );

$pagination = new JWPagination($tag_status_num, $page);
$status_data = JWStatus::GetStatusIdsFromDiZhen();
$status_data = JWStatus::GetStatusIdsFromDiZhen($pagination->GetNumPerPage(), $pagination->GetStartPos());
//$status_data = JWDB_Cache_Status::GetStatusIdsTopicByIdTag( $tag_row['id'], $pagination->GetNumPerPage(), $pagination->GetStartPos());

$status_rows = $user_rows = array();
if ( false==empty($status_data) )
{
	$status_rows = JWDB_Cache_Status::GetDbRowsByIds( $status_data['status_ids'] );
	$user_rows = JWDB_Cache_User::GetDbRowsByIds( $status_data['user_ids'] );
}

/* meta info [ keywords, description ] */
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
foreach ( $status_rows AS $status_id=>$one )
{
	$description .= $one['status'];
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
	'rss' => array(
		array(
			'type' => 'rss',
			'title' => '频道：'.$tag_row['name'],
			'url' => 'http://api.jiwai.de/statuses/channel_timeline/'.$tag_row['id'].'.rss',
		),
	),
);
JWTemplate::html_head($options);
?>
</head>
<body class="normal">

<?php JWTemplate::accessibility() ?>
<?php JWTemplate::header('/t/'.$tag_row['name'].'/') ?> 
<?php
$has_following = JWTagFollower::IsFollower( $tag_row['id'], $current_user_id );
$follow_string = $has_following ? '已关注' : '关注['.$tag_row['name'].']';
?>

<div id="container">
	<div id="content">
	<?php JWTemplate::ShowActionResultTips(); ?>
	<div id="wtchannel">
	<div class="cha_tit" style="height:252px;">
		<span class="pad"><?php if($has_following) echo $follow_string; else { ?> <a href="<?php echo JW_SRVNAME .'/wo/followings/followchannel/' .$tag_row['id']; ?>" onClick="return JWAction.redirect(this);"><?php echo $follow_string;?><?php } ?></a></span>[<?php echo $tag_row['name'];?>]
		<p style="padding-top:5px; clear:both; font-size:14px; font-weight:normal;"><a href="http://gongyi.qq.com/juanzeng/llj_dizhen.htm?http://jiwai.de" target="_blank">为2008.05.12四川地震紧急网络捐赠(腾讯QQ)</a></p>
		<p style="padding-top:5px; clear:both; font-size:14px; font-weight:normal;"><a href="<?echo JW_SRVNAME;?>/地震新闻/" target="_blank">查看更多地震新闻</a></p>
		<p style="padding-top:5px; clear:both; font-size:14px; font-weight:normal;"><a href="<?echo JW_SRVNAME;?>/%E5%9C%B0%E9%9C%87%E7%9F%A5%E8%AF%86/thread/8815523/8815523" target="_blank">制定家庭防震计划</a></p>
		<p style="padding-top:5px; clear:both; font-size:14px; font-weight:normal;"><a href="<?echo JW_SRVNAME;?>/%E5%9C%B0%E9%9C%87%E7%9F%A5%E8%AF%86/thread/8815523/8815453" target="_blank">避震要点：身体应采取的姿势</a></p>
		<p style="padding-top:5px; clear:both; font-size:14px; font-weight:normal;"><a href="<?echo JW_SRVNAME;?>/%E5%9C%B0%E9%9C%87%E7%9F%A5%E8%AF%86/thread/8815523/8815383" target="_blank">地震时的几个自救办法 </a></p>
		<p style="padding-top:5px; clear:both; font-size:14px; font-weight:normal;"><a href="<?echo JW_SRVNAME;?>/%E5%9C%B0%E9%9C%87%E7%9F%A5%E8%AF%86/thread/8815523/8815305" target="_blank">在公共场所怎样避震</a></p>
		<p style="padding-top:5px; clear:both; font-size:14px; font-weight:normal;"><a href="<?echo JW_SRVNAME;?>/%E5%9C%B0%E9%9C%87%E7%9F%A5%E8%AF%86/thread/8815523/8814501" target="_blank">地震中逃生十大法则</a></p>
		<p style="padding-top:5px; clear:both; font-size:14px; font-weight:normal;"><a href="<?echo JW_SRVNAME;?>/%E5%9C%B0%E9%9C%87%E7%9F%A5%E8%AF%86/thread/8815523/8813866" target="_blank">地震时的应急防护原则</a></p>
		<p style="padding-top:5px; clear:both; font-size:12px; font-weight:normal;">发送短信到<span style="font-weight:bold;">1066808866002</span>，直播你的所见所闻</p>
		<p style="padding-top:5px; clear:both; font-size:12px; font-weight:normal;">发送彩信，内容用"<span style="font-weight:bold;">[<?php echo $tag_row['name'];?>]</span>"开头，到<span style="font-weight:bold;">m@jiwai.de</span></p>
		<p style="padding-top:5px; clear:both; font-size:12px; font-weight:normal;"><input type="button" class="submitbutton" value="告诉朋友" onClick="send_dongzai('dizhen');"/></p>
	</div>
	</div>
		<div id="wrapper">
<?php
JWTemplate::ShowActionResultTips();

$options=array(		
	'title' =>'你想发表新话题？',
	'mode'=>'2',
);
JWTemplate::updater( $options );

if (false==empty($status_data)) 
{
	JWTemplate::Timeline( $status_data['status_ids'],$user_rows,$status_rows, array(
		'pagination' => $pagination,
		'idInTag' => 26559,
	));
}
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
