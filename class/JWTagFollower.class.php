<?php
/**
 * @package	JiWai.de
 * @copyright	AKA Inc.
 * @author	shwdai@gmail.com
 * @version	$Id$
 */

/**
 * JiWai.de Follower Class
 */
class JWTagFollower {
	/**
	 * Instance of this singleton
	 *
	 * @var JWTagFollower
	 */
	static private $msInstance;

	const DEFAULT_FOLLOWER_MAX = 9999;
	/**
	 * Instance of this singleton class
	 *
	 * @return JWTagFollower
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
	static function IsFollower($tag_id, $user_id)
	{
		if( false == $user_id || false == $tag_id )
			return false;

		$user_id = JWDB::CheckInt( $user_id );
		$tag_id = JWDB::CheckInt( $tag_id );

		$follower_array = self::GetFollowerIds( $tag_id );

		return in_array( $user_id, $follower_array );
	}

	/**
	 * Get need notification's user_id by tag_id
	 */
	static function GetNotificationIds($tag_id, $num_max=self::DEFAULT_FOLLOWER_MAX, $offset = 0)
	{
		$tag_id = JWDB::CheckInt($tag_id);
		$num_max = JWDB::CheckInt($num_max);

		$follower_infos = self::GetFollowerInfos( $tag_id, $num_max, $offset );

		if ( empty($follower_infos) )
		{
			return array();
		}

		$arr_follower_id = array();
		foreach ( $follower_infos as $user_id => $row )
		{
			if( $row['notification'] == 'Y' ) 
				array_push($arr_follower_id, intval($row['idUser']));
		}
		return $arr_follower_id;
	}


	/**
	 * Destroy
	 */
	static public function Destroy($tag_id, $user_id)
	{
		$tag_id = JWDB::CheckInt($tag_id);
		$user_id = JWDB::CheckInt($user_id);

		return JWDB::DelTableRow('TagFollower', array(
			'idTag' => $tag_id,
			'idUser' => $user_id,
		));
	}


	/**
	 * Create
	 */
	static public function Create($tag_id, $user_id, $notification='N')
	{
		$tag_id = JWDB::CheckInt($tag_id);
		$user_id = JWDB::CheckInt($user_id);

		return JWDB::SaveTableRow('TagFollower', array(
			'idTag' => $tag_id,
			'idUser' => $user_id,
			'notification' => $notification,
			'timeCreate' => JWDB::MysqlFuncion_Now(),
		));
	}

	/**
	 * set notification by tag_id, user_id
	 */
	static public function SetNotification($tag_id, $user_id, $notification='N')
	{
		$tag_id = JWDB::CheckInt($tag_id);
		$user_id = JWDB::CheckInt($user_id);
		
		$exist_id = JWDB::ExistTableRow( 'TagFollower', array(
			'idTag' => $tag_id,
			'idUser' => $user_id,
		));

		if( $exist_id ) {
			return JWDB::UpdateTableRow( 'TagFollower', $exist_id, array(
				'notification' => $notification,
			));
		}
		
		return true;	
	}

	/**
	 * 	Get user_ids whom follow tag_id
	 *	@return array	array of follower id list
	 */
	static function GetFollowerIds($tag_id, $num_max=self::DEFAULT_FOLLOWER_MAX, $offset = 0)
	{
		$follower_infos = self::GetFollowerInfos( $tag_id, $num_max, $offset );
		return array_keys( $follower_infos );
	}

	/**
	 * 	Get tag_ids whom user_id following.
	 *	@return array	array of friend id list
	 */
	static function GetFollowingIds($user_id, $num_max=9999, $offset=0)
	{
		$following_infos = self::GetFollowingInfos( $user_id, $num_max, $offset );
		return array_keys( $following_infos );
	}

	/**
	 * Get tag's follower tag_follower info;
	 */
	static function GetFollowerInfos($tag_id, $num_max=self::DEFAULT_FOLLOWER_MAX, $offset=0) {

		$tag_id = JWDB::CheckInt($tag_id);
		$num_max = JWDB::CheckInt($num_max);
		
		$sql = <<<_SQL_
SELECT	*
	FROM	TagFollower
	WHERE	
		idTag=$tag_id
		AND idUser IS NOT NULL
	ORDER BY id DESC
	LIMIT $offset, $num_max
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

	/**
	 * Get user following tag_follower info;
	 */
	static function GetFollowingInfos($user_id, $num_max=self::DEFAULT_FOLLOWER_MAX, $offset=0) {

		$user_id = JWDB::CheckInt($user_id);
		$num_max = JWDB::CheckInt($num_max);

		$sql = <<<_SQL_
SELECT	*
	FROM	TagFollower
	WHERE	
		idUser=$user_id
		AND idTag IS NOT NULL
	ORDER BY id DESC
	LIMIT $offset, $num_max
_SQL_;
		$arr_result = JWDB::GetQueryResult($sql, true);

		if ( empty($arr_result) )
			return array();

		$rtn = array();
		foreach ( $arr_result as $row ){
			$rtn[ $row['idTag'] ] = $row;
		}

		return $rtn;
	}

	/**
	 * Get user's following tag ids;
	 *	@param int $idUser
	 */
	static public function GetFollowingNum($user_id)
	{
		$user_id = JWDB::CheckInt( $user_id );

		$sql = <<<_SQL_
SELECT	
	COUNT(1) as num
FROM	
	TagFollower
WHERE	
	idUser=$user_id
	AND idTag IS NOT NULL
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}

	/**
	 * Get Tag's follower user ids;
	 *	@param	int	$tag_id
	 */
	static public function GetFollowerNum($tag_id)
	{
		$tag_id = JWDB::CheckInt($tag_id);

		$sql = <<<_SQL_
SELECT	
	COUNT(1) as num
FROM	
	TagFollower
WHERE	
	idTag=$tag_id
	AND idUser IS NOT NULL
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}
}
?>
