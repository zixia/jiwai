<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Follower Class
 */
class JWFollower {
	/**
	 * Instance of this singleton
	 *
	 * @var JWFollower
	 */
	static private $msInstance;

	const	DEFAULT_FOLLOWER_MAX	= 9999;
	/**
	 * Instance of this singleton class
	 *
	 * @return JWFollower
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


	/**
	 *	这个函数有些复杂。;-)

	 * 	Is idFollowers is idUser's follower? or check bidirection? 
	 *	根据 $idFollowers，返回二维($idUser,$idFollowers) 为 key 的 array，true为是朋友，false 为不是朋友
	 *
	 *	@param	array of int	$idFollowers
	 *	@param	bool			$biDirection	如果是 true，则检查双向关系

	 *	@return	array	$follower_relation[idUser][idFollower] = true/false
						如果 biDirection, 还会有 $follower_rows[idFollower][idUser] 来确认 idUser 有没有被 idFollower 订阅 
						ie.	array(1=>array(2=>true, 4=>false), 2=>array(...) );
						如果 true，说明 idFollower 是 idUesr 订阅者
	 */
	static function IsFollowers($idUser, $idFollowers, $biDirection=false)
	{
		$idUser = intval($idUser);

		if ( 0>=$idUser )
			throw new JWException('must int');

		if ( empty($idFollowers) )
			return array();

		if ( !is_array($idFollowers) )
			throw new JWException('must array');

		// prepare for memcache, key may like this: Follower_IdFollower_of_$IdUser_$user_follower_version
		// $user_follower_version 	= JWDB::GetMaxId('Follower', array('idUser'=>$idUser));

		$user_follower_ids 		= JWFollower::GetFollowerIds	($idUser, 9999);
		
		$user_follower_rows = array();

		if ( !empty($user_follower_ids) ) {
			foreach ( $user_follower_ids as $user_follower_id ) {
				$user_follower_rows[$idUser][$user_follower_id] = true;
			}
		}

		/*
		 *	如果查找双向的关系，找出都有谁添加了 $idUser 为好友
	 	 */
		if ( $biDirection )
		{
			$user_be_follower_ids		= JWFollower::GetBeFollowerIds	($idUser, 9999);
		
			if ( !empty($user_be_follower_ids) ) {
				foreach ( $user_be_follower_ids as $user_be_follower_id ) {
					$user_follower_rows[$user_be_follower_id][$idUser] = true;
				}
			}
		}

		$return_follower_relation = array();

		foreach ( $idFollowers as $follower_id )
		{
			// 检查正向订阅关系
			if ( isset($user_follower_rows[$idUser][$follower_id]) )
				$return_follower_relation[$idUser][$follower_id] = true;
			else
				$return_follower_relation[$idUser][$follower_id] = false;


			// 检查反向订阅关系
			if ( $biDirection )
			{
				if ( isset($user_follower_rows[$follower_id][$idUser]) )
					$return_follower_relation[$follower_id][$idUser] = true;
				else
					$return_follower_relation[$follower_id][$idUser] = false;
			}
			
		}

		return $return_follower_relation;
	}


	/**
	 * Is idFollower is idUser's follower?
	 *
	 */
	static function IsFollower($idUser, $idFollower)
	{
		$is_follower_rows = self::IsFollowers($idUser, array($idFollower));

		return $is_follower_rows[$idUser][$idFollower];
	}


	/**
	 * 	Get follower list
	 *	@return array	array of follower id list
	 */
	static function GetFollowerIds($idUser, $numMax=JWFollower::DEFAULT_FOLLOWER_MAX, $offset = 0)
	{
		$idUser = intval($idUser);
		$numMax = intval($numMax);

		if ( 0>=$idUser || 0>=$numMax )
			throw new JWException('not int');

		$sql = <<<_SQL_
SELECT	idFollower
FROM	Follower
WHERE	idUser=$idUser
		AND idFollower IS NOT NULL
		ORDER BY id DESC
LIMIT $offset,$numMax
_SQL_;

		$arr_result = JWDB::GetQueryResult($sql, true);

		if ( empty($arr_result) )
		{
			return null;
		}

		$arr_follower_id = array();
		foreach ( $arr_result as $row )
			array_push($arr_follower_id, intval($row['idFollower']));

		return $arr_follower_id;
	}


	/**
	 * 	Get ids of whom is follower with $idUser
	 *	@return array	array of be-follower id list
	 */
	static function GetBeFollowerIds($idFollower, $numMax=40, $start=0)
	{
		$idFollower 	= intval($idFollower);
		$numMax 	= intval($numMax);
		$start  	= intval($start);

		if ( 0==$idFollower || 0==$numMax )
			throw new JWException('not int');

		$sql = <<<_SQL_
SELECT	idUser
FROM	Follower
WHERE	idFollower=$idFollower
		AND idUser IS NOT NULL
LIMIT	$start,$numMax
_SQL_;

		$arr_result = JWDB::GetQueryResult($sql, true);

		if ( empty($arr_result) )
			return array();

		$arr_user_id = array();
		foreach ( $arr_result as $row )
			array_push($arr_user_id, $row['idUser']);

		return $arr_user_id;
	}



	/*
	 *	取消 idUser 的 follower idFollower
	 * @param	int	idFollower
	 * @param	int	idUser
	 * @return 
			true: 成功 
			false: 失败
	 */
	static public function Destroy($idUser, $idFollower)
	{
		$idUser 	= JWDB::CheckInt($idUser);
		$idFollower = JWDB::CheckInt($idFollower);

		return JWDB::DelTableRow('Follower', array(	 'idUser'	=> $idUser
													,'idFollower'	=> $idFollower
												)
								);
	}


	/*
	 *	添加 idFollower 为 idUser 订阅者
	 * @param	int	idFollower
	 * @param	int	idUser
	 * @return 
			true: 成功 
			false: 失败
	 */
	static public function Create($idUser, $idFollower)
	{
		$idUser 	= JWDB::CheckInt($idUser);
		$idFollower = JWDB::CheckInt($idFollower);

		return JWDB::SaveTableRow('Follower', array(	 'idUser'		=> $idUser
														,'idFollower'	=> $idFollower
														,'timeCreate'	=> JWDB::MysqlFuncion_Now()
												)
									);
	}

	/*
	 *	@param	int		$idUser
	 *	@return	int		$followerNum for $idUser
	 */
	static public function GetFollowerNum($idUser)
	{
		$idUser = intval($idUser);

		if ( !is_int($idUser) )
			throw new JWException('must be int');

		$sql = <<<_SQL_
SELECT	COUNT(*) as num
FROM	Follower
WHERE	idUser=$idUser
		AND idFollower IS NOT NULL
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}
}
?>
