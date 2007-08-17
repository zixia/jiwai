<?php
/*
 * @author: Seek
 */
class JWNetFunc {

	static public function DoGet( $urlstr, $data = array() ) {
		$encoded = "";

		$myurl = $urlstr;
		if( false == empty($data)  ) {
			if( count($data) ) {
				while (list($k,$v) = each($data)) {
					$encoded .= ($encoded ? "&" : "");
					$encoded .= rawurlencode($k)."=".rawurlencode($v);
				}
				$myurl = $urlstr . '?' . $encoded;
			}
		}

		$fp = fopen( $myurl, 'r');
		if( false == $fp )
			return false;

		$rtn = '';
		while( !feof( $fp )) {
			$str = fgets( $fp );
			$rtn = $rtn . $str . "\n";
		}
		fclose( $fp );

		return $rtn;
	}
	/**
	 * send out post data
	 *
	 */
	static public function doPost($urlstr, $data=array()) {

		$url = parse_url( $urlstr );
		if (empty($url))
			return false;

		if ( false == isset($url['port']) )
			$url['port'] = "";

		if ( false == isset($url['query']) )
			$url['query'] = "";

		$encoded = "";
		if( is_array( $data ) ) {
			while (list($k,$v) = each($data)) {
				$encoded .= ($encoded ? "&" : "");
				$encoded .= rawurlencode($k)."=".rawurlencode($v);
			}
		}else if( is_string($data) ){
			$encoded = $data;
		}

		$fp = fsockopen( $url['host'], $url['port'] ? $url['port'] : 80);
		if (empty($fp) )
			return false;

		fputs($fp, sprintf("POST %s%s%s HTTP/1.0\n", $url['path'], $url['query'] ? "?" : "", $url['query']));
		fputs($fp, "Host: $url[host]\n");
		fputs($fp, "Content-type: application/x-www-form-urlencoded\n");
		fputs($fp, "Content-length: " . strlen($encoded) . "\n");
		fputs($fp, "Connection: close\n\n");

		fputs($fp, "$encoded\n");

		$line = fgets($fp,1024);
		if ( false == eregi("^HTTP/1\.. 200", $line))
			return false;

		$results = "";
		$inheader = true;
		while( false == feof($fp) ) {
			$line = fgets($fp,1024);
			if ($inheader && ($line == "\n" || $line == "\r\n"))
				$inheader = false;
			else if (false == $inheader)
				$results .= $line;
		}
		fclose($fp);

		return $results;
	}
}
?>
