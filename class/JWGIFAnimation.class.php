<?php
/*
 * http://www.lcdf.org/gifsicle/
 */

class JWGIFAnimation {
	private $proc;
	private $pipes;
	private $output;
	function __construct($delay=20, $loop=true) {
		$this->output = '';
		$this->proc = proc_open('gifsicle '.($loop ? '--loop' : '').' -U --multifile --delay '.$delay.' - ', array(array("pipe", "r"), array("pipe", "w"), array("pipe", "w")), $this->pipes);
	}
	function addFrame($i) {
		ob_start();
		imagegif($i);
		fwrite($this->pipes[0], ob_get_clean());
	}
	function finish() {
		fclose($this->pipes[0]);
		$this->output.= stream_get_contents($this->pipes[1]);
		//echo 'xxx';die();
		fclose($this->pipes[1]);
		fclose($this->pipes[2]);
		proc_close($this->proc);
		return $this->output;
	}
}
?>