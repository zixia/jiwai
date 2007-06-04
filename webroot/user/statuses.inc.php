<?php
function user_status($idPageUser, $idStatus)
{
	JWTemplate::html_doctype();

	$status_rows	= JWStatus::GetStatusDbRowsByIds(array($idStatus));
	$status_info	= $status_rows[$idStatus];

	$page_user_info	= JWUser::GetUserInfo($idPageUser);

	if ( $status_info['idUser']!==$idPageUser )
	{
		$_SESSION['404URL'] = $_SERVER['SCRIPT_URI'];
		header ( "Location: " . JWTemplate::GetConst("UrlError404") );
		exit(0);
	}

	$logined_user_info	= JWUser::GetCurrentUserInfo();

	$formated_status 	= JWStatus::FormatStatus($status_info['status']);
?>

<html>

<?php JWTemplate::html_head() ?>

<body class="status" id="show">

<?php //JWTemplate::accessibility() ?>

<?php //JWTemplate::header() ?>

<div class="separator"></div>

<style type="text/css">
h2.thumb, h2.thumb a {
color:#000000;
}
#content div.desc{
background: transparent url()
}
</style>

<div id="container">
	<div id="content">
		<div id="wrapper">


			<div id="permalink">
	
	
    			<div class="desc">

					<!-- google_ad_section_start -->

    				<h1>
    		  			<?php echo $formated_status['status'] ?>
    				</h1>

<?php
	$duration	= JWStatus::GetTimeDesc($status_info['timeCreate']);

	$device = $status_info['device'];

	if ( 'sms'==$device )
		$device='手机';
	else
		$device=strtoupper($device);


?>
					<!-- google_ad_section_end -->

    				<p class="meta">
    					<span class="meta">
							<?php echo $duration?>
    				    	来自于 <?php echo $device?>
							<span id="status_actions_<?php echo $status_info['idStatus']?>">
<?php 
if ( JWLogin::IsLogined() )	
{
	$id_user_logined 	= JWLogin::GetCurrentUserId();
	$is_fav				= JWFavourite::IsFavourite($id_user_logined,$status_info['idStatus']);

	echo JWTemplate::FavouriteAction($status_info['idStatus'],$is_fav);
	if ( JWUser::IsAdmin($id_user_logined) || $id_user_logined==$idPageUser ) {
		echo JWTemplate::TrashAction($idStatus);
	}
}

	$replyto = $formated_status['replyto'];

	if (!empty($replyto))
	{
		if ( empty($status_info['idStatusReplyTo']) )
			echo " <a href='/$replyto/'>给 ${replyto} 的回复</a> ";
		else
			echo " <a href='/$replyto/statuses/$status_info[idStatusReplyTo]'>给 ${replyto} 的回复</a> ";
	}


?>
							</span>
    					</span>
    				</p>
    			</div>

			<h2 class="thumb">
				<a href="/<?php echo $page_user_info['nameScreen']?>/"><img alt="<?php echo $page_user_info['nameFull']?>" src="<?php echo JWPicture::GetUserIconUrl($page_user_info['id'],'thumb48')?>" /></a>
				<a href="/<?php echo $page_user_info['nameScreen']?>/"><?php echo $page_user_info['nameFull']?></a>
			</h2>

			<div id="ad">
<script type="text/javascript"><!--
google_ad_client = "pub-8383497624729613";
google_ad_width = 234;
google_ad_height = 60;
google_ad_format = "234x60_as";
google_ad_type = "text_image";
//2007-04-30: JiWai.de - Statuses
google_ad_channel = "3074588979";
google_color_border = "99CC00";
google_color_bg = "C3E169";
google_color_link = "669900";
google_color_text = "333333";
google_color_url = "669900";

google_language = 'zh-CN';
//-->
</script>
<script type="text/javascript"
  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
<script type="text/javascript" id="google_show_ads">
</script>
			</div>

<script type="text/javascript"><!--
/*
setTimeout("show_ad()", 1000);
function show_ad()
{
alert(google_ad_width);
	$('google_show_ads').src="http://pagead2.googlesyndication.com/pagead/show_ads.js";
	ad_element = document.createElement("script")
	ad_element.type = "text/javascript";
	ad_element.language = 'javascript';
	ad_element.src 	= "http://pagead2.googlesyndication.com/pagead/show_ads.js"
	ad_element.id 	= "show_ads";
	document.getElementById('ad').appendChild(ad_element);
}
*/
</script>

		</div><!-- wrapper -->
	</div><!-- content -->

</div><!-- #container -->

<hr class="separator" />

<?php //JWTemplate::footer() ?>

</body>
</html>

<?php 
}  // end function
?>
