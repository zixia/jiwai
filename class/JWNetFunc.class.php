<?php
/*
 * @author: Seek
 */
class JWNetFunc {

	/**
	 * send out post data
	 *
	 */
	static public function DoGet( $getUrl, $data = array() ) {

		if( false == empty($data)  ) {
			if( is_array($data) && count($data) ) {
				$encoded = null; 
				while ( list($k,$v) = each($data) ) {
					$encoded .= ( $encoded ? '&' : '');
					$encoded .= rawurlencode($k) .'='. rawurlencode($v);
				}
				$getUrl .= '?' . $encoded;
			}
		}

		$ch = curl_init();    
		curl_setopt($ch, CURLOPT_URL, $getUrl);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut); 
		$result = curl_exec($ch);
		curl_close($ch);

		return trim($result);
	}
	/**
	 * send out post data
	 *
	 */
	static public function DoPost($postUrl, $data=array(), $timeOut=2) {

		$encoded = null;
		if( is_array( $data ) ) {
			while (list($k,$v) = each($data)) {
				$encoded .= ($encoded ? "&" : "");
				$encoded .= rawurlencode($k)."=".rawurlencode($v);
			}
		}else if( is_string($data) ){
			$encoded = $data;
		}

		$ch = curl_init();    
		curl_setopt($ch, CURLOPT_URL, $postUrl);  
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Expect:' ) );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded); 
		$result = curl_exec($ch);
		curl_close($ch);

		return trim($result);
	}
}
?>
