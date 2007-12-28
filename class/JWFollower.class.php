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
	static function IsFollower($user_id, $follower_user_id)
	{
		if( false == $follower_user_id || false == $user_id )
			return false;

		$user_id = JWDB::CheckInt( $user_id );
		$follower_user_id = JWDB::CheckInt( $follower_user_id );

		$following_array = self::GetFollowingIds( $follower_user_id );

		return in_array( $user_id, $following_array );
	}



	/**
	 * 	Get follower list
	 *	@return array	array of follower id list
	 */
	static function GetFollowerIds($user_id, $num=self::DEFAULT_FOLLOWER_MAX, $offset = 0)
	{
		$cached_result = JWDB_Cache_Follower::GetFollowerInfos_Inner($user_id,$num,$offset);

		if ( empty($cached_result) )
			return array();

		return $cached_result['user_ids'];
	}
	
	/**
	 * for called for JWDB_Cache_Follower 
	 */
	static function GetNotificationIds($user_id)
	{
		$user_id = JWDB::CheckInt($user_id);

		$sql = <<<_SQL_
SELECT	idFollower
FROM	
	Follower
WHERE	
	idUser=$user_id
	AND notification='Y'
ORDER BY id DESC
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
	static public function Destroy($user_id, $follower_user_id)
	{
		$user_id = JWDB::CheckInt($user_id);
		$follower_user_id = JWDB::CheckInt($follower_user_id);

		return JWDB_Cache::DelTableRow('Follower', array(
			'idUser' => $user_id,
			'idFollower' => $follower_user_id,
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
	static public function Create($user_id, $follower_user_id, $notification='N')
	{
		$user_id = JWDB::CheckInt($user_id);
		$follower_user_id = JWDB::CheckInt($follower_user_id);

		return JWDB_Cache::SaveTableRow('Follower', array(
			'idUser' => $user_id,
			'idFollower' => $follower_user_id,
			'notification' => $notification,
			'timeCreate' => JWDB::MysqlFuncion_Now(),
		));
	}

	static public function SetNotification($user_id, $follower_user_id, $notification='N'){
		$user_id = JWDB::CheckInt($user_id);
		$follower_user_id = JWDB::CheckInt($follower_user_id);
		
		$idExist = JWDB::ExistTableRow( 'Follower', array(
			'idUser' => $user_id,
			'idFollower' => $follower_user_id,
		));

		if( $idExist ) {
			return JWDB_Cache::UpdateTableRow( 'Follower', $idExist, array(
				'notification' => $notification,
			));
		}
		
		return true;	
	}

	/**
	 * 	Get ids of whom is $user_id's friend.
	 *	@return array	array of friend id list
	 */
	static function GetFollowingIds($user_id, $num=JWFollower::DEFAULT_FOLLOWER_MAX, $offset=0)
	{
		$cached_result = JWDB_Cache_Follower::GetFollowingInfos_Inner($user_id, $num, $offset);
		
		if ( empty($cached_result) )
			return array();

		return $cached_result['user_ids'];
	}

	/**
	 * 	for called from JWDB_Cache_Follower
	 * 	Get ids of whom is $user_id's friend (bio)
	 *	@return array	array of friend id list
	 */
	static function GetBioFollowingIds($user_id)
	{
		$user_id = JWDB::CheckInt($user_id);

		$sql = <<<_SQL_
SELECT	idUser
FROM	Follower
WHERE	idFollower=$user_id
		AND idUser IN
		(
		 SELECT idFollower FROM Follower WHERE idUser=$user_id
		)
ORDER BY id DESC
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

	static function GetFollowerInfos($user_id, $num=self::DEFAULT_FOLLOWER_MAX, $offset=0) {

		$user_id = JWDB::CheckInt($user_id);
		$num = JWDB::CheckInt($num);
		
		$cached_result = JWDB_Cache_Follower::GetFollowerInfos_Inner($user_id, $num, $offset);
		
		if ( empty($cached_result) )
			return array();

		$ids = $cached_result['ids'];
		
		$arr_result = JWDB_Cache_Follower::GetDbRowsByIds( $ids );
		if ( empty($arr_result) )
			return array();

		$rtn = array();
		foreach ( $arr_result as $row ){
			$rtn[ $row['idFollower'] ] = $row;
		}

		return $rtn;
	}

	static function GetFollowingInfos($user_id, $num=self::DEFAULT_FOLLOWER_MAX, $offset=0) {

		$user_id = JWDB::CheckInt($user_id);
		$num = JWDB::CheckInt($num);

		$cached_result = JWDB_Cache_Follower::GetFollowingInfos_Inner($user_id, $num, $offset);
		
		if ( empty($cached_result) )
			return array();

		$ids = $cached_result['ids'];
		
		$arr_result = JWDB_Cache_Follower::GetDbRowsByIds( $ids );
		if ( empty($arr_result) )
			return array();

		$rtn = array();
		foreach ( $arr_result as $row ){
			$rtn[ $row['idUser'] ] = $row;
		}

		return $rtn;
	}
	
	/**
	 * for called by jwdb_cache_follower 
	 */
	static function GetFollowerInfos_Inner($user_id, $num=self::DEFAULT_FOLLOWER_MAX, $offset=0)
	{
		$user_id = JWDB::CheckInt($user_id);
		$num = JWDB::CheckInt($num);

		$sql = <<<_SQL_
SELECT	
	id,idFollower
FROM	
	Follower
WHERE	
	idUser=$user_id
	AND idFollower IS NOT NULL
ORDER BY id DESC
LIMIT $offset, $num
_SQL_;

		$arr_result = JWDB::GetQueryResult($sql, true);

		if ( empty($arr_result) )
			return array();

		$rtn_id = array();
		$rtn_user = array();
		foreach ( $arr_result as $row ){
			array_push( $rtn_id, $row['id'] );
			array_push( $rtn_user, $row['idFollower'] );
		}

		return array('ids'=> $rtn_id, 'user_ids'=> $rtn_user,);
	}

	/**
	 * for called by jwdb_cache_follower 
	 */
	static function GetFollowingInfos_Inner($user_id, $num=self::DEFAULT_FOLLOWER_MAX, $offset=0)
	{
		$user_id = JWDB::CheckInt($user_id);
		$num = JWDB::CheckInt($num);


		$sql = <<<_SQL_
SELECT	
	id,idUser
FROM	
	Follower
WHERE	
	idFollower=$user_id
	AND idFollower IS NOT NULL
ORDER BY id DESC
LIMIT $offset, $num
_SQL_;

		$arr_result = JWDB::GetQueryResult($sql, true);

		if ( empty($arr_result) )
			return array();

		$rtn_id = array();
		$rtn_user = array();
		foreach ( $arr_result as $row ){
			array_push( $rtn_id, $row['id'] );
			array_push( $rtn_user, $row['idUser'] );
		}

		return array('ids'=> $rtn_id, 'user_ids'=> $rtn_user,);
	}

	/**
	 * for called by jwdb_cache_follower 
	 */
	static public function GetDbRowById($id)
	{
		$id = JWDB::CheckInt($id);
		$follower_db_rows = self::GetDbRowsByIds(array($id));

		if ( empty($follower_db_rows) )
			return array();

		return $follower_db_rows[$id];
	}

	/**
	 * for called by jwdb_cache_follower 
	 */
	static public function GetDbRowsByIds($ids)
	{
		if ( empty($ids) )
			return array();

		if ( false==is_array($ids) )
			throw new JWException('must array');

		$ids = array_unique( $ids );

		$condition_in = JWDB::GetInConditionFromArray($ids);

		$sql = <<<_SQL_
SELECT	*
FROM	Follower	
WHERE	id IN ($condition_in)
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);

		$follow_map = array();

		if ( empty($rows) )
			return array();

		foreach ( $rows as $row )
		{
			$follow_map[$row['id']] = $row;
		}

		return $follow_map;
	}

	/*
	 *	@param	int		$user_id
	 *	@return	int		$friendNum for $user_id
	 */
	static public function GetFollowingNum($user_id)
	{
		$user_id = intval($user_id);

		if ( !is_int($user_id) )
			throw new JWException('must be int');

		$sql = <<<_SQL_
SELECT	COUNT(*) as num
FROM	Follower
WHERE	idFollower=$user_id
		AND idUser IS NOT NULL
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}

	/*
	 *	@param	int		$user_id
	 *	@return	int		$followerNum for $user_id
	 */
	static public function GetFollowerNum($user_id)
	{
		$user_id = JWDB::CheckInt($user_id);

		$sql = <<<_SQL_
SELECT	COUNT(*) as num
FROM	Follower
WHERE	idUser=$user_id
		AND idFollower IS NOT NULL
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}
}
?>
