<?php
require_once(JW_ROOT . 'lib/PinYin/PinYin.class.php');
/**
 * @author shwdai@gmail.com
 * @version $Id$
 */
class JWSearchWord{

	/**
	 * InterPunction
	 */
	private static $punctuation = array(
			',', 	'，',	':',	'：',
			'!',	'！',	'.',	'。',
			'~',	'～',	'?',	'？',
			'、',	'\'',	'’',	'“',
			'”',	'(',	')',	'（',
			'）',	'[',	']',	'【',
			'】',
			);

	/**
	 * If search result count larger than guess_threahold, no guess.
	 */
	static public function GetThreshold() {
		$ini = JWConfig::Ini();
		return abs(intval(@$ini['search']['G_THRESHOLD']));
	}

	/**
	 * Create SearchWord | If Exists return id
	 */
	static function GuessWord($word, $result=0 ) {
		$word = strtolower( trim($word) );

		if( null == $word ) {
			return false;
		}

		if( in_array( $word, self::$punctuation ) )
			return false;

		if( preg_match('/[\s,\(\)\+\-]+/', $word) )
			return false;

		$exist_id = JWDB::ExistTableRow( 'SearchWord', array( 'word'=>$word, ) );
		if( $exist_id ) {
			JWDB::UpdateTableRow('SearchWord', $exist_id, array('countResult'=>$result));
		}

		$all_letter = PinYin::GetAllLetter($word); 
		$init_letter = PinYin::GetInitLetter($word); 

		if ( !$exist_id && $result > 0 && $all_letter ) {
			$save_array = array(
					'word' => $word,
					'initLetter' => $init_letter,
					'allLetter' => $all_letter,
					'timeCreate' => date('Y-m-d H:i:s'),
					'countResult' => $result,
					);
			JWDB::SaveTableRow('SearchWord', $save_array);
		}

		if ( $result < self::GetThreshold() ) {
			if ( preg_match('/^\w+$/',$word) ) {
				return PinYin::CorrectWord($word);
			} else if ( $all_letter ) {
				$sql = "SELECT word FROM SearchWord WHERE allLetter='{$all_letter}' AND word <> '{$word}' AND countResult > {$result} LIMIT 1";
				$result = JWDB::GetQueryResult( $sql ) ;
				return $result ? array($result['word']) : false;
			}
		}
		return false;
	}
}
?>
