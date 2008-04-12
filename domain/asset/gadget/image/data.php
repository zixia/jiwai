<?php
require_once '../../../../jiwai.inc.php';
if (empty($_GET['user']) || !($page_user_info = JWUser::GetUserInfo($_GET['user']))) {
	echo 'Invalid user.';
	die();
}
$page_user_id = $page_user_info['idUser'];
$username = $page_user_info['nameFull'];
$url='http://jiwai.de/'.$page_user_info['nameUrl'];
if (JWUser::IsProtected($page_user_id)) {
	echo 'Protected user.';
	die();
}
$width = (int) $_GET['width'];
$mode = (int) $_GET['mode'];
$count = (int) $_GET['count'];
$last = (int) $_GET['cc1'];
$sum = crc32('JW'.$page_user_id.$width.$mode.$count.$last);
if ($sum != $_GET['cc2'] && !defined('BETA')) {
	echo 'Unknown parameters.';
	die();
}

function getOwnTimeline($count=10, $icon_size=48) {
	global $page_user_id, $page_user_info;
	if( $page_user_info['idConference'] ) {
		$status_data    = JWStatus::GetStatusIdsFromConferenceUser($page_user_id, $count);
	}else{
		$status_data    = JWStatus::GetStatusIdsFromUser($page_user_id, $count);
	}
	$status_rows	= JWStatus::GetDbRowsByIds($status_data['status_ids']);
	$user_rows	= JWDB_Cache_User::GetDbRowsByIds($status_data['user_ids']);
	$a = array();
	foreach ($status_rows as $r) {
		$a[] = array(
			'from'=>$user_rows[$r['idUser']]['nameScreen'],
			'icon'=> /*((!empty($r['idPicture']) && $icon_size==48) ?  JWPicture::GetUrlById($r['idPicture'], 'thumb'.$icon_size) :*/ JWPicture::GetUserIconUrl($r['idUser'], 'thumb'.$icon_size)/*)*/,
			'body'=>$r['status'], 
			'time'=>substr($r['timeCreate'], 0, strrpos($r['timeCreate'], ':')), 
			'via'=>'来自'.JWDevice::GetNameFromType($r['device']).($r['statusType']=='SIG'?'签名':'')
			);
	}
	//var_dump($a);die();
	return array_reverse($a);
}
function getFriendsTimeline($count=10) {
	$a = array(
	);
	return $a;
}
?>
