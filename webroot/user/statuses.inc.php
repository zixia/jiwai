<?php
function user_status($idPageUser, $idStatus)
{
	JWTemplate::html_doctype();

	$status_rows	= JWStatus::GetStatusDbRowsByIds(array($idStatus));
	$status_info	= @$status_rows[$idStatus];


	if ( $status_info['idUser']!==$idPageUser )
	{
		JWTemplate::RedirectTo404NotFound();
		exit(0);
	}

	$page_user_info	= JWUser::GetUserInfo($idPageUser);

	$logined_user_info	= JWUser::GetCurrentUserInfo();

	$formated_status 	= JWStatus::FormatStatus($status_info['status'],false);
    $pettyDevice = JWDevice::GetNameFromType( $status_info['device'] );

	$protected = false;
	if ( JWUser::IsProtected($idPageUser) )
	{
		$protected = true;
		if ( ! empty($logined_user_info) )
		{
			if ( JWFriend::IsFriend($idPageUser, $logined_user_info['idUser']) || $logined_user_info['idUser']==$idPageUser )
				$protected = false;
		}
	}

?>
<html>

<head>

<style type="text/css">
h2.thumb, h2.thumb a {
color:#000000;
}
#content div.desc{
background: transparent url()
}
</style>


<?php 
$head_options = array ( 'ui_user_id'=>$idPageUser );
JWTemplate::html_head($head_options) ;
?>
</head>

<body class="status" id="show">

<?php //JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>



<div id="container">
	<div id="content">
    <div id="wrapper">
    <div id="permalink">

        <div class="odd">
            <div class="head">
                <a href="/<?php echo $page_user_info['nameScreen'];?>/"><img alt="<?php echo $page_user_info['nameFull'];?>" src="<?php echo JWPicture::GetUserIconUrl($page_user_info['id'], 'thumb48');?>" width="94" height="94"/></a>
            </div>
            <div class="cont">
                <div class="bg"></div>
                <table width="100%" border="0" cellspacing="5" cellpadding="0">
                <tr>
                    <td><h3><?php echo $page_user_info['nameFull'];?></h3></td>
                    <td align="right">
                        <?php 
                        if ( ! empty($logined_user_info) && $logined_user_info['idUser'] != $idPageUser ) {
                            if ( JWFriend::IsFriend($logined_user_info['idUser'], $idPageUser) ) {
                                echo "$page_user_info[nameFull]是你的好友";
                            }else{
                                echo "<a href='/wo/friendships/create/$idPageUser'>加$page_user_info[nameFull]为好友</a>";
                            }
                        } 
                        ?>
                    </td>
                </tr>
<?php if ( $protected ) { ?>
    				<tr>
    		  			<td colspan="2">我只和我的好友分享我的叽歪de。</td>
    				</tr>
<?php }else{ 
    
	$replyto = $formated_status['replyto'];
    $replyHtml = null;
	if (!empty($replyto)) {
		if ( empty($status_info['idStatusReplyTo']) )
			$replyHtml = " <a href='/$replyto/'>给 ${replyto} 的回复</a> ";
		else
			$replyHtml = " <a href='/$replyto/statuses/$status_info[idStatusReplyTo]'>给 ${replyto} 的回复</a> ";
	}
    
?>
                <tr>
                    <td colspan="2"><?php echo $formated_status['status'] ?><?php echo $replyHtml; ?></td>
                </tr>
                <tr>
                    <td><span class="meta"><a href="/sunhquan/statuses/<?php echo $status_info['idStatus'];?>"><?php echo JWStatus::GetTimeDesc($status_info['timeCreate']);?></a>来自于 <?php echo $pettyDevice;?><span id="status_actions_<?php echo $status_info['idStatus'];?>"></span></span></td>
                    <?php if ( JWLogin::IsLogined() ) { 

                        $id_user_logined 	= JWLogin::GetCurrentUserId();
                        $is_fav				= JWFavourite::IsFavourite($id_user_logined,$status_info['idStatus']);
                        $trashAction = null;
                        if( JWUser::IsAdmin( $id_user_logined ) || $id_user_logined == $idPageUser ) 
                            $trashAction = JWTemplate::TrashAction( $idStatus );
                        
                    ?> 

                    <td align="right"><?php echo JWTemplate::FavouriteAction($status_info['idStatus'], $is_fav); ?><img src="images/org-icon-mark.gif" alt="标记" width="16" height="16" align="absmiddle" /><a href="#">标记</a><?php echo $trashAction; ?></td>
                    <?php } else { ?>
                    <td>&nbsp;</td>
                    <?php } ?>
                </tr>
<?php } ?>
                </table>
            </div>
        </div>
        
        </div>
		</div><!-- wrapper -->
	</div><!-- content -->

<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>
</div><!-- #container -->
<?php JWTemplate::footer(); ?>

</body>
</html>

<?php } ?>
