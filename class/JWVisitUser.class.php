<?php
/**
 * @package	 JiWai.de
 * @copyright   AKA Inc.
 * @author	  wqsemc@jiwai.com
 * @version	 $Id$
 */

/**
 * 
 */

class JWVisitUser
{
	/**
	 * Instance of this singleton
	 *
	 * @var 
	 */
	static private $msInstance;

	/**
	 * Instance of this singleton class
	 *
	 * @return 
	 */
	static public function &Instance()
	{
		if (!isset(self::$msInstance)) {
			$class = __CLASS__;
			self::$msInstance = new $class;
		}
		return self::$msInstance;
	}

	static public function Record($idUser, $ip)
	{

		$mc_key = self::GetCacheKeyByUserIdAndIp($idUser, $ip); 
		$memcache = JWMemcache::Instance();

		$v = $memcache->Get( $mc_key );
		if( $v )
			return false;

		$memcache->Set( $mc_key, 1, 0, 600);
		self::SetCount($idUser);

		return true;

	}

	static public function GetCacheKeyUserIds()
	{
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array( 'JWVisitUser', 'GetCacheKeyUserIds' ), array());
		return $mc_key;
	}

	static public function GetCacheKeyTotal()
	{
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array( 'JWVisitUser', 'GetCacheKeyTotal' ), array());
		return $mc_key;
	}

	static public function GetCacheKeyByUserId($idUser)
	{
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array( 'JWVisitUser', 'GetCacheKeyByUserId' ), array($idUser));
		return $mc_key;
	}

	static public function GetCacheKeyByUserIdAndIp($idUser, $ip)
	{
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array( 'JWVisitUser', 'GetCacheKeyByUserIdAndIp' ), array($idUser, $ip));
		return $mc_key;
	}

	static public function SetCount($idUser)
	{
		$mc_key = self::GetCacheKeyByUserId($idUser);
		$memcache = JWMemcache::Instance();

		$v = $memcache->Get( $mc_key );
		if( !$v )
		{
			$v = 0;
			$memcache->Set( $mc_key, $v);
			$mc_key2 = self::GetCacheKeyUserIds();
			$v2 = $memcache->Get( $mc_key2 );
			if(!$v2)
				$v2 = array();

			array_push($v2, $idUser);
			$v2 = array_unique($v2);
			$memcache->Set($mc_key2, $v2);
		}

		$memcache->Set($mc_key, $v+1);
		return true;
	}

	static public function Update()
	{
		$memcache = JWMemcache::Instance();
		$mc_key2 = self::GetCacheKeyUserIds();
		$idUsers = $memcache->Get( $mc_key2 );

		foreach($idUsers as $idUser)
		{
			$mc_key = self::GetCacheKeyByUserId($idUser);
			$v = $memcache->Get( $mc_key );

			$condition = array(
				'idUser' => $idUser,
				'count' => $v,
			);
			$row = JWDB::SaveTableRow('VisitUser', $condition);
			$memcache->Set($mc_key, 0);
		}
		$memcache->Set($mc_key2, array());

		return true;
	}

	static public function Query($limit=null)
	{
		$month = date("m");
		$day = date("d");
		$year = date("Y");
		$yesterday = date("Y-m-d H:i:s", mktime (0, 0, 0, $month, $day-1, $year));
		$today = "$year-$month-$day";
		$sql="select idUser,count(1)as count from VisitUser where timeStamp >='$yesterday' and timeStamp <'$today' group by idUser order by count desc";
		if (!empty($limit)) $sql .= " limit $limit";
		$row = JWDB_Cache::GetQueryResult($sql, true);

		if(empty($row))
			$row = array();
		$memcache = JWMemcache::Instance();
		$mc_key = self::GetCacheKeyTotal();
		$memcache->Set($mc_key, $row);

		return $row;
	}

	static public function Total($limit=null)
	{
		$memcache = JWMemcache::Instance();
		$mc_key = self::GetCacheKeyTotal();
		$v = $memcache->Get($mc_key);
		if (!$v)
		{
			$v = self::Query($limit);
		}

		return $v;
			
	}
}