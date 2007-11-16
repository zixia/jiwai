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

	const DEFAULT_FOLLOWER_MAX = 9999;
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
	 * Is idFollower is idUser's follower?
	 *
	 */
	static function IsFollower($idUser, $idFollower)
	{
		if( false == $idFollower || false == $idUser )
			return false;

		$idUser = JWDB::CheckInt( $idUser );
		$idFollower = JWDB::CheckInt( $idFollower );

		$followerArray = self::GetFollowerIds( $idUser );

		return in_array( $idFollower, $followerArray );
	}



	/**
	 * 	Get follower list
	 *	@return array	array of follower id list
	 */
	static function GetFollowerIds($idUser, $numMax=self::DEFAULT_FOLLOWER_MAX, $offset = 0)
	{
		$followerInfos = self::GetFollowerInfos( $idUser, $numMax, $offset );
		return array_keys( $followerInfos );
	}

	static function GetNotificationIds($idUser, $numMax=self::DEFAULT_FOLLOWER_MAX, $offset = 0)
	{
		$idUser = JWDB::CheckInt($idUser);
		$numMax = JWDB::CheckInt($numMax);

		$sql = <<<_SQL_
SELECT	idFollower
	FROM	Follower
	WHERE	
		idUser=$idUser 
		AND notification='Y'
	ORDER BY id DESC
	LIMIT $offset,$numMax
_SQL_;

		$arr_result = JWDB::GetQueryResult($sql, true);

		if ( empty($arr_result) )
		{
			return array();
		}

		$arr_follower_id = array();
		foreach ( $arr_result as $row )
			array_push($arr_follower_id, intval($row['idFollower']));

		return $arr_follower_id;
	}


	/*
	 * 取消 idUser 的 follower idFollower
	 * @param	int	idFollower
	 * @param	int	idUser
	 * @return 
			true: 成功 
			false: 失败
	 */
	static public function Destroy($idUser, $idFollower)
	{
		$idUser = JWDB::CheckInt($idUser);
		$idFollower = JWDB::CheckInt($idFollower);

		return JWDB::DelTableRow('Follower', array(
			'idUser' => $idUser,
			'idFollower' => $idFollower,
		));
	}


	/*
	 * 添加 idFollower 为 idUser 关注者
	 * @param	int	idFollower
	 * @param	int	idUser
	 * @return 
			true: 成功 
			false: 失败
	 */
	static public function Create($idUser, $idFollower, $notification='N')
	{
		$idUser = JWDB::CheckInt($idUser);
		$idFollower = JWDB::CheckInt($idFollower);

		return JWDB::SaveTableRow('Follower', array(
			'idUser' => $idUser,
			'idFollower' => $idFollower,
			'notification' => $notification,
			'timeCreate' => JWDB::MysqlFuncion_Now(),
		));
	}

	static public function SetNotification($idUser, $idFollower, $notification='N'){
		$idUser = JWDB::CheckInt($idUser);
		$idFollower = JWDB::CheckInt($idFollower);
		
		$idExist = JWDB::ExistTableRow( 'Follower', array(
			'idUser' => $idUser,
			'idFollower' => $idFollower,
		));

		if( $idExist ) {
			return JWDB::UpdateTableRow( 'Follower', $idExist, array(
				'notification' => $notification,
			));
		}
		
		return true;	
	}


	/*
	 *	@param	int		$idUser
	 *	@return	int		$friendNum for $idUser
	 */
	static public function GetFriendNum($idUser)
	{
		$idUser = intval($idUser);

		if ( !is_int($idUser) )
			throw new JWException('must be int');

		$sql = <<<_SQL_
SELECT	COUNT(*) as num
FROM	Follower
WHERE	idFollower=$idUser
		AND idUser IS NOT NULL
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}

	/**
	 * 	Get ids of whom is $idUser's friend.
	 *	@return array	array of friend id list
	 */
	static function GetFollowingIds($idUser, $numMax=9999, $offset=0)
	{
		$followingInfos = self::GetFollowingInfos( $idUser, $numMax, $offset );
		return array_keys( $followingInfos );
	}

	/**
	 * 	Get ids of whom is $idUser's friend (bio)
	 *	@return array	array of friend id list
	 */
	static function GetBioFollowingIds($idUser, $numMax=self::DEFAULT_FOLLOWER_MAX, $offset=0)
	{
		$idUser = JWDB::CheckInt($idUser);
		$numMax = JWDB::CheckInt($numMax);
		$offset  = intval($offset);

		$sql = <<<_SQL_
SELECT	idUser
FROM	Follower
WHERE	idFollower=$idUser 
		AND idUser IN
		(
		 SELECT idFollower FROM Follower WHERE idUser=$idUser 
		)
ORDER BY id DESC
LIMIT $offset,$numMax
_SQL_;

		$arr_result = JWDB::GetQueryResult($sql, true);

		if ( empty($arr_result) )
		{
			return array();
		}

		$rtn = array();
		foreach ( $arr_result as $row )
			array_push($rtn, $row['idUser']);

		return $rtn;
	}

	static function GetFollowerInfos($idUser, $numMax=self::DEFAULT_FOLLOWER_MAX, $offset=0) {

		$idUser = JWDB::CheckInt($idUser);
		$numMax = JWDB::CheckInt($numMax);
		
		$sql = <<<_SQL_
SELECT	*
	FROM	Follower
	WHERE	
		idUser=$idUser
		AND idFollower IS NOT NULL
	ORDER BY id DESC
	LIMIT $offset, $numMax
_SQL_;

		$arr_result = JWDB::GetQueryResult($sql, true);

		if ( empty($arr_result) )
			return array();

		$rtn = array();
		foreach ( $arr_result as $row ){
			$rtn[ $row['idFollower'] ] = $row;
		}

		return $rtn;
	}

	static function GetFollowingInfos($idUser, $numMax=self::DEFAULT_FOLLOWER_MAX, $offset=0) {

		$idUser = JWDB::CheckInt($idUser);
		$numMax = JWDB::CheckInt($numMax);

		$sql = <<<_SQL_
SELECT	*
	FROM	Follower
	WHERE	
		idFollower=$idUser
		AND idUser IS NOT NULL
	ORDER BY id DESC
	LIMIT $offset, $numMax
_SQL_;

		$arr_result = JWDB::GetQueryResult($sql, true);

		if ( empty($arr_result) )
			return array();

		$rtn = array();
		foreach ( $arr_result as $row ){
			$rtn[ $row['idUser'] ] = $row;
		}

		return $rtn;
	}

	/*
	 *	@param	int		$idUser
	 *	@return	int		$friendNum for $idUser
	 */
	static public function GetFollowingNum($idUser)
	{
		$idUser = intval($idUser);

		if ( !is_int($idUser) )
			throw new JWException('must be int');

		$sql = <<<_SQL_
SELECT	COUNT(*) as num
FROM	Follower
WHERE	idFollower=$idUser
		AND idUser IS NOT NULL
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}

	/*
	 *	@param	int		$idUser
	 *	@return	int		$followerNum for $idUser
	 */
	static public function GetFollowerNum($idUser)
	{
		$idUser = JWDB::CheckInt($idUser);

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
