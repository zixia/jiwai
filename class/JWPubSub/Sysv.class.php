<?php
/**
 * JWPubSub System V IPC Message Queue Adaptor Class
 * 
 * @author      FreeWizard
 */

if (!defined('SYSV_MSG_ID')) 
define('SYSV_MSG_ID', 0xDEAD0000);

class JWPubSub_Sysv extends JWPubSub {
	static private $_q = array();
	private $id;
	private $chan = array();
	function __construct($url) {
		$c = parse_url($url);
		if (empty($c['port'])) $c['port'] = 1;
		$this->id = SYSV_MSG_ID | $c['port'];
	}
	private function q() {
		if (empty(self::$_q[$this->id])) {
			self::$_q[$this->id] = msg_get_queue($this->id, 0666);
		}
		return self::$_q[$this->id];
	}
	private function c($channel) {
		return abs(crc32($channel)>>1)+1; //FIXME not very well considered here
	}
	function Publish($channel, $data) {
		msg_send($this->q(), $this->c($channel), json_encode($data), false, false, $err);
	}
	function Subscribe($channel) {
		$this->chan[] = $channel;
	}
	function Unsubscribe($channel) {
		$i = array_search($this->chan, $channel);
		if ($i === false) return;
		unset($this->chan[$i]);
	}
	function PeekMessages() {
		$r = array();
		foreach ($this->chan as $c) {
			while (msg_receive($this->q(), $this->c($c), $t, 1024*1024, $m, false, MSG_IPC_NOWAIT, $err)) {
				$j = new JWPubSub_Message();
				$j->channel = $c;
				$j->data = json_decode($m, true);
				$r[] = $j;
			} 
		}
		return $r;
	}
}
?>
