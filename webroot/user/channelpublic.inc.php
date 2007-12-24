<?php

JWTemplate::html_doctype();

$page = isset( $_REQUEST['page'] ) ? intval( $_REQUEST['page'] ) : 1;
$page = ( $page < 1 ) ? 1 : $page;

?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<?php
/* move to top for meta-seo  */
$user_status_num = JWDB_Cache_Status::GetCountPostByIdTagAndIdUser($tag_row['id'], $page_user_id);
$pagination = new JWPagination($user_status_num, $page);
$status_data = JWDB_Cache_Status::GetStatusIdsPostByIdTagAndIdUser($tag_row['id'], $page_user_id, $pagination->GetNumPerPage(), $pagination->GetStartPos() );
$status_rows = JWDB_Cache_Status::GetDbRowsByIds( $status_data['status_ids'] );
$user_rows = JWUser::GetDbRowsByIds($status_data['user_ids']);

/* meta-seo content */
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
foreach ( $status_rows as $one )
{
	$description .= $one['status'];
	if ( mb_strlen($description,'UTF-8') > 140 )
	{
			$description = mb_substr($description,0,140,'UTF-8');
			break;
	}
}

$head_options = array(
	'ui_user_id' => JWLogin::GetCurrentUserId(),
	'keywords' => $keywords,
	'description' => $description,
);
JWTemplate::html_head($head_options);
?>

</head>
<body class="normal">
<?php JWTemplate::accessibility() ?>
<?php JWTemplate::header() ?>

<div id="container">
	<div id="content">
	<div id="wtchannel">
	<div class="cha_tit"><span class="pad"><a href="<?php echo JW_SRVNAME .'/t/' .$tag_row['name']. '/';?>">查看大家的</a></span>#<?php echo $tag_row['name'];?></div>
	</div>

<!-- wtTimeline start -->
<?php
JWTemplate::Timeline( $status_data['status_ids'], $user_rows, $status_rows, array(
	'pagination'=>$pagination, 
));
?>

</div><!-- content -->
<div id="wtchannelsidebar">
		<div class="sidediv">
		<h2 class="forul">云</h2>
		<div class="clouddiv">
<?php
	$tag_info_rows = JWStatus::GetTagIdsPostByIdUser( $page_user_id );
	$count_tag_all_post = 0;
	foreach( $tag_info_rows as $k => $v )
	{
		$count_tag_all_post += $v;
	}
	
	$count_style = 3;
	foreach( $tag_info_rows as $k => $v )
	{
		$count_tag_post = $v;
		$tag_row = JWTag::GetDbRowById( $k );
		echo '<a href="' .JW_SRVNAME. "/$nameScreen/t/" .$tag_row['name']. '/" class="cloud';
		echo intval($count_tag_post * $count_style / $count_tag_all_post)+1 .'">'. $tag_row['name'];
		echo '</a>';
	}
?>
</div>
        <div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
		</div><!-- sidediv -->
  </div><!-- wtsidebar -->
<div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->

<?php  JWTemplate::footer(); ?>          

</body>
</html>
