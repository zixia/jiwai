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
class JWDB_Cache_Picture implements JWDB_Cache_Interface
{
	/**
	 * Instance of this singleton
	 *
	 * @var JWDB_Cache_Picture
	 */

	static $msInstance;


	static private $msMemcache	= null;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWPicture
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
		self::$msMemcache 	= JWMemcache::Instance();
	}

	/*
	 *	规范命名方式，以后都应该是 GetDbRowsByIds 或者 GetDbRowById，不用在函数名称中加数据库表名
	 */
	static public function GetDbRowsByIds ($idPictures)
	{
		return JWDB_Cache::GetCachedDbRowsByIds('Picture', $idPictures);
	}

	static public function GetDbRowById ($idPicture)
	{
		$db_rows = self::GetDbRowsByIds(array($idPicture));

		if ( empty($db_rows) )
			return array();

		return $db_rows[$idPicture];
	}


	static function OnDirty($dbRow, $table=null)
	{
		self::Instance();

		/* 	取出 idPK 的 row，
		 *	然后依据自己表中的cache逻辑，得到相关其他应该 OnDirty 的 key
		 *	接下来一个一个的 OnDirty 过去
		 */ 

		$pk_id		= $dbRow['id'];
		$user_id	= $dbRow['idUser'];

		//$user_db_row = JWDB_Cache_User::GetUserInfo($user_id);
		//JWDB_Cache_User::OnDirty($user_db_row);

		$dirty_keys = array(
					 JWDB_Cache::GetCacheKeyById		('Picture'	, $pk_id)
			);

		foreach ( $dirty_keys as $dirty_key )
		{
			self::$msMemcache->Del($dirty_key);
		}
	}
}
?>
