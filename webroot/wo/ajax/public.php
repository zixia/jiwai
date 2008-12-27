<?
require_once('../../../jiwai.inc.php');
$current_user_id = JWUser::GetCurrentUserInfo('id');
$status_data 	= JWStatus::GetStatusIdsFromPublic(100);
$statusRows	= JWStatus::GetDbRowsByIds($status_data['status_ids']);
$userRows		= JWDB_Cache_User::GetDbRowsByIds	($status_data['user_ids']);
$statusIds = $status_data['status_ids'];
$options = array(
	'nummax' => 20,
	'uniq' => 2,
);
		$n=0;
		$user_showed = array();
		foreach ( $statusIds as $status_id ){

			$user_id 	= $statusRows[$status_id]['idUser'];
			if ( JWSns::IsProtectedStatus( $statusRows[$status_id], $current_user_id ) )
				continue;

			// 最多显示的条数已经达到
			if ( $options['nummax'] && $n >= $options['nummax'] )
				break;
			// 如果设置了一个用户只显示一条，则跳过
			if ( $options['uniq']>0 && @$user_showed[$user_id]>=$options['uniq'] )
				continue;

				@$user_showed[$user_id] += 1;
				$n++;
				$user_info = $userRows[$user_id];
				$pic = JWPicture::GetUrlById( $user_info['idPicture'] );
				$status = $statusRows[$status_id]['status'];
?>
          <ul>
          <li class="one"><a href="/<? echo $user_info['nameUrl'];?>/" title="<? echo $user_info['nameFull'];?>"><img alt="<? echo $user_info['nameFull'];?>" src="<? echo $pic; ?>" /></a></li>
          <li class="two"><a href="/<? echo $user_info['nameUrl'];?>/thread/<? echo $status_id;?>/<? echo $status_id;?>" title="<? echo $status; ?>"><? echo JWStatus::GetSubString( $status, 15);?></a><a class="bye" href="/<? echo $user_info['nameUrl'];?>/" title="<? echo "$user_info[nameScreen] ";echo  $statusRows[$status_id]['timeCreate'];?>"><? echo mb_substr($user_info['nameScreen'], 0, 6);?></a></li>
         </ul>
         <ul>
<?		}
?>		 

