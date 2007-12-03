<?php
/**
 * JWPubSub Spread-Toolkit Adaptor Class
 * 
 * @author      FreeWizard
 */

if (!defined('SPREAD_PORT')) 
define('SPREAD_PORT', 4803);

class JWPubSub_Spread extends JWPubSub {
	private $sp;
	function __construct($url) {
		$c = parse_url($url);
		if (empty($c['port'])) $c['port'] = SPREAD_PORT;
		$this->sp = new Spread();
		$this->sp->connect(($c['host']!='localhost') ? $c['host'].':'.$c['port'] : $c['port']);
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
			$m->data = json_decode($ev['message']);
			$r[] = $m;
		}
		return $r;
	}
}
?>
