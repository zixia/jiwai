<?php
/**
 *
 * JiWai SDK
 *
 * http:/JiWai.de
 *
 **/

require_once 'OAuth.php';

define('JIWAI_SDK_REV', '{$Rev$}');
define('JIWAI_DOMAIN', 'jiwai.de');
define('JIWAI_API_DOMAIN', 'api.'.JIWAI_DOMAIN);
define('JIWAI_OAUTH_REQUEST_TOKEN_URL', 'http://'.JIWAI_API_DOMAIN.'/oauth/request_token');
define('JIWAI_OAUTH_ACCESS_TOKEN_URL', 'http://'.JIWAI_API_DOMAIN.'/oauth/access_token');
define('JIWAI_OAUTH_AUTHORIZE_URL', 'http://'.JIWAI_DOMAIN.'/wo/oauth/authorize');

interface Jiwai_Auth {
	function sendRequest($url, $param=array(), $method='GET');
}

class Jiwai_Auth_Basic implements Jiwai_Auth {
	private $username;
	private $password;
	private $curl_handler;
	protected function getCurlHandler() {
		if (!$this->curl_handler) $this->curl_handler = curl_init();
		return $this->curl_handler; 
	}
	function setLogin($username, $password) {
		$this->username = $username;
		$this->password = $password;
	}
	function sendRequest($url, $param=array(), $method='GET') {
		if (strpos($url, '://')===false) $url = 'http://'.JIWAI_API_DOMAIN.$url;
		if (empty($param)) $param = array();
		$curl = $this->getCurlHandler();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, strtoupper($method)=='POST');
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($param));
		if ($this->username) curl_setopt($curl, CURLOPT_USERPWD, $this->username.':'.$this->password);
		$ret = curl_exec($curl);
		if (!$ret) return false;
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($code!=200) return false;
		return $ret;
	}
}

class Jiwai_Auth_Oauth implements Jiwai_Auth {
	private $consumer;
	private $token;
	private $sign_method;
	private $curl_handler;
	protected function getCurlHandler() {
		if (!$this->curl_handler) $this->curl_handler = curl_init();
		return $this->curl_handler; 
	}
	function __construct($consumer_key, $consumer_secret) {
		$this->consumer = new OAuthConsumer($consumer_key, $consumer_secret);
		$this->sign_method = new OAuthSignatureMethod_HMAC_SHA1();
	}
	function setToken($key, $secret = null) {
		if ($secret) {
			$this->token = new OAuthToken($key, $secret);
		} else {
			$this->token = $key;
		}
		return $this->token;
	}
	function getToken() {
		return $this->token;
	}
	function fetchRequestToken() {
		$this->setToken(null);
		$ret = $this->sendRequest(JIWAI_OAUTH_REQUEST_TOKEN_URL);
		if (!$ret) return false;
		parse_str($ret, $t);
		return $this->setToken($t['oauth_token'], $t['oauth_token_secret']);
	}
	function getAuthorizeUrl($callback = false) {
		$url = JIWAI_OAUTH_AUTHORIZE_URL.'?oauth_token='.urlencode($this->token->key);
		if ($callback) $url.= '&oauth_callback='.urlencode($callback);
		return $url;
	}
	function fetchAccessToken() {
		$ret = $this->sendRequest(JIWAI_OAUTH_ACCESS_TOKEN_URL);
		if (!$ret) return false;
		parse_str($ret, $t);
		return $this->setToken($t['oauth_token'], $t['oauth_token_secret']);
	}
	function sendRequest($url, $param=array(), $method='GET') {
		if (strpos($url, '://')===false) $url = 'http://'.JIWAI_API_DOMAIN.$url;
		if (empty($param)) $param = array();
		$req = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, strtoupper($method), $url, $param);
		$req->sign_request($this->sign_method, $this->consumer, $this->token);
		$curl = $this->getCurlHandler();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		if ($method=='GET') {
			curl_setopt($curl, CURLOPT_POST, false);
			curl_setopt($curl, CURLOPT_URL, $req->to_url());
		} else {
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_URL, $req->get_normalized_http_url());
			curl_setopt($curl, CURLOPT_POSTFIELDS, $req->to_postdata());
		}
		//curl_setopt($curl, CURLOPT_HTTPHEADER, array($req->to_header()));
		$ret = curl_exec($curl);
		if (!$ret) return false;
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($code!=200) return false;
		return $ret;
	}
}

class Jiwai {
	private $auth;
	function __construct(&$auth) {
		$this->auth = &$auth;
	}
	function update($status) {
		$ret = $this->auth->sendRequest('/statuses/update.json', 
			array(
				'status' => $status
			), 'POST');
		return $ret ? json_decode($ret) : false;
	}
	function publicTimeline($since = null) {
		$ret = $this->auth->sendRequest('/statuses/public_timeline.json'.
			($since ? '?since='.urlencode($since) : ''), 
			array(
			), 'GET');
		return $ret ? json_decode($ret) : false;
	}
	function account() {
		$ret = $this->auth->sendRequest('/account/verify_credentials.json', array(), 'GET');
		return $ret ? json_decode($ret) : false;
	}
}
?>
