<?php
class JWPubSub {
	static private $m_instance_pool = array();
	static function Instance($url) {
		if (isset(self::$m_instance_pool[$url]))
			return self::$m_instance_pool[$url];
		$c = parse_url($url, PHP_URL_SCHEME);
		$class = 'JWPubSub_'.ucfirst($c);
		$obj = new $class($url);
		return self::$m_instance_pool[$url]=$obj;
	}
	private $listeners = array();
	function __construct() {
	}
	function Publish($channel, $data) {
	}
	function Subscribe($channel) {
	}
	function Unsubscribe($channel) {
	}
	function PeekMessages() {
	}
	function AddListener($channel, &$obj) {
		if (is_array($channel)) {
			foreach($channel as $c) $this->AddListener($c, $obj);
			return;
		}
		if (!isset($this->listeners[$channel])) {
			$this->listeners[$channel]=array();
			$this->Subscribe($channel);
		}
		$this->listeners[$channel][] = $obj;
	}
	function RemoveListener($channel, $obj) {
		if (!isset($this->listeners[$channel])) return;
		$i = array_search($this->listeners[$channel], $obj);
		if ($i===false) return;
		unset($this->listeners[$channel][$i]);
		if (!count($this->listeners[$channel])) {
			$this->Unsubscribe($channel);
		}
	}
	function RunLoop() {
		while(1) {
			$this->RunOnce();
			usleep(20);
		}
	}
	function RunOnce() {
		$ev = $this->PeekMessages();
		foreach ($ev as $m) {
			$this->Dispatch($m->channel, $m->data);
		}
	}
	function Dispatch($channel, $data) {
		if (!isset($this->listeners[$channel])) return;
		foreach ($this->listeners[$channel] as $l) {
			if (gettype($l)!='object') continue;
			$l->onData($channel, $data);
		}
	}
}
?>
