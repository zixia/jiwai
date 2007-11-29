<?php
class JWPubSub_Spread extends JWPubSub {
	private $sp;
	function __construct($host) {
		$this->sp = new Spread();
		$this->sp->connect(($host&&$host!='localhost') ? $host : '4803');
	}
	function Publish($channel, $data) {
		$this->sp->multicast($channel, json_encode($data));
	}
	function Subscribe($channel) {
		$this->sp->join($channel);
	}
	function Unsubscribe($channel) {
	}
	function PeekMessages() {
		$r = array();
		while ($ev = $this->sp->receive(0.1)) {
			$m = new JWPubSub_Message();
			$m->channel = $ev['groups'][0];
			$m->data = $ev['message'];
			$r[] = $m;
		}
		return $r;
	}
}
?>
