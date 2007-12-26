<?php
require_once '../../../../jiwai.inc.php';
if (empty($_GET['user']) || !($page_user_info = JWUser::GetUserInfo($_GET['user']))) {
	echo 'Invalid user.';
	die();
}
$page_user_id = $page_user_info['idUser'];
$username = $page_user_info['nameScreen'];
$url='http://jiwai.de/'.$username.'/';
if (JWUser::IsProtected($page_user_id)) {
	echo 'Protected user.';
	die();
}
$width = (int) $_GET['width'];
$mode = (int) $_GET['mode'];
$count = (int) $_GET['count'];
$last = (int) $_GET['cc1'];
$sum = crc32('JW'.$page_user_id.$width.$mode.$count.$last);
if ($sum != $_GET['cc2']) {
	echo 'Unknown parameters.';
	die();
}

function getOwnTimeline($count=10) {
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
			'icon'=> ( !empty($r['idPicture']) ?  JWPicture::GetUrlById($r['idPicture']) : JWPicture::GetUserIconUrl($user_id)),
			'body'=>$r['status'], 
			'time'=>$r['timeCreate'], 
			'via'=>'来自'.$r['device'].($r['isSignature']=='Y'?'签名':'')
			);
	}
	//var_dump($a);die();
	return array_reverse($a);
}
function getFriendsTimeline($count=10) {
	$a = array(
		array('from'=>'jessica', 'body'=>'乌拉消失了一下午，你现在又说肩关节疼，我不能不联想啊……嘿嘿嘿……', 'time'=>'4 小时前', 'via'=>'来自于 GTalk', 'icon'=>'ic48.gif'),
		array('from'=>'gtax', 'body'=>'这个胖葫芦在MOTO E2上怎么有个BUG,只能照一次二维码,第二次照就不能调用手机的摄像头,必须重启软件才可以用...', 'time'=>'4 小时前', 'via'=>'来自于 GTalk', 'icon'=>'av48.gif'),
		array('from'=>'kevin', 'body'=>'3玩耍数据库中……', 'time'=>'4 小时前', 'via'=>'来自于 GTalk', 'icon'=>'sb48.gif'),
		array('from'=>'finalboy', 'body'=>'4玩耍数据库中……', 'time'=>'4 小时前', 'via'=>'来自于 GTalk', 'icon'=>'xx48.gif'),
		array('from'=>'jessica', 'body'=>'乌拉消失了一下午，你现在又说肩关节疼，我不能不联想啊……嘿嘿嘿……', 'time'=>'4 小时前', 'via'=>'来自于 GTalk', 'icon'=>'ic48.gif'),
		array('from'=>'gtax', 'body'=>'这个胖葫芦在MOTO E2上怎么有个BUG,只能照一次二维码,第二次照就不能调用手机的摄像头,必须重启软件才可以用...', 'time'=>'4 小时前', 'via'=>'来自于 GTalk', 'icon'=>'av48.gif'),
		array('from'=>'kevin', 'body'=>'3玩耍数据库中……', 'time'=>'4 小时前', 'via'=>'来自于 GTalk', 'icon'=>'sb48.gif'),
		array('from'=>'finalboy', 'body'=>'4玩耍数据库中……', 'time'=>'4 小时前', 'via'=>'来自于 GTalk', 'icon'=>'xx48.gif'),
	);
	return $a;
}
?>
