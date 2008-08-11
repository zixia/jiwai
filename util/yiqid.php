<?php
/**
 *	Facebook app daemon
 **/
require_once(dirname(__FILE__) . "/../jiwai.inc.php");

class YiQiListener implements JWPubSub_Listener {
	private $curl;
	function __construct() {
		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curl, CURLOPT_HEADER, false);
		curl_setopt($this->curl, CURLOPT_URL, 'http://www.yiqi.com/jiwai/recievemessge.php');
		curl_setopt($this->curl, CURLOPT_POST, true);
		
	}
	function onData($channel, $data) {
		$b = JWDB_Cache_Vender::Query($data['idUser'], 'yiqi');
		if (empty($b)) return;
		$status = JWDB_Cache_Status::GetDbRowById($data['metaInfo']['idStatus']);
		$param = array(
			'user_id' => $data['idUser'],
			'status_id' => $data['metaInfo']['idStatus'],
			'status' => $status['status'],
			'create_time' => $status['timeCreate'],
			'picture_url' => $status['statusType']=='MMS' ? JWPicture::GetUrlById( $status['idPicture'] , 'picture' ) : '',
			'device' => $status['device'],
		);
		echo date(DATE_ATOM)." User {$data['idUser']} Status {$data['metaInfo']['idStatus']} ";
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($param));
		$ret = curl_exec($this->curl);
		$code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
		echo "HTTP $code $ret\n";
	}
}

$pubsub = JWPubSub::Instance('spread://localhost/');
$pubsub->AddListener(array('/statuses/update', '/statuses/destroy'), new YiQiListener());
$pubsub->RunLoop();

?>
