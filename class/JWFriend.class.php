<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Friend Class
 */
class JWFriend {
	/**
	 * Instance of this singleton
	 *
	 * @var JWFriend
	 */
	static private $msInstance;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWFriend
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

	 * 	Is idFriends is idUser's friend? or check bidirection? 
	 *	根据 $idFriends，返回以 $idFriends 为 key 的 array，true为是朋友，false 为不是朋友
	 *
	 *	@param	array of int	$idFriends
	 *	@param	bool			$biDirection	如果是 true，则检查双向关系

	 *	@return	array	$friend_relation[idUser][idFriend] = true/false
						如果 biDirection, 还会有 $friend_rows[idFriend][idUser] 来确认 idUser 有没有被 idFriend 添加为好友
						ie.	array(1=>array(2=>true, 4=>false), 2=>array(...) );
						如果 true，说明 idFriend 是 idUesr 的好友
	 */
	static function IsFriends($idUser, $idFriends, $biDirection=false)
	{
		$idUser = intval($idUser);

		if ( 0>=$idUser )
			throw new JWException('must int');

		if ( empty($idFriends) )
			return array();

		if ( !is_array($idFriends) )
			throw new JWException('must array');

		// prepare for memcache, key may like this: Friend_IdFriend_of_$IdUser_$user_friend_version
		// $user_friend_version 	= JWDB::GetMaxId('Friend', array('idUser'=>$idUser));

		$user_friend_ids 		= JWFriend::GetFriendIds	($idUser, 9999);
		
		$user_friend_rows = array();

		if ( !empty($user_friend_ids) ) {
			foreach ( $user_friend_ids as $user_friend_id ) {
				$user_friend_rows[$idUser][$user_friend_id] = true;
			}
		}


		/*
		 *	如果查找双向的关系，找出都有谁添加了 $idUser 为好友
	 	 */
		if ( $biDirection )
		{
			$user_be_friend_ids		= JWFriend::GetBeFriendIds	($idUser, 9999);
		
			if ( !empty($user_be_friend_ids) ) {
				foreach ( $user_be_friend_ids as $user_be_friend_id ) {
					$user_friend_rows[$user_be_friend_id][$idUser] = true;
				}
			}
		}

		$return_friend_relation = array();

		foreach ( $idFriends as $friend_id )
		{
			// 检查正向朋友关系
			if ( isset($user_friend_rows[$idUser][$friend_id]) )
				$return_friend_relation[$idUser][$friend_id] = true;
			else
				$return_friend_relation[$idUser][$friend_id] = false;


			// 检查反向朋友关系
			if ( $biDirection )
			{
				if ( isset($user_friend_rows[$friend_id][$idUser]) )
					$return_friend_relation[$friend_id][$idUser] = true;
				else
					$return_friend_relation[$friend_id][$idUser] = false;
			}
			
		}

		return $return_friend_relation;
	}


	/**
	 * Is idFriend is idUser's friend?
	 *
	 */
	static function IsFriend($idUser, $idFriend)
	{
		$is_friend_rows = self::IsFriends($idUser, array($idFriend) );

		return $is_friend_rows[$idUser][$idFriend];
	}


	/**
	 * 	Get ids of whom is $idUser's friend.
	 *	@return array	array of friend id list
	 */
	static function GetFriendIds($idUser, $numMax=40, $start=0)
	{
		$idUser = intval($idUser);
		$numMax = intval($numMax);
		$start  = intval($start);

		if ( 0==$idUser || 0==$numMax )
			throw new JWException('not int');

		$sql = <<<_SQL_
SELECT	idFriend
FROM	Friend
WHERE	idUser=$idUser
		AND idFriend IS NOT NULL
LIMIT	$start,$numMax
_SQL_;

		$arr_result = JWDB::GetQueryResult($sql, true);

		if ( empty($arr_result) )
		{
			return null;
		}

		$arr_friend_id = array();
		foreach ( $arr_result as $row )
			array_push($arr_friend_id, $row['idFriend']);

		return $arr_friend_id;
	}


	/**
	 * 	Get ids of whom is friend with $idUser
	 *	@return array	array of be-friend id list
	 */
	static function GetBeFriendIds($idFriend, $numMax=40, $start=0)
	{
		$idFriend 	= intval($idFriend);
		$numMax 	= intval($numMax);
		$start  	= intval($start);

		if ( 0==$idFriend || 0==$numMax )
			throw new JWException('not int');

		$sql = <<<_SQL_
SELECT	idUser
FROM	Friend
WHERE	idFriend=$idFriend
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
	 *	idUser 删除好友 idFriend
	 * @param	int	idFriend
	 * @param	int	idUser
	 * @return 
			true: 成功 
			false: 失败
	 */
	static public function Destroy($idUser, $idFriend)
	{
		if ( !is_int($idUser) )
			$idUser 	= intval($idUser);

		if ( !is_int($idFriend) )
			$idFriend 	= intval($idFriend);


		if ( !is_int($idFriend) || !is_int($idUser ) )
			throw new JWException("id not int");

		$sql = <<<_SQL_
DELETE FROM	Friend
WHERE 		idUser=$idUser
			AND idFriend=$idFriend
_SQL_;

		try
		{
			$result = JWDB::Execute($sql) ;
		}
		catch(Exception $e)
		{
			JWLog::Instance()->Log(LOG_ERR, $e );
			return false;
		}

		return true;
	}


	/*
	 *	添加 idFriend 为 idUser 的好友
	 * @param	int	idFriend
	 * @param	int	idUser
	 * @return 
			true: 成功 
			false: 失败
	 */
	static public function Create($idUser, $idFriend)
	{
		$idUser 	= intval($idUser);
		$idFriend 	= intval($idFriend);

		if ( 0>=$idFriend || 0>=$idUser )
			throw new JWException("id not int");

		$sql = <<<_SQL_
INSERT INTO	Friend
SET 		idUser			= $idUser
			, idFriend		= $idFriend
			, timeCreate	= NOW()
_SQL_;

		try
		{
			$result = JWDB::Execute($sql) ;
		}
		catch(Exception $e)
		{
			JWLog::Instance()->Log(LOG_ERR, $e );
			return false;
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
FROM	Friend
WHERE	idUser=$idUser
		AND idFriend IS NOT NULL
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}



}
?>
