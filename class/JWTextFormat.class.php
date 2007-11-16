<?php
/**
* @package		JiWai.de
* @copyright		AKA Inc.
* @author	 	shwdai@gmail.com 
*
*/
class JWTextFormat {

	static public function PreFormatRobotMsg( $text ) {
		//Tags
		$text = self::_StripTags( $text );
		
		//Mobile Soft
		$text = self::_StripQQTail( $text );
		$text = self::_StripMsnHead( $text );
		
		//html entity
		$text = self::_EntityDecode( $text );

		//trim special char
		$text = self::_TrimSpecialChar( $text );

		return $text;
	}

	static public function PreFormatWebMsg( $text ) {
		//Tags
		$text = self::_StripTags( $text );
		
		//html entity
		$text = self::_EntityDecode( $text );

		//trim special char
		$text = self::_TrimSpecialChar( $text );

		return $text;
	}

	/**
	 * trim special char
	 */
	static public function _TrimSpecialChar( $text ) {
		// new line to space
		$text = preg_replace( '/[\n\r]/', ' ', $text);

		// invalid character in XML
		$text = preg_replace( '/[\x00-\x09\x0b\x0c\x0e-\x19]/U', "", $text ); 

		// utf-8 line-reverse
		$text = preg_replace( '/\xE2\x80\xAE/U', '', $text );	
		
		// trim control
		$text = trim( $text, "\x00..\x1F" );

		return $text;
	}

	/**
	 * Strip tags | comment | js | style propertye
	 */
	static public function _StripTags( $text ) {
		$search = array('@<script[^>]*?>.*?</script>@si',	// Strip out javascript
				'@<style[^>]*?>.*?</style>@siU',	// Strip style tags properly
				'@<[\/\!]*?[^<>]*?>@si',		// Strip out HTML tags
				'@<![\s\S]*?--[ \t\n\r]*>@'		// Strip multi-line comments including CDATA
		);

		return preg_replace( $search, '', $text );
	}

	static public function _StripMsnHead( $text ) {
		$msnString = '对方正在使用手机MSN,详见http://mobile.msn.com.cn。';
		$text = trim( str_replace( $msnString, '', $text ) );

		return $text;
	}

	/**
	 * Strip Mobile QQ tail
	 */
	static public function _StripQQTail( $text ) {

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
	static public function _EntityDecode( $text ) {
		$quots = array(
			'&apos;' => "'",
		);

		$text = str_replace( array_keys( $quots ), array_values( $quots ), $text );
		$text = html_entity_decode( $text, ENT_QUOTES );

		return $text;
	}

}
?>
