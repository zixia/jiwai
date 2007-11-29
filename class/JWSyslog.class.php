<?php
/**
 *  Send log lines to remote syslog(-ng) server in UDP
 *  
 *  @author freewizard
 */
class JWSyslog {
        const TIMEOUT = 200.0;
        static private $conn = array();
        public $hostname = '';
        public $process;
        public $server;
        public $facility;
        public $severity;
        function __construct($process='', $server='127.0.0.1', $facility=LOG_LOCAL6, $severity=LOG_INFO) {
                if ($process) $this->process = $process;
                else $process = basename($_SERVER['SCRIPT_FILENAME']);
                $this->server = $server;
                $this->facility = $facility;
                $this->severity = $severity;
        }
        static private function _conn($ip) {
                if (empty(self::$conn[$ip])) {
                        self::$conn[$ip] = fsockopen("udp://$ip", 514, $errno, $errstr, self::TIMEOUT/1000);
                        stream_set_timeout(self::$conn[$ip], (int)self::TIMEOUT/1000, self::TIMEOUT%1000);
                }
                return self::$conn[$ip];
        }
        function Send($msg) {
                $priority = $this->facility*8 + $this->severity;
                $timestamp = trim(date("M j H:i:s"));
                $data = "<{$priority}>{$timestamp} {$this->hostname} {$this->process}: {$msg}";
                $data = substr($data, 0, 1024);
                fwrite(self::_conn($this->server), $data);
        }
}
?>
