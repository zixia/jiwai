<?php
	function Timeline($statusIds, $userRows, $statusRows, $options=array() )
	{
		global $idUser;
		$host = 'http://jiwai.de';
		if ( empty($statusIds) || empty($userRows) || empty($statusRows) )
			return;

		if ( !isset($options['icon']) )
			$options['icon'] 	= false;
		if ( !isset($options['trash']) )
			$options['trash'] 	= false;
		if ( !isset($options['uniq']) )
			$options['uniq']	= 0;
		if ( !isset($options['nummax']) )
			$options['nummax']	= 0;
		if ( !isset($options['protected']) )
			$options['protected']	= false;

		$current_user_id = 0; //$idUser;
?>

				<table class="doing" id="timeline" cellspacing="0" cellpadding="0">    
<?php
		$n=0;
		$user_showed = array();

		foreach ( $statusIds as $status_id ){
			if( !isset($statusRows[$status_id]) )
				continue;

			$user_id 	= $statusRows[$status_id]['idUser'];

			if ( $options['protected'] && JWUser::IsProtected($user_id) )
				continue;
				
			// 最多显示的条数已经达到
			if ( $options['nummax'] && $n >= $options['nummax'] )
				break;

			// 如果设置了一个用户只显示一条，则跳过
			if ( $options['uniq']>0 && @$user_showed[$user_id]>=$options['uniq'] )
				continue;
			else
				@$user_showed[$user_id] += 1;
				
			$name_screen= $userRows[$user_id]['nameScreen'];
			$name_full	= $userRows[$user_id]['nameFull'];
			$status		= $statusRows[$status_id]['status'];
			$timeCreate	= $statusRows[$status_id]['timeCreate'];
			$device		= $statusRows[$status_id]['device'];
			$reply_id	= $statusRows[$status_id]['idStatusReplyTo'];
			$sign		= ( $statusRows[$status_id]['isSignature'] == 'Y' ) ?
						'signature' : '';
			
			$duration	= date('G:i M j', strtotime($timeCreate));//JWStatus::GetTimeDesc($timeCreate);

			if ( !empty($statusRows[$status_id]['idPicture']) )
				$photo_url	= JWPicture::GetUrlById($statusRows[$status_id]['idPicture']);
			else
				$photo_url	= JWPicture::GetUserIconUrl($user_id);
	
			$device		= JWDevice::GetNameFromType($device);

			$formated_status 	= JWStatus::FormatStatus($status);

			$replyto			= $formated_status['replyto'];
			$status				= htmlspecialchars($status);
?>
					<tr class="<?php echo $n++%2?'even':'odd';?>" id="status_<?php echo $status_id;?>">
<?php if ( $options['icon'] ){ ?>
						<td class="thumb">
							<a href="<?php echo $host; ?>/<?php echo $name_screen;?>"><img alt="<?php echo $name_screen;?>" 
									src="<?php echo $photo_url?>"/></a>
						</td>
<?php } ?>
						<td>	
<?php if ( $options['icon'] ){ ?>
							<strong>
								<a href="<?php echo $host; ?>/<?php echo $name_screen; ?>" 
										title="<?php echo $name_full?>"><?php echo $name_full; ?></a>
							</strong>
<?php } ?>

							<?php echo $status?>
			
							<span class="meta">
								<?php if (is_numeric($status_id)) {?>
								<a href="<?php echo $host; ?>/<?php echo $name_screen; ?>/statuses/<?php echo $status_id?>"><?php echo $duration?></a>
								<?php } else {
									echo $duration;	
								} ?>
								via <?php echo "$device $sign"?> 
<?php 
		if (!empty($replyto) )
		{
			if ( empty($reply_id) )
				echo " <a href='$host/$replyto/'>in reply of ${replyto}</a> ";
			else
				echo " <a href='$host/$replyto/statuses/$reply_id'>in reply of ${replyto}</a> " ;
		}
?>

								<span id="status_actions_<?php echo $status_id?>">

<?php	
if ( isset($current_user_id) && is_numeric($status_id) )	
{
	$is_fav	= JWFavourite::IsFavourite($current_user_id,$status_id);

	echo self::FavouriteAction($status_id,$is_fav);
	if ( ( JWUser::IsAdmin($current_user_id) || $current_user_id==$user_id  )
			&& $options['trash'] )
	{
		//是自己的 status 可以删除
		echo self::TrashAction($status_id);
	}
}
?>

								</span>

							</span>

						</td>
					</tr>
<?php
				}
?>
				</table>
<?php
	}


$status_data 	= JWDB_Cache_Status::GetStatusIdsFromFriends($idUser, 20);
$status_rows	= JWDB_Cache_Status::GetDbRowsByIds($status_data['status_ids']);
if(!empty($status_rows)) {
	$mergedStatusResult = JWStatusQuarantine::GetMergedQuarantineStatusFromUser(
			$idUser, $status_data['status_ids'], $status_rows);
	if( !empty( $mergedStatusResult ) ) {
		$status_data['status_ids'] = $mergedStatusResult['status_ids'];
		$status_rows = $mergedStatusResult['status_rows'];
	}
}
$user_rows		= JWUser::GetUserDbRowsByIds	($status_data['user_ids']);
Timeline($status_data['status_ids'], $user_rows, $status_rows);
//FIXME friends' status not refresh on f8 profile

?>
