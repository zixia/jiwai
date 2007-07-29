<?php
$GLOBALS['facebook_config'] = array('debug'=>0);
require_once JW_ROOT.'/lib/Facebook/facebook.php';

class JWFacebook extends Facebook {
	static public $ProfileUrl = 'http://api.alpha.jiwai.de/facebook/?profile=';
	public $user = '';
	static function CallFromFB() {
		return !empty($_POST['fb_sig']);
	}
	function __construct($ForcePrivateAuthToken=false) {
		$api_key = 'ad4f05fadac08e34aeff6f96d6f84c51';
		$secret  = 'f9dec00a3bbf623abd3cf5e89a8f4471';
		parent::__construct($api_key, $secret);
		if ($ForcePrivateAuthToken || CONSOLE) {
			$user_id = 535996158;
			$session_key = '6259efacb91d5ccc6fcdd67a-535996158';
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
	static function RefreshRef($id) {
		self::instance()->api_client->fbml_refreshRefUrl(self::$ProfileUrl.$id);
	}
	static function GetName($fbid) {
		$u = self::instance()->api_client->users_getInfo(array($fbid), array('name'));
		return count($u) ? $u[0]['name'] : '';
	}
	static function SendNotification($fbid, $fbml) {
		if (!is_array($fbid)) $fbid = array($fbid);
		self::instance()->api_client->notifications_send($fbid, $fbml);
	}
	static function Verified($idUser) {
		$dev = JWDevice::GetDeviceRowByUserId($idUser);
		if (empty($dev)) return false;
		if (empty($dev['facebook'])) return false;
		if (!empty($dev['facebook']['secret'])) return false;
		return true;
	}
}
?>
