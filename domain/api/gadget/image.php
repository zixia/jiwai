<?php
require_once '../../../jiwai.inc.php';

/*
http://api.jiwai.de/gadget/image/mode2/width200/count5/id89/gadage.png
*/

$pArray = explode('/', trim($_REQUEST['pathParam'], '/') );
foreach( $pArray as $pv ){
	if( preg_match( '/^([[:alpha:]]+)(\d+)$/', $pv, $matches) ){
		${$matches[1]} = $matches[2];
	}
}

if( false == (isset($id)&&isset($width)&&isset($count)&&isset($mode)) ) exit();

$idUser = (int) $id;

if (!($page_user_info = JWUser::GetUserInfo($idUser))) {
	echo 'Invalid user.';
	die();
}
if (JWUser::IsProtected($idUser)) {
	echo 'Protected user.';
	die();
}

$width = intval($width);
$mode = intval($mode);
$count = intval($count);

$last = JWStatus::GetStatusNum($idUser);
$sum = crc32('JW'.$idUser.$width.$mode.$count.$last);

$url = "http://asset.jiwai.de/gadget/image/?user=$idUser&mode=$mode&width=$width&count=$count&cc1=$last&cc2=$sum";
header('Location: '.$url);

?>
