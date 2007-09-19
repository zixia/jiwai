<?php
require_once '../../../jiwai.inc.php';

$pathParam = $_GET['pathParam'];
if (!preg_match('/\d+/', $pathParam, $m)) exit();
$idUser = (int) $m[0];
if (!($page_user_info = JWUser::GetUserInfo($idUser))) {
	echo 'Invalid user.';
	die();
}
if (JWUser::IsProtected($idUser)) {
	echo 'Protected user.';
	die();
}
if( $page_user_info['idConference'] ) {
	//论坛模式用户
	$status_data 	= JWStatus::GetStatusIdsFromConferenceUser( $idUser, 1 );
}else{
	// 显示用户自己的
	$status_data 	= JWDB_Cache_Status::GetStatusIdsFromUser( $idUser, 1 );
}

$width = (int) $_GET['width'];
$mode = (int) $_GET['mode'];
$count = (int) $_GET['count'];
$last = (empty($status_data) || empty($status_data['status_ids'])) ? 0 : $status_data['status_ids'][0];
$sum = crc32('JW'.$idUser.$width.$mode.$count.$last);

$url = "http://asset.alpha.jiwai.vm/gadget/image/?user=$idUser&mode=$mode&width=$width&count=$count&cc1=$last&cc2=$sum";
header('Location: '.$url);

?>
