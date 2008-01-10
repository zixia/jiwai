<?php
$status = JWDB_Cache_Status::GetDbRowById( $mmsId );
if( empty( $status ) || $status['isMms'] == 'N' ){
	JWTemplate::RedirectTo404NotFound();
}
$picture = JWDB_Cache_Picture::GetDbRowById( $status['idPicture'] );
if( empty( $picture ) ){
	JWTemplate::RedirectTo404NotFound();
}

$page_user_info = JWUser::GetUserInfo( $page_user_id );
$photo_url = JWPicture::GetUrlById( $status['idPicture'] , 'picture' );
$photo_name = $picture['fileName'];

$current_user_id = JWLogin::GetCurrentUserId();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<?php 
$head_options = array ( 'ui_user_id'=>$page_user_id );
JWTemplate::html_head($head_options) ;
?>
</head>

<body class="account" id="create">

<?php JWTemplate::header() ?>

<div id="container">
	<h2>
		<?php echo htmlSpecialChars($page_user_info['nameFull']); ?>的彩信消息 -- <?php echo $photo_name; ?>
		<span class="h2note">
			<span>拍摄时间:<?php echo $status['timeCreate'];?></span>
			<div id="status_action_<?echo $status['id'];?>">
<?php
$reply_count = JWDB_Cache_Status::GetCountReply($status['id']);
echo "<a href=".JW_SRVNAME."/${page_user_info['nameUrl']}/thread/${status['id']}/${status['id']} >";
echo 0<$reply_count?$reply_count."条回复":"回复";
echo "</a>";
if( $current_user_id ) {
	$is_fav = JWFavourite::IsFavourite($current_user_id,$status['id']);
	echo JWTemplate::FavouriteAction($status['id'],$is_fav);
}
if( $page_user_id == $current_user_id ) {
	echo JWTemplate::TrashAction($status['id']);
}
?>
			</div>
		</span>
	</h2>
	<div class="bigimg" style="text-align:center;">
		<img src="<?php echo $photo_url; ?>" title="<?php echo $photo_name;?>" alt="<?php echo $photo_name;?>" />
		<div><?php echo $status['status']; ?></div><br/>
	<input type="button" class="submitbutton" onclick="javascript:location.href='<?php echo JW_SRVNAME ."/${page_user_info['nameUrl']}/mms/";?>';" value="返回" />
	</div>
	<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>
</div><!-- #container -->
<?php JWTemplate::footer(); ?>

</body>
</html>
