<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	shwdai@gmail.com
 */

/**
 * JiWai.de FilterRule Class
 */
class JWFilterRule {
	/**
	 * Instance of this singleton
	 *
	 * @var JWFile
	 */
	static private $msInstance;

	/**
	 * const
	 */
	const FILTER_WORD = 1;
	const FILTER_IDSENDER = 2;
	const FILTER_IDRECIEVER = 3;
	const FILTER_STRICT = 4;

	
	/**
	 * filter rules
	 */
	static private $ruleWord = true;
	static private $ruleStrict = false;
	static private $ruleIdSender = array();
	static private $ruleIdReciever = array();

	/**
	 * dictfilter object
	 */
	static private $dictFilter = null;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWFilterRule
	 */
	static public function &Instance()
	{
		if (!isset(self::$msInstance)) {
			$class = __CLASS__;
			self::$msInstance = new $class;
		}
		return self::$msInstance;
	}
	
	/**
	 * set Dict Filter
	 */
	static public function SetDictFilter($filter){
		self::$dictFilter = $filter;
	}
	
	/**
	 * @param $boolean, whether use rule filter word
	 */
	static public function SetRuleWord($boolean=true){
		self::$ruleWord = ($boolean===true);
	}

	/**
	 * @param $boolean, whether use rule strict
	 */
	static public function SetRuleStrict($boolean=true){
		self::$ruleStrict = ($boolean===true);
	}
	
	/**
	 * @param $idSender, mixed id sender,which need be filter
	 */
	static public function AddRuleIdSender($idSender=null){
		setType($idSender, 'array');
		self::$ruleIdSender = array_unique( array_merge( self::$ruleIdSender, $idSender ) );
	}

	/**
	 * @param $idSender, mixed id sender,which need not be filter
	 */
	static public function RemoveRuleIdSender($idSender=null){
		setType($idSender, 'array');
		self::$ruleIdSender = array_unique( array_diff( self::$ruleIdSender, $idSender ) );
	}

	/**
	 * @param $idReciever, mixed id sender,which need be filter
	 */
	static public function SetRuleIdReciever($idReciever=null){
		setType($idReciever, 'array');
		self::$ruleIdSender = array_unique( array_merge( self::$ruleIdReciever, $idReciever ) );
	}

	/**
	 * @param $idReciever, mixed id sender,which need be filter
	 */
	static public function RemoveRuleIdReciever($idReciever=null){
		setType($idReciever, 'array');
		self::$ruleIdReciever = array_unique( array_diff( self::$ruleIdReciever, $idReciever ) );
	}

	/**
	 * @param $status
	 * @param $idSender
	 * @param $idReciever
	 * @param $device
	 */
	static public function IsNeedFilter($status=null, $idSender=null, $idReciever=null, $device=null){
		if (self::$ruleStrict){
			return true;
		}

		if (self::$ruleWord && self::$dictFilter){
			$words = self::$dictFilter->GetFilterWords( "{$status}|{$status}" );
			if( !empty($words) ){
				return true;
			}
		}

		if (in_array($idSender,self::$ruleIdSender)){
			return true;
		}

		if (in_array($idReciever,self::$ruleIdReciever)){
			return true;
		}

		return false;
	}

}
?>
