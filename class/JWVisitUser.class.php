<?php
/**
 * @package	JiWai.de
 * @copyright   AKA Inc.
 * @author	wqsemc@jiwai.com
 * @version	$Id$
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

	static public function Record($idUser, $ip=null)
	{
		$ip = JWRequest::GetRemoteIp();
		$mc_key = self::GetCacheKeyByUserIdAndIp($idUser, $ip); 
		$memcache = JWMemcache::Instance();

		$v = $memcache->Get( $mc_key );
		if( $v )
			return false;

		$memcache->Set( $mc_key, 1, 0, 1);
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
		}
		$mc_key2 = self::GetCacheKeyUserIds();
		$v2 = $memcache->Get( $mc_key2 );
		if(!$v2)
			$v2 = array();

		array_push($v2, $idUser);
		$v2 = array_unique($v2);
		$memcache->Set($mc_key2, $v2);

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
			if(!$v) $v=1;

			$user_info = JWUser::GetDbRowById( $idUser );
			if(!empty($user_info))
			{
				$condition = array(
						'idUser' => $idUser,
						'count' => $v,
						);
				$row = JWDB::SaveTableRow('VisitUser', $condition);
			}
			$memcache->Del($mc_key);
		}
		$memcache->Set($mc_key2, array());

		return true;
	}

	static public function Total($size=10)
	{
		$memcache = JWMemcache::Instance();
		$mc_key = self::GetCacheKeyTotal();
		$row = $memcache->Get($mc_key);

		if (empty($row)) {
			$expire = 60 * 60; //1 hour cache
			$limit = 100;
			$yesterday = date('Y-m-d', strtotime('1 days ago'));
			$today = date('Y-m-d', time());
			$sql="SELECT idUser,SUM(count) AS sum FROM VisitUser FORCE INDEX(IDX__VisitUser__timeStamp) WHERE timeStamp >='{$yesterday}' AND timeStamp <'{$today}' AND idUser IS NOT NULL GROUP BY idUser ORDER BY sum DESC LIMIT 0,100";
			$row = JWDB::GetQueryResult($sql, true);
			if(empty($row))
				$row = array();

			/* block admin user */
			foreach( $row AS $k=>$one ) {
				if($one['idUser'] == '498')
					unset($row[$k]);
			}
			/* end block */
			$memcache->Set($mc_key, $row, 0, $expire);
		}

		if ( empty($row) )
			return array();

		if ($size < count($row)) 
			return array_slice($row, 0, $size);
		return $row;
	}

	static public function GetCount($idUser)
	{
		$sql = "select sum(count) as sum from VisitUser where idUser=$idUser";
		$row = JWDB::GetQueryResult($sql);

		return $row['sum'];
	}
}
