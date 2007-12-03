<?php
/**
 * JWPubSub Syslog(-ng) Adaptor Class
 * 
 * @author      FreeWizard
 */
class JWPubSub_Syslog {
    private $client;
    private function __construct($url) {
	$c = parse_url($url);
        $this->client = new JWSyslog(substr($c['path'], 1), $c['host']);
    }

    public function Publish($channel, $data) {
	switch (gettype($data)) {
		case 'array':
			$s = '';
			foreach($data as $k => $v) {
				if ($s) $s.=' ';
				$s.= (is_int($k) ? $v : "$k=$v");
			}
			break;
		default:
			$s = (string)$data;
	}
        $this->client->send($channel, $s);
    }
}
?>
