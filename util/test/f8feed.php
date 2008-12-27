<?php
$_SERVER['HTTP_HOST']='api.jiwai.de';
require_once(dirname(__FILE__) . "/../../jiwai.inc.php");
function publish($data) {
		$idUser = $data['idUser'];
		$idFacebook = JWFacebook::GetFBbyUser( $idUser );
		if (!$idFacebook) {
			echo "$idUser not bind\n";
			return; //check f8 bind
		}
		JWFacebook::RefreshRef($idUser);
		$userInfo = JWUser::GetUserInfo( $idUser );
		$pic = null;
		$picUrl = null;
//		JWFacebook::PublishAction($idFacebook, $userInfo['nameUrl'], $data['idStatus'], $data['message'], JWDevice::GetNameFromType($data['device']), $pic, $picUrl);
$f = new JWFacebook(true);
$f->api_client->feed_publishTemplatizedAction($idFacebook,
'{actor} has activity on JiWai.',
'{}',
'{status} via {via}',
json_encode(array('status'=>'testing', 'via'=>'SMTH')),
'follow me!');
}

$d = array(
	'idUser'	=> 61481,
	'idStatus'	=> 7451679,
	'device'	=> 'web',
	'message'	=> 'just a test, hehe',
);
publish($d);
?>
