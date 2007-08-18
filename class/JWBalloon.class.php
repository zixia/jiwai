<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Balloon Class
 */
class JWBalloon {
	/**
	 * Instance of this singleton
	 *
	 * @var JWBalloon
	 */
	static private $msInstance;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWBalloon
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
	}

	static function GetDbRowById($idBalloon)
	{
		$rows = self::GetDbRowsByIds(array($idBalloon));
		return $rows[$idBalloon];
	}

	static function GetDbRowsByIds($idBalloons)
	{
		if ( empty($idBalloons) )
			return array();

		if ( !is_array($idBalloons) )
			throw new JWException('must array');

		$idStatuses = array_unique($idBalloons);

		$condition_in = JWDB_Cache::GetInConditionFromArray($idBalloons);

		/*
		 *	每个结果集中，必须保留 id，为了 memcache 统一处理主键
		 */
		$sql = <<<_SQL_
SELECT	 *
		,id as idBalloon
FROM	Balloon
WHERE	id IN ($condition_in)
_SQL_;

		$rows = JWDB_Cache::GetQueryResult($sql,true);


		if ( empty($rows) ){
			$balloon_map = array();
		} else {
			foreach ( $rows as $row ) {
				$balloon_map[$row['idBalloon']] = $row;
			}
		}

		return $balloon_map;
	}


	/**
	 * 	Get ids of whom is $idUser's friend.
	 *	@return array	array of friend id list
	 */
	static function GetBalloonIds($idUser, $numMax=9999, $start=0)
	{
		$idUser = JWDB::CheckInt($idUser);
		$numMax = JWDB::CheckInt($numMax);
		$start  = intval($start);

		$sql = <<<_SQL_
SELECT	id as idBalloon
FROM	Balloon
WHERE	idUser=$idUser
ORDER BY id DESC
LIMIT	$start,$numMax
_SQL_;

		$arr_result = JWDB::GetQueryResult($sql, true);

		if ( empty($arr_result) )
		{
			return array();
		}

		$arr_friend_id = array();
		foreach ( $arr_result as $row )
			array_push($arr_friend_id, $row['idBalloon']);

		return $arr_friend_id;
	}


	/*
	 *	idUser 删除好友 idBalloon
	 * @param	int	idBalloon
	 * @param	int	idUser
	 * @return 
			true: 成功 
			false: 失败
	 */
	static public function Destroy($idBalloon)
	{
		$idBalloon	= JWDB::CheckInt($idBalloon);

		return JWDB::DelTableRow('Balloon', array(	'id'	=> $idBalloon
												)
								);
	}


	/*
	 *	给用户一个泡泡显示
	 * @param	int		idUser
	 * @param	string	html
	 * @return 
			true: 成功 
			false: 失败
	 */
	static public function Create($idUser, $html)
	{
		$idUser 	= JWDB::CheckInt($idUser);

		return JWDB::SaveTableRow('Balloon', array(	 'idUser'	=> $idUser
													,'html'		=> $html
													,'timeCreate'	=> JWDB::MysqlFuncion_Now()
												)
								);
	}

	/*
	 *	@param	int		$idUser
	 *	@return	int		$friendNum for $idUser
	 */
	static public function GetBalloonNum($idUser)
	{
		$idUser = JWDB::CheckIn($idUser);

		$sql = <<<_SQL_
SELECT	COUNT(*) as num
FROM	Balloon
WHERE	idUser=$idUser
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}



}
?>
