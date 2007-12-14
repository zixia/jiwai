<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	shwdai@gmail.com
 */

/**
 * JiWai.de FilterConfig Class
 */
class JWFilterConfig {
	/**
	 * Filter
	 */
	static private $dictFilter = null;

	static public function Normal(){
		JWFilterRule::SetDictFilter( self::GetDictFilter() );
	}

	static public function Strict(){
		JWFilterRule::SetDictFilter( self::GetDictFilter() );
		JWFilterRule::SetRuleStrict( true );
	}

	static public function Strict_Other(){
		JWFilterRule::SetDictFilter( self::GetDictFilter() );
		JWFilterRule::SetRuleStrict( true );
	}

	static public function GetDictFilter(){
		if( self::$dictFilter == null ) {
			$dictFilter = new JWDictFilter();
			$dictFilter->Load( self::GetDictFilename() );
			self::$dictFilter = $dictFilter;
		}
		return self::$dictFilter;
	}

	static public function GetDictFilename()
	{
		return FRAGMENT_ROOT . '/dictionary/filter_dict.txt' ;
	}
}
?>
