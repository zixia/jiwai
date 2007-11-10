<?php
$GLOBALS['facebook_config'] = array('debug'=>0);
require_once JW_ROOT.'/lib/Facebook/facebook.php';

class JWFacebook extends Facebook {
	static public $ProfileUrl = 'http://api.jiwai.de/facebook/?profile=';
	public $user = '';
	static function CallFromFB() {
		return !empty($_POST['fb_sig']);
	}
	static function GetPermUrl($perm, $next='http://apps.facebook.com/jiwaide/', $next_cancel='http://apps.facebook.com/jiwaide/') {
		return 'http://www.facebook.com/authorize.php?api_key=4d373987420c13a7a3080bc597216ef4&v=1.0&ext_perm='.urlencode($perm).'&next='.urlencode($next).'&next_cancel='.urlencode($next);
	}
	function __construct($ForcePrivateAuthToken=false) {
		$api_key = '4d373987420c13a7a3080bc597216ef4';
		$secret  = '861eaecde72e5e0a6eac8696e3d576f0';
		parent::__construct($api_key, $secret);
		if ($ForcePrivateAuthToken || CONSOLE) {
			$user_id = 734632045;
			$session_key = '88b485932073e080f48b656f-734632045';
			$this->user = $user_id;
			$this->api_client->session_key = $session_key; 
		} else {
			//$this->require_frame();
			$this->user = $this->require_login();
		}
	}
	function SetProfile($id) {
		$fbml = '<fb:ref url="'.self::$ProfileUrl.$id.'" />';
		$this->api_client->profile_setFBML($fbml);
	}
	static private function instance() {
		static $i;
		if (!$i) $i = new JWFacebook(true);
		return $i;
	}
	static function PublishAction($fbid, $name_url, $status_id, $status, $via, $img=null, $img_link=null) {
		try {
			$s = json_encode(array(
				'link_user'=>'http://jiwai.de/'.$name_url.'/',
				'link_status'=>'http://jiwai.de/'.$name_url.'/statuses/'.$status_id,
				'status'=>$status, 'via'=>$via));
			self::instance()->api_client->feed_publishTemplatizedAction($fbid,
				'{actor} posted a message on <a href="{link_user}">JiWai</a> via {via}', $s,
				'<a href="{link_status}">{status}</a>', $s,
				'', $img, $img_link);
		} catch (Exception $e) {
		}
	}
	function SetStatus($status) {
		try {
			$this->api_client->call_method('facebook.users.setStatus', array('status' => $status));
		} catch (Exception $e) {
		}
	}
	static function RefreshRef($id) {
		try {
			self::instance()->api_client->fbml_refreshRefUrl(self::$ProfileUrl.$id);
		} catch (Exception $e) {
		}
	}
	static function GetName($fbid) {
		try {
			$u = self::instance()->api_client->users_getInfo(array($fbid), array('name'));
			return count($u) ? $u[0]['name'] : '';
		} catch (Exception $e) {
			return '';
		}
	}
	static function SendNotification($fbid, $fbml) {
		try {
			if (!is_array($fbid)) $fbid = array($fbid);
			self::instance()->api_client->notifications_send($fbid, $fbml);
		} catch (Exception $e) {
		}
	}
	static function GetFBbyUser($idUser) {
		$dev = JWDevice::GetDeviceRowByUserId($idUser);
		if (empty($dev)) return false;
		if (empty($dev['facebook'])) return false;
		if (!empty($dev['facebook']['secret'])) return false;
		return $dev['facebook']['address'];
	}
	static function Verified($idUser) {
		return (self::GetFBbyUser($idUser)!=false);
	}
}
?>
