<?php
require_once 'PEAR.php';
require_once 'HTTP/Client.php';
require_once 'KBS/Consts.php';

class KBS_Client {
	protected $url;
	protected $username;
	protected $password;
	protected $conn;
	public $loggedin = false;
	function __construct($url, $username, $password, $login = true) {
		$this->url = $url;
		$this->username = $username;
		$this->password = $password;
		$this->conn = new HTTP_Client();
		$this->conn->enableHistory(false);
		if ($login) $this->login();
	}

	function trace($s) {
		//print $s."\n";
	}

	function get($uri) {
		$this->trace('GET '.$uri);
		$c = $this->conn->get($this->url.$uri);
		if ($c) {
			$r = $this->conn->currentResponse();
			$this->trace($r['body']);
			return $r['body'];
		} else {
			$this->trace('Error.');
			return false;
		}
	}
	function post($uri, $param) {
		$this->trace('POST '.$uri);
		$c = $this->conn->post($this->url.$uri, $param);
		if ($c) {
			$r = $this->conn->currentResponse();
			$this->trace($r['body']);
			return $r['body'];
		} else {
			$this->trace('Error.');
			return false;
		}
	}
	function isError($s) {
		return $s===false || (bool)strstr($s, KBS_Consts::$strings['error']);
	}
	function login() {
		$this->loggedin = false;
		$r = $this->post(strstr($this->url, 'www.newsmth') ? '/bbslogin2.php' : '/bbslogin.php', array('id'=>$this->username, 'passwd'=>$this->password, 'kick_multi'=>'1'));
		if ($this->isError($r)) return false;
		$this->loggedin = true;
		return $this->loggedin;
	}
	function sendMessage($user, $text) {
		$r = $this->post('/bbssendmsg.php', array('destid'=>$user, 'msg'=>iconv('UTF-8', 'GBK', $text)));
		return !$this->isError($r);
	}
	function receiveMessage() {
		$r = $this->get('/bbsgetmsg.php');
		if ($this->isError($r)) return false;
        $s = strstr($r, '<div id="msgs">');
		if (!$s) return array();
        $s = substr($s, 15, strpos($s, '</div>')-15);
		$a = split('\(<a [^\(]+</a>\)', $s);
		$r = array();
		foreach($a as $s) {
			$s = trim($s);
			if (!$s) continue;
			if (!preg_match('/([A-Za-z0-9]+)\s\(([^\)]+)\):\s(.*)/', $s, $m)) continue; //FIXME: multiline in preg not work?
			$m[3] = trim(substr(strstr($s, '):'), 2));
			$r[] = array('user'=>$m[1], 'time'=>$m[2], 'text'=>html_entity_decode(iconv('GBK', 'UTF-8', $m[3])));
		}
		return $r;
	}
}
?>
