<?php
class JWPubSub_File extends JWPubSub {
	static private $_initialized = false;
	static private function _initialize() {
		inotifytools_initialize();
		self::$_initialized = true;
	}
	static private function _check($dir) {
		if (is_dir($dir)) return;
		mkdir($dir, 0777, true);
	}
	private $_root;
	function __construct($path) {
		$this->_root = $path;
	}
	function Publish($channel, $data) {
		self::_check($this->_root.$channel);
		file_put_contents(tempnam($this->_root.$channel, 'JWPubSub.'), json_encode($data));
	}
	private $_wd = array();
	function Subscribe($channel) {
		if (!self::$_initialized) self::_initialize();
		self::_check($this->_root.$channel);
		$wd = inotifytools_watch_recursively($this->_root.$channel, IN_MOVE | IN_CLOSE_WRITE);
		$this->_wd[$wd] = $channel;
	}
	function Unsubscribe($channel) {
		$i = array_search($this->_wd, $channel);
		if ($i!==false) unset($this->_wd[$i]);
	}
	private $_scanned = 0;
	function PeekMessages() {
		$r = array();

		if ((time() - $this->_scanned) > 5) $r = $this->ScanDirectories();

		while ($ev = inotifytools_next_event(1)) {
			$channel = $this->_wd[$ev['wd']];
			if (!empty($r[$channel.'/'.$ev['name']])) continue;
			$fn = $this->_root.$channel.'/'.$ev['name'];
			$data = file_get_contents($fn);
			unlink($fn);
			if (!$data) continue;
			$r[$channel.'/'.$ev['name']] = $data;
		}

		$r1 = array();
		foreach ($r as $k=>$v) {
			$m = new JWPubSub_Message();
			$m->channel = substr($k, 0, strrpos($k, '/'));
			$m->data = json_decode($v);
			$r1[] = $m;
		}
		return $r1;
	}
	private function ScanDirectories() {
		$r = array();
		foreach($this->_wd as $channel) {
			if (!($h=opendir($this->_root.$channel))) continue;
			while (false !== ($f=readdir($h))) {
				if ($f[0]=='.') continue;
				$fn = $this->_root.$channel.'/'.$f;
				if (!is_file($fn)) continue;
				$fp = fopen($fn, 'r');
				if (!flock($fp, LOCK_EX|LOCK_NB)) continue;
				$data = stream_get_contents($fp);
				flock($fp, LOCK_UN);
				fclose($fp);
				unlink($fn);
				if ($data) $r[$channel.'/'.$f] = $data;
			}
			closedir($h);
		}
		$this->_scanned = time();
		return $r;
	}
}
?>
