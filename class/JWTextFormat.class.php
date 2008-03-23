<?php
/**
* @package		JiWai.de
* @copyright		AKA Inc.
* @author	 	shwdai@gmail.com 
*
*/
class JWTextFormat {

	static public function PreFormatRobotMsg( $text ) 
	{
		/* Mobile Software */
		$text = self::_StripQQTail( $text );
		$text = self::_StripMsnHead( $text );
		
		/* html entity */
		$text = self::_EntityDecode( $text );

		/* trim special char */
		$text = self::_TrimSpecialChar( $text );

		return $text;
	}

	static public function PreFormatWebMsg( $text, $type=null ) 
	{
		/* html Tags */
		$text = self::_StripTags( $text, $type );
		
		/* html entity */
		$text = self::_EntityDecode( $text );

		/* trim special char */
		$text = self::_TrimSpecialChar( $text );

		return $text;
	}

	/**
	 * 将字符串转化为半角，从而支持半角指令
	 * @param string $string , 
	 * @return string
	 */
	static function ConvertCorner($text, $keys=array())
	{
		$corner = array(
			'１' => '1', '２' => '2', '３' => '3', '４' => '4', '５' => '5',
			'６' => '6', '７' => '7', '８' => '8', '９' => '9', '０' => '0',
			'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd', 'ｅ' => 'e',
			'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i', 'ｊ' => 'j',
			'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n', 'ｏ' => 'o',
			'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's', 'ｔ' => 't',
			'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x', 'ｙ' => 'y',
			'ｚ' => 'z', 'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D',
			'Ｅ' => 'E', 'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I',
			'Ｊ' => 'J', 'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N',
			'Ｏ' => 'O', 'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S',
			'Ｔ' => 'T', 'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X',
			'Ｙ' => 'Y', 'Ｚ' => 'Z', '＠' => '@', '＃' => '#', '＄' => '$',
			'！' => '!', '％' => '%', '【' => '[', '】' => ']', '　' => ' ',
			'［' => '[', '］' => ']',
	    	);

		$text = preg_replace('/\xa3([\xa1-\xfe])/e', 'chr(ord(\1)-0x80)', $text);

		$keys = empty($keys) ? array_keys( $corner ) : array_unique($keys);
		$convert_values = array();
		$convert_keys = array();
		foreach( $keys AS $k )
		{
			array_push( $convert_values, $corner[ $k ] );
			array_push( $convert_keys, '/'.$k.'/');
		}

		return trim(preg_replace( $convert_keys, $convert_values, "$text\r\n"));
	}

	/**
	 * trim special char
	 */
	static public function _TrimSpecialChar( $text ) 
	{
		// new line to space
		$text = preg_replace( '/[\n\r]/', ' ', $text);

		// invalid character in XML
		$text = preg_replace( '/[\x00-\x09\x0b\x0c\x0e-\x19]/U', '', $text ); 

		// utf-8 line-reverse
		$text = preg_replace( '/\xE2\x80\xAE/U', '', $text );	
		
		// trim control
		$text = trim( $text, "\x00..\x1F" );

		// trim Corner Space
		$text = preg_replace('/(^　)+|(　$)/', '', $text);

		return $text;
	}

	/**
	 * Strip tags | comment | js | style propertye
	 */
	static public function _StripTags( $text, $type=null ) 
	{
		$search = array(
			'@<script[^>]*?>.*?</script>@si',	// Strip out javascript
			'@<style[^>]*?>.*?</style>@siU',	// Strip style tags properly
			'@<![\s\S]*?--[ \t\n\r]*>@'		// Strip multi-line comments including CDATA
		);
		
		/* allow gtalk|msn send html tag */
		if (false===(in_array($type, JWDevice::$htmlTagAllowArray)) )
		{
			array_push( $search, '@<[\/\!]*?[^<>]*?>@si' );
		}

		return preg_replace( $search, '', $text );
	}

	static public function _StripMsnHead( $text ) 
	{
		$msnString = '对方正在使用手机MSN,详见http://mobile.msn.com.cn。';
		$text = trim( str_replace( $msnString, '', $text ) );

		return $text;
	}

	/**
	 * Strip Mobile QQ tail
	 */
	static public function _StripQQTail( $text ) 
	{

		$qqString1 = '(本消息发自腾讯官方';
		$qqString2 = '（您的好友正在使用手机QQ';
		$qqString3 = '（ 您的好友正在使用手机QQ';

		$index1 = strpos( $text, $qqString1 );
		if( $index1 ) $text = substr( $text, 0, $index1 );

		$index2 = strpos( $text, $qqString2 );
		if( $index2 ) $text = substr( $text, 0, $index2 );

		$index3 = strpos( $text, $qqString3 );
		if( $index3 ) $text = substr( $text, 0, $index3 );

		return $text;
	}

	/**
	 * Decode Html Entity
	 */
	static public function _EntityDecode( $text ) 
	{
		$quots = array(
			'&apos;' => "'",
		);

		$text = str_replace( array_keys( $quots ), array_values( $quots ), $text );
		$text = html_entity_decode( $text, ENT_QUOTES );

		return $text;
	}

}
?>
