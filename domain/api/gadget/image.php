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

$width = (int) $_GET['width'];
$mode = (int) $_GET['mode'];
$count = (int) $_GET['count'];
$last = JWStatus::GetStatusNum($idUser);
$sum = crc32('JW'.$idUser.$width.$mode.$count.$last);

$url = "http://asset.jiwai.de/gadget/image/?user=$idUser&mode=$mode&width=$width&count=$count&cc1=$last&cc2=$sum";
header('Location: '.$url);

?>
