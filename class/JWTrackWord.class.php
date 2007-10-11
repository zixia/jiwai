<?php
/**
 * @author shwdai@gmail.com
 * @version $Id$
 */
class JWTrackWord{

	/**
	 * InterPunction
	 */
	private static $punctuation = array(
		',', 	'，',	':',	'：',
		'!',	'！',	'.',	'。',
		'~',	'～',	'?',	'？',
		'、',	'\'',	'’',	'“',
		'”',
	);


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
	 * 将一个句子，存入TrackWord，并考虑各种标点符号的断句效果
	 */
	static function CreateSentence( $sentence ) {

		$wordArray = self::Segment( $sentence );
		if( false === $wordArray || empty( $wordArray ) )
			return array();

		$uniqueArray = array_unique( $wordArray );
		$keyWords = array();
		foreach( $uniqueArray as $word ) {
			$keyWords[ $word ] = self::Create( $word );
		}
		
		$rtn = array();
		foreach( $wordArray as $word ) {
			array_push( $rtn, $keyWords[ $word ] );
		}

		return $rtn;
	}

	/**
	 * GetUserTrackOrder
	 */
	static public function GetUserTrackOrder( $sentence, $limit=3 ) {
		$rtn = self::CreateSentence( $sentence );
		if( empty( $rtn ) )
			return false;

		$orderString = self::GetIdSequence( $rtn, false, $limit );

		return $orderString;
	}

	/**
	 * GetStatusTrackOrder
	 */
	static public function GetStatusTrackOrder( $sentence, $limit=3 ){
		$rtn = self::CreateSentence( $sentence );
		if( empty( $rtn ) )
			return array();

		$orderArray = self::GetIdSequence( $rtn, true, $limit );

		return $orderArray;		
	}

	/**
	 * Get Id sequence
	 */
	static function GetIdSequence( $idArray=array(), $split=true, $limit=3 ) {

		if( empty( $idArray ) )
			return array();
		
		/** For User Track*/
		if( $split == false ) {
			$rtn = null;
			$index = 0;
			foreach( $idArray as $id ) {
				if( $id == false || ++$index > 3 )
					return trim($rtn, ',');

				$rtn .= ','.$id;
			}

			return trim($rtn, ',');
		}

		/** For Sentence split **/
		$idString = implode( ',', $idArray );
		$idStringArray = preg_split( '/,{2,}/', $idString, 0, PREG_SPLIT_NO_EMPTY );
		
		$idArrays = array();	
		foreach( $idStringArray as $string ) {
			$array = explode( ',', $string );
			
			for( $j=0; $j<$limit; $j++ ){
				for($k=0; $k<$limit; $k++) {
					$idArrays = array_merge($idArrays, array_chunk($array, ($k+1) ) );
				}
				array_shift( $array );
				if( empty($array) ) break;
			}
		}

		$rtn = array();
		foreach( $idArrays as $a ) {
			array_push( $rtn, implode(',', $a) );	
		}

		return array_unique( $rtn );
	}

	/**
	 * Create TrackWord | If Exists return id
	 */
	static function Create( $word ) {
		$word = strtolower( trim($word) );

		if( null == $word ) {
			return false;
		}

		if( in_array( $word, self::$punctuation ) )
			return false;
		
		$idExist = JWDB::ExistTableRow( 'TrackWord', array( 'word'=>$word, ) );
		if( $idExist ){
			return $idExist;
		}
		
		$uArray = array(
			'word' => $word,
			'timeCreate' => date('Y-m-d H:i:s'),
		);
		
		return JWDB::SaveTableRow( 'TrackWord', $uArray );
	}

	/**
	 * GetDbRowsByIds
	 */
	static public function GetDbRowsByIds( $ids ){
		if( is_numeric($ids))
			$ids = JWDB::CheckInt( $ids );

		if( empty( $ids ) )
			return array();

		settype( $ids, 'array') ;

		$idString = implode(',', $ids);

		$sql = <<<_SQL_
SELECT * 
	FROM 
		TrackWord 
	WHERE 
		id IN ( $idString )
_SQL_;

		return JWDB::GetQueryResult( $sql, true ) ;
	}
}
?>
