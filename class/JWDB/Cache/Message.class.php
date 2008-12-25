<?php
/**
 * @package		JiWai.de
 * @copyright		AKA Inc.
 * @author	  	seek@jiwai.de
 * @date	  	2007/07/06
 */

/**
 * JiWai.de File Class
 */
class JWDB_Cache_Message implements JWDB_Cache_Interface
{
	/**
	 * Instance of this singleton
	 *
	 * @var JWDB_Cache_Message
	 */

	static $msInstance;


	static private $msMemcache	= null;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWMessage
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
	static public function GetDbRowsByIds ($message_ids)
	{
		if( empty($message_ids) || false==is_array($message_ids) )
			return array();

		$message_ids = array_unique( $message_ids );
		return JWDB_Cache::GetCachedDbRowsByIds('Message', $message_ids);
	}

	static public function GetDbRowById ($message_id)
	{
		$db_rows = self::GetDbRowsByIds(array($message_id));

		if ( empty($db_rows) )
			return array();

		return $db_rows[$message_id];
	}

	static public function GetNewMessageNum($idUser) {
		$idUser = JWDB::CheckInt($idUser);

		$ds_function = array('JWMessage','GetNewMessageNum');
		$ds_param = array($idUser);

		$mc_key = JWDB_Cache::GetCacheKeyByFunction($ds_function,$ds_param);

		$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		$num = JWDB_Cache::GetCachedValueByKey(
			 $mc_key
			,$ds_function
			,$ds_param
			,$expire_time
		);
		return $num;
	}

	static public function GetNewNoticeMessageNum($idUser) {
		$idUser = JWDB::CheckInt($idUser);

		$ds_function = array('JWMessage','GetNewNoticeMessageNum');
		$ds_param = array($idUser);

		$mc_key = JWDB_Cache::GetCacheKeyByFunction($ds_function,$ds_param);

		$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		$num = JWDB_Cache::GetCachedValueByKey(
			 $mc_key
			,$ds_function
			,$ds_param
			,$expire_time
		);
		return $num;
	}

	static public function GetAllInputMessageNum($idUser) {
		$idUser = JWDB::CheckInt($idUser);

		$ds_function = array('JWMessage','GetAllInputMessageNum');
		$ds_param = array($idUser);

		$mc_key = JWDB_Cache::GetCacheKeyByFunction($ds_function,$ds_param);

		$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		$num = JWDB_Cache::GetCachedValueByKey(
			 $mc_key
			,$ds_function
			,$ds_param
			,$expire_time
		);
		return $num;
	}

	static public function GetAllOutputMessageNum($idUser) {
		$idUser = JWDB::CheckInt($idUser);

		$ds_function = array('JWMessage','GetAllOutputMessageNum');
		$ds_param = array($idUser);

		$mc_key = JWDB_Cache::GetCacheKeyByFunction($ds_function,$ds_param);

		$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		$num = JWDB_Cache::GetCachedValueByKey(
			 $mc_key
			,$ds_function
			,$ds_param
			,$expire_time
		);
		return $num;
	}

	static public function GetAllNoticeMessageNum($idUser) {
		$idUser = JWDB::CheckInt($idUser);

		$ds_function = array('JWMessage','GetAllNoticeMessageNum');
		$ds_param = array($idUser);

		$mc_key = JWDB_Cache::GetCacheKeyByFunction($ds_function,$ds_param);

		$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		$num = JWDB_Cache::GetCachedValueByKey(
			 $mc_key
			,$ds_function
			,$ds_param
			,$expire_time
		);
		return $num;
	}

	static function OnDirty($dbRow, $table=null)
	{
		self::Instance();

		$pk_id = $dbRow['id'];
		$sender_id = abs(intval($dbRow['idUserSender']));
		$receiver_id = abs(intval($dbRow['idUserReceiver']));

		$name = strtoupper($dbRow['name']);

		$dirty_keys = array(
			 JWDB_Cache::GetCacheKeyById		('Message'	, $pk_id)
		);

		array_push( $dirty_keys
			, JWDB_Cache::GetCacheKeyByFunction( array('JWMessage', 'GetAllInputMessageNum'), array($receiver_id) )
			, JWDB_Cache::GetCacheKeyByFunction( array('JWMessage', 'GetNewMessageNum'), array($receiver_id) )
			, JWDB_Cache::GetCacheKeyByFunction( array('JWMessage', 'GetAllNoticeMessageNum'), array($receiver_id) )
			, JWDB_Cache::GetCacheKeyByFunction( array('JWMessage', 'GetNewNoticeMessageNum'), array($receiver_id) )
			, JWDB_Cache::GetCacheKeyByFunction( array('JWMessage', 'GetAllOutputMessageNum'), array($sender_id) )
		);

		foreach ( $dirty_keys as $dirty_key )
		{
			self::$msMemcache->Del($dirty_key);
		}
	}
}
?>
