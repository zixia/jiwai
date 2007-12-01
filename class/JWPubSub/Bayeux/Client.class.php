<?php
class JWPubSub_Bayeux_Client {
        const VERSION = '0.1';
        const CONNTYPE = 'long-polling';
        //const CONNTYPE = 'http-polling';
        //const CONNTYPE = 'mime-message-block';
        public $url, $clientId, $initialized, $connected, $lastTimestamp, $lastId;
        public function __construct($url, $auto_connect=true) {
                $this->url = $url;
                if ($auto_connect) {
                        $this->handshake();
                }
        }
        public function __destruct() {
        }
        public function handshake() {
                $m = $this->message('/meta/handshake');
                $m->version = self::VERSION;
                $m->minimumVersion = self::VERSION;
                $m->connectionType = self::CONNTYPE;
                $m->supportedConnectionTypes = array($m->connectionType);
                $this->send($m);
        }
        public function connect() {
                $m = $this->message('/meta/connect');
                $m->connectionType = self::CONNTYPE;
                $this->send($m);
        }
        public function reconnect() {
                $m = $this->message('/meta/reconnect');
                $m->connectionType = self::CONNTYPE;
                $this->send($m);
        }
        public function publish($chan, $data) {
                $m = $this->message($chan);
                $m->data = $data;
                $this->send($m);
        }
        public function subscribe($chan) {
                $m = $this->message('/meta/subscribe');
                $m->subscription = $chan;
                $this->send($m);
        }
        public function unsubscribe($chan) {
                $m = $this->message('/meta/unsubscribe');
                $m->subscription = $chan;
                $this->send($m);
        }
        protected function message($chan) {
                $m = new JWPubSub_Bayeux_Message($chan);
                if ($this->clientId) $m->clientId = $this->clientId;
                return $m;
        }
        protected function send($m, $return = false) {
                if (!is_array($m)) $m = array($m);
                $r = self::_curl_post($this->url, array('message'=>json_encode($m))); 
                if ($r===false) return false;
                $r = json_decode($r);
                if (!is_array($r)) return false;
                foreach ($r as $m) {
                        $this->process($m);
                }
                if ($return) return $r;
                return true;
        }
        protected function process($m) {
                if (!isset($m->channel)) return;
                $f = 'proc'.str_replace('/', '_', $m->channel);
                if (method_exists($this, $f)) call_user_func(array($this, $f), $m);
                else $this->proc_default($m);
        }
        protected function proc_default($m) {
                return;
        }
        protected function proc_meta_handshake($m) {
		if (!$m->successful) return; //failed
                $this->clientId = $m->clientId;
                $this->initialized = true;
        }
        protected function proc_meta_connect($m) {
                $this->connected = true;
        }
        static private function _curl_post($url, $vars) {
                static $ch = null;
                if (!$ch) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Cometd Client 1.0');
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
                }
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
                $data = curl_exec($ch);
                //curl_close($ch);
                return $data;
        }
}
?>
