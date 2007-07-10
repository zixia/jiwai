<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @date	  	2007/07/06
 */

/**
 * JiWai.de JWDBCacheUser Class
 */
class JWDB_Cache_User implements JWDB_Cache_Interface
{
	/**
	 * Instance of this singleton
	 *
	 * @var JWDBCacheUser
	 */
	static private $msInstance = null;

	static private $msMemcache	= null;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWDBCacheUser
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

	static function OnDirty($idPK, $dbRow, $table=null)
	{
		/* 	取出 idPK 的 row，
		 *	然后依据自己表中的cache逻辑，得到相关其他应该 OnDirty 的 key
		 *	接下来一个一个的 OnDirty 过去
		 */ 

		$email			= $dbRow['email'];
		$screen_name	= $dbRow['nameScreen'];

		$dirty_keys = array();

		array_push( $dirty_keys,	"User(id=$idPK)" );
		array_push( $dirty_keys,	"User(email=$email)" );
		array_push( $dirty_keys,	"User(nameScreen=$screen_name)" );

		foreach ( $dirty_keys as $dirty_key )
		{
			self::$msMemcache->Del($dirty_key);
		}
	}
}
?>
