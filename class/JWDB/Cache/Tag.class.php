<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @date	  	2007/07/06
 */

/**
 * JiWai.de File Class
 */
class JWDB_Cache_Tag implements JWDB_Cache_Interface
{
	/**
	 * Instance of this singleton
	 *
	 * @var JWDB_Cache_Tag
	 */

	static $msInstance;


	static private $msMemcache	= null;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWTag
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
		self::$msMemcache = JWMemcache::Instance();
	}

	/*
	 *	规范命名方式，以后都应该是 GetDbRowsByIds 或者 GetDbRowById，不用在函数名称中加数据库表名
	 */
	static public function GetDbRowsByIds ($tag_ids)
	{
		if( empty($tag_ids) || false==is_array($tag_ids) )
			return array();

		$tag_ids = array_unique( $tag_ids );
		return JWDB_Cache::GetCachedDbRowsByIds('Tag', $tag_ids);
	}

	static public function GetDbRowById ($tag_id)
	{
		$db_rows = self::GetDbRowsByIds(array($tag_id));

		if ( empty($db_rows) )
			return array();

		return $db_rows[$tag_id];
	}

	static public function GetDbRowByName($tag_name)
	{
		$tag_name = strtoupper(trim($tag_name));
		$ds_function = array('JWTag','GetDbRowByName');
		$ds_param = array($tag_name);

		$mc_key = JWDB_Cache::GetCacheKeyByFunction($ds_function,$ds_param);

		$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		$tag_info = JWDB_Cache::GetCachedValueByKey(
			 $mc_key
			,$ds_function
			,$ds_param
			,$expire_time
		);

		return $tag_info;
	}

	static function OnDirty($dbRow, $table=null)
	{
		self::Instance();

		$pk_id = $dbRow['id'];
		$name = strtoupper($dbRow['name']);

		$dirty_keys = array(
			 JWDB_Cache::GetCacheKeyById		('Tag'	, $pk_id)
		);

		array_push( $dirty_keys
			, JWDB_Cache::GetCacheKeyByFunction( array('JWTag', 'GetDbRowByName'), array($name) )
		);

		foreach ( $dirty_keys as $dirty_key )
		{
			self::$msMemcache->Del($dirty_key);
		}
	}
}
?>
