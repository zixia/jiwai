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

class JWVisitTag
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

	static public function Record($idTag, $ip)
	{

		$mc_key = self::GetCacheKeyByTagIdAndIp($idTag, $ip); 
		$memcache = JWMemcache::Instance();

		$v = $memcache->Get( $mc_key );
		if( $v )
			return false;

		$memcache->Set( $mc_key, 1, 0, 1);
		self::SetCount($idTag);

		return true;

	}

	static public function GetCacheKeyTagIds()
	{
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array( 'JWVisitTag', 'GetCacheKeyTagIds' ), array());
		return $mc_key;
	}

	static public function GetCacheKeyTotal()
	{
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array( 'JWVisitTag', 'GetCacheKeyTotal' ), array());
		return $mc_key;
	}

	static public function GetCacheKeyByTagId($idTag)
	{
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array( 'JWVisitTag', 'GetCacheKeyByTagId' ), array($idTag));
		return $mc_key;
	}

	static public function GetCacheKeyByTagIdAndIp($idTag, $ip)
	{
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array( 'JWVisitTag', 'GetCacheKeyByTagIdAndIp' ), array($idTag, $ip));
		return $mc_key;
	}

	static public function SetCount($idTag)
	{
		$mc_key = self::GetCacheKeyByTagId($idTag);
		$memcache = JWMemcache::Instance();

		$v = $memcache->Get( $mc_key );
		if( !$v )
		{
			$v = 0;
			$memcache->Set( $mc_key, $v);
			$mc_key2 = self::GetCacheKeyTagIds();
			$v2 = $memcache->Get( $mc_key2 );
			if(!$v2)
				$v2 = array();

			array_push($v2, $idTag);
			$v2 = array_unique($v2);
			$memcache->Set($mc_key2, $v2);
		}

		$memcache->Set($mc_key, $v+1);
		return true;
	}

	static public function Update()
	{
		$memcache = JWMemcache::Instance();
		$mc_key2 = self::GetCacheKeyTagIds();
		$idTags = $memcache->Get( $mc_key2 );

		foreach($idTags as $idTag)
		{
			$mc_key = self::GetCacheKeyByTagId($idTag);
			$v = $memcache->Get( $mc_key );
			if(!$v) $v=1;

			$tag_info = JWTag::GetDbRowById( $idTag );
			if(empty($tag_info))
			{
				$condition = array(
					'idTag' => $idTag,
					'count' => $v,
				);
				$row = JWDB::SaveTableRow('VisitTag', $condition);
			}
			$memcache->Del($mc_key);
		}
		$memcache->Set($mc_key2, array());

		return true;
	}

	static public function Query($limit=null)
	{
		$month = date("m");
		$day = date("d");
		$year = date("Y");
		$yesterday = date("Y-m-d", mktime (0, 0, 0, $month, $day-1, $year));
		$today = "$year-$month-$day";
		$sql="select idTag,sum(count)as sum from VisitTag force index(IDX__VisitTag__timeStamp) where timeStamp >='$yesterday' and timeStamp <'$today' group by idTag order by sum desc";
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
