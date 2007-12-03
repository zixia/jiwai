<?php
/**
 *	Facebook app daemon
 **/
require_once(dirname(__FILE__) . "/../jiwai.inc.php");

class FBListener implements JWPubSub_Listener {
	function onData($channel, $data) {
var_dump ($data);
		$idUser = $data['idUser'];
		$idFacebook = JWFacebook::GetFBbyUser( $idUser );
		if (!$idFacebook) return; //check f8 bind
		JWFacebook::RefreshRef($idUser);
		if ($channel!='/statuses/update') return;
		if ($data['idUserReplyTo']) return; //check if is reply
		$userInfo = JWUser::GetUserInfo( $idUser );
		if (empty($picUrl)) {
			$pic = null;
			$picUrl = null;
		} else {
			$pic = JWPicture::GetUrlById( $data['idPicture'] , 'picture' );
			$picUrl = 'http://jiwai.de/'.urlencode($userInfo['nameUrl']).'/mms/'.$data['idStatus'];
		}
		JWFacebook::PublishAction($idFacebook, $userInfo['nameUrl'], $data['idStatus'], $data['message'], JWDevice::GetNameFromType($data['device']), $pic, $picUrl);
	}
}

$pubsub = JWPubSub::Instance('spread://localhost/');
$pubsub->AddListener(array('/statuses/update', '/statuses/destroy'), new FBListener());
$pubsub->RunLoop();

?>
