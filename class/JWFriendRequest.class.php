<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de FriendRequest Class
 */
class JWFriendRequest {
	/**
	 * Instance of this singleton
	 *
	 * @var JWFriendRequest
	 */
	static private $msInstance;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWFriendRequest
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


	static function IsFriendRequestExist($idUser, $idFriend)
	{
		$idUser 	= JWDB::CheckInt($idUser);
		$idFriend 	= JWDB::CheckInt($idFriend);

		return JWDB::ExistTableRow('FriendRequest', array ( 'idUser'=>$idUser, 'idFriend'=>$idFriend) );
	}

	/**
	 * 	Get ids of whom is $idUser's friend.
	 *	@return array	array of friend id list
	 */
	static function GetFriendRequestIds($idUser, $numMax=9999, $start=0)
	{
		$idUser = intval($idUser);
		$numMax = intval($numMax);
		$start  = intval($start);

		if ( 0==$idUser || 0==$numMax )
			throw new JWException('not int');

		$sql = <<<_SQL_
SELECT	idFriend
FROM	FriendRequest
WHERE	idUser=$idUser
		AND idFriend IS NOT NULL
LIMIT	$start,$numMax
_SQL_;

		$arr_result = JWDB::GetQueryResult($sql, true);

		if ( empty($arr_result) )
		{
			return array();
		}

		$arr_friend_id = array();
		foreach ( $arr_result as $row )
			array_push($arr_friend_id, $row['idFriend']);

		return $arr_friend_id;
	}



	/*
	 *idUser 删除好友 idFriendRequest
	 * @param	int	idFriendRequest
	 * @param	int	idUser
	 * @return 
			true: 成功 
			false: 失败
	 */
	static public function Destroy($idUser, $idFriend)
	{
		$idUser		= JWDB::CheckInt($idUser);
		$idFriend	= JWDB::CheckInt($idFriend);

		$sql = <<<_SQL_
DELETE FROM	FriendRequest
WHERE 		idUser=$idUser
			AND idFriendRequest=$idFriend
_SQL_;

		try
		{
			$result = JWDB::Execute($sql) ;
		}
		catch(Exception $e)
		{
			JWLog::Instance()->Log(LOG_ERR, $e->getMessage() );
			return false;
		}

		return true;
	}


	/*
	 *	为 idFriend 请求 idUser 添加好友
	 * @param	int	idFriend	请求者
	 * @param	int	idUser		被请求者
	 * @return 
			true: 成功 
			false: 失败
	 */
	static public function Create($idUser, $idFriend)
	{
		$idUser 			= JWDB::CheckInt($idUser);
		$idFriend= JWDB::CheckInt($idFriend);

		$sql = <<<_SQL_
INSERT INTO	FriendRequest
SET 		idUser		= $idUser
			, idFriend	= $idFriend
			, timeCreate	= NOW()
_SQL_;

		try
		{
			$result = JWDB::Execute($sql) ;
		}
		catch(Exception $e)
		{
			JWLog::Instance()->Log(LOG_ERR, $e->getMessage() );
			return false;
		}

		return true;
	}

	/*
	 *	@param	int		$idUser
	 *	@return	int		$friendNum for $idUser
	 */
	static public function GetFriendRequestNum($idUser)
	{
		$idUser = JWDB::CheckInt($idUser);

		$sql = <<<_SQL_
SELECT	COUNT(*) as num
FROM	FriendRequest
WHERE	idUser=$idUser
		AND idFriendRequest IS NOT NULL
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}



}
?>
