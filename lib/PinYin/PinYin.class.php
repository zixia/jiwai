<?php
/**
 * UTF Only
 */
class PinYin 
{
	static private $map = array();
	static private $en = array();

	static private function Init() {
		if (self::$map) return;
		$db = dirname(__FILE__) . '/pinyin.db';
		$fp = fopen($db, 'r');
		while($line = trim(fgets($fp))){
			list($word, $pinyin) = explode('`', $line);
			self::$map[$word] = $pinyin;
		}
		fclose($fp);
	}

	static private function InitEn() {
		if (self::$en) return;
		$db = dirname(__FILE__) . '/english.db';
		$fp = fopen($db, 'r');
		while($line = trim(fgets($fp))){
			@self::$en[strlen($line)][] = strtolower($line);
		}
		fclose($fp);
	}

	static public function GetInitLetter($string, $other=false, $delim='_') {
		self::Init();
		$ret = array();
		$strlen = mb_strlen($string, 'UTF-8');
		for($i=0; $i<$strlen; $i++) {
			$w = mb_substr($string, $i, 1, 'UTF-8');
			if ( preg_match('/[a-z0-9]/i', $w) && $other ) 
			{
				$ret[] = $w;
			} 
			else if ( $l = @self::$map[$w] ) {
				$ret[] = substr($l,0,1);
			}
		}
		return strtoupper(join($delim, $ret));
	}

	static public function GetAllLetter($string, $other=false, $delim='_') {
		self::Init();
		$ret = array();
		$strlen = mb_strlen($string, 'UTF-8');
		for($i=0; $i<$strlen; $i++) {
			$w = mb_substr($string, $i, 1, 'UTF-8');
			if ( preg_match('/[a-z0-9]/i', $w) && $other ) 
			{
				$ret[] = $w;
			} 
			else if ( $l = @self::$map[$w] ) {
				$ret[] = $l;
			}
		}
		return strtoupper(join($delim, $ret));
	}

	static public function CorrectWord($word) {
		self::InitEn();
		$len = strlen($word);
		$word = strtolower($word);
		if ( $len < 4 ) return false;
		if ( !isset(self::$en[$len]) ) return false;
		if ( in_array($word, self::$en[$len]) )
			return false;
		switch($len) {
			case 4: $r = range(4,5);  
					break;
			case 5: case 6: case 7: case 8: case 9:
					$r = range($len-1, $len+1); break;
			default: $r = range($len-2, $len+2); 
					 break;
		}

		$ws = array();
		foreach($r AS $i) {
			if (isset(self::$en[$i])) { 
				foreach(self::$en[$i] AS $w) {
					$l = similar_text($word, $w);
					if ( $l==$len || ( $l==$i ) )
						$ws[] = $w;
				}
			}
		}
		return $ws;
	}
}
?>
