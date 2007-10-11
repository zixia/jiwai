<?php
/**
 * @author shwdai@gmail.com
 * @version $Id$
 */
class JWTrackWord{

	/**
	 * Sentence segment
	 */
	static public function Segment($sentence){
		$string = str_replace(array(' ', "\n", "\t", "\r", "\0", "\x0B"), '+', trim($sentence)); 
		return self::RealSegment( $string );
	}

	/**
	 * private function for segment
	 */
	static private function RealSegment($string, $host='10.1.10.49', $port=12345, $timeout=1000){

		$string = mb_convert_encoding( $string, 'GB2312', 'UTF-8,GB2312' );

		if( ! ( $sock = @socket_create( AF_INET, SOCK_STREAM, SOL_TCP ) ) ){
			return false;
		}
		
		//@socket_set_nonblock ( $sock );
		/**
		 * set timeout unit( sec )
		 */
		$timeoutSec = (float) $timeout / 1000000 ;
		@socket_set_timeout( $sock, $timeoutSec );
		if( ! @socket_connect( $sock, $host, $port ) ){
			return false;
		}
		
		$cmd = "tokenize $string 0 24 0\r\n";

		if( ! socket_write( $sock, $cmd ) ) {
			return false;
		}
		
		$falseFlag = true;
		$inToken = false;
		$wordString = null;

		while( $line = @socket_read( $sock, 1024, PHP_NORMAL_READ ) ){
			$line = trim( $line );

			if( $line == 'ERROR' || $line == 'END' ) {
				break;
			}

			if( $inToken ) {
				$wordString .= $line;
				continue;
			}
			
			if( substr($line,0,6) == 'VALUE ' ) {
				$inToken = true;
				continue;
			}
		}
		/**
		 * Close Socket
		 */
		@socket_close( $sock );

		/**
		 * Return words
		 */
		if ( $wordString ) {
			$wordString = mb_convert_encoding( $wordString, 'UTF-8', 'GB2312,UTF-8' );
			$wordString = str_replace('+', ' ',$wordString); 
			$wordArray = preg_split('/\s+/', $wordString );
			return $wordArray;
		}
		return array();	
	}

	/**
	 * Create TrackWord | If Exists return id
	 */
	static function Create( $word ) {
		$word = strtolower( trim($word) );

		if( null == $word ) {
			return false;
		}
		
		$idExist = JWDB::ExistTableRow( 'TrackWord', array('word'=>$word,) );
		if( $idExist ){
			return $idExist;
		}
		
		$uArray = array(
			'word' => $word,
			'timeCreate' => date('Y-m-d H:i:s'),
		);
		
		return JWDB::SaveTableRow( 'TrackWord', $uArray );
	}
}
?>
