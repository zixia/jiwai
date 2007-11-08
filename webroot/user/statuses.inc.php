<?php
function user_status($idPageUser, $idStatus)
{
	JWTemplate::html_doctype();

	$status_rows	= JWStatus::GetStatusDbRowsByIds(array($idStatus));
	$status_info	= @$status_rows[$idStatus];

	$page_user_info	= JWUser::GetUserInfo($idPageUser);
	if ( $status_info['idUser']!==$idPageUser && false == ( @$status_info['idConference']!=null 
						&& $status_info['idConference'] == $page_user_info['idConference'] 
					) 
		)
	{
		JWTemplate::RedirectTo404NotFound();
		exit(0);
	}

	$logined_user_info	= JWUser::GetCurrentUserInfo();

	$formated_status 	= JWStatus::FormatStatus($status_info,false);

	$pettyDevice = JWDevice::GetNameFromType( $status_info['device'], $status_info['idPartner'] );

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
            <div class="head"><a href="/<?php echo $page_user_info['nameUrl'];?>/"><img alt="<?php echo $page_user_info['nameFull'];?>" src="<?php echo JWPicture::GetUserIconUrl($page_user_info['id'], 'thumb96');?>" width="96" height="96"/></a></div>
            <div class="cont">
                <div class="bg"></div>
                <table width="100%" border="0" cellspacing="5" cellpadding="0">
                <tr>
                    <td><h3><?php echo $page_user_info['nameScreen'];?></h3></td>
                    <td align="right">
                        <?php 
                        if ( ! empty($logined_user_info) && $logined_user_info['idUser'] != $idPageUser ) {
                            if ( JWFriend::IsFriend($logined_user_info['idUser'], $idPageUser) ) {
                                echo "$page_user_info[nameScreen]被你关注";
                            }else{
				if( false == JWBlock::IsBlocked( $idPageUser, $logined_user_info['id'] ) )
				echo "<a href='/wo/friendships/create/$idPageUser'>关注$page_user_info[nameScreen]</a>";
                            }
                        } 
                        ?>
                    </td>
                </tr>
<?php if ( $protected ) { ?>
    				<tr>
    		  			<td colspan="2">我只和我关注的人分享我的叽歪de。</td>
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
                    <td><span class="meta"><a href="/<?php echo $page_user_info['nameUrl'];?>/statuses/<?php echo $status_info['idStatus'];?>"><?php echo JWStatus::GetTimeDesc($status_info['timeCreate']);?></a>来自于 <?php echo $pettyDevice;?><span id="status_actions_<?php echo $status_info['idStatus'];?>"></span></span></td>
                    <?php if ( JWLogin::IsLogined() ) { 

                        $id_user_logined 	= JWLogin::GetCurrentUserId();
                        $is_fav				= JWFavourite::IsFavourite($id_user_logined,$status_info['idStatus']);
                        $trashAction = null;
                        if( JWUser::IsAdmin( $id_user_logined ) || $id_user_logined == $idPageUser ) 
                            $trashAction = JWTemplate::TrashAction( $idStatus );
                        
                    ?> 

                    <td align="right"><?php echo JWTemplate::FavouriteAction($status_info['idStatus'], $is_fav); ?><?php echo $trashAction; ?></td>
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
