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
			echo '<div class="sidediv2"><a href="'.JW_SRVNAME .'/wo/followings/followchannel/' . $tag_row['id'].'" class="pad">关注这里</a></div>';
		}
		if( $action_row['leave'] ) 
		{
			echo '<div class="sidediv2"><a href="'.JW_SRVNAME .'/wo/followings/leavechannel/' . $tag_row['id'].'" class="pad">取消关注这里</a></div>';
		}
		if( $action_row['on'] ) 
		{
			echo '<div class="sidediv2"><a href="'.JW_SRVNAME .'/wo/followings/onchannel/' . $tag_row['id'].'" class="pad">接受这里更新通知</a></div>';
		}
		if( $action_row['off'] ) 
		{
			echo '<div class="sidediv2"><a href="'.JW_SRVNAME .'/wo/followings/offchannel/' . $tag_row['id'].'" class="pad">取消这里更新通知</a></div>';
		}
?>
        <div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
		<div class="line"><div></div></div>

<?php if ( $current_user_id ) { ?>
        <a href="<?php echo JW_SRVNAME .'/' .$current_user_info['nameScreen'] .'/t/' .$tag_row['name'].'/';?>" class="pad" style="margin-left:12px;">我在这里的叽歪</a>
<?php } ?>

<a href="http://api.jiwai.de/statuses/channel_timeline/<?php echo $tag_row['id']; ?>.rss" class="rsshim">订阅[<?php echo $tag_row['name'];  ?>]的消息</a>
		<div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
  </div><!-- wtsidebar -->
