<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 *
 * XXX 目前使用的是 JWTemplate::GetAssetUrl，未启用 JWAsset 类
 *
 */

/**
 * JiWai.de Asset Class
 */
class JWAsset {
	/**
	 * Instance of this singleton
	 *
	 * @var JWAsset
	 */
	static private $msInstance;

	/**
	 * path_config
	 *
	 * @var
	 */
	static private $msAssetRoot;

	static private $msAssetCounter 	= 0;
	static private $msAssetMax 		= 6;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWAsset
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
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
		throw new JWException("目前使用的是 JWTemplate::GetAssetUrl，未启用 JWAsset 类");

		self::$msAssetRoot 		= JW_ROOT . "domain/asset/";
		self::$msAssetCounter	= 0;
		self::$msAssetMax		= 6;
	}


	static public function GetDomain()
	{
		self::Instance();

		$n = self::$msAssetCounter++;
		$n %= self::$msAssetMax;
		$n += 1;

		return "http://asset$n.JiWai.de/";
	}

}
?>
