<?php
/**
 * UTF Only
 */
class PinYin 
{
	static private $map = array();
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
}
?>
