<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de FriendRequest Class
 *
 */
class JWFriendRequest 
{
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



	/*
	 *	为 idUser 请求 idFriend 添加自己为好友
	 * @param	int	idFriend	请求者
	 * @param	int	idUser		被请求者
	 * @return 
			true: 成功 
			false: 失败
	 */
	static public function Create($idUser, $idFriend, $note='')
	{
		$idUser 	= JWDB::CheckInt($idUser);
		$idFriend	= JWDB::CheckInt($idFriend);

		return JWDB::SaveTableRow('FriendRequest', array(	 'idUser'	=> $idUser
							,'idFriend'	=> $idFriend
							,'timeCreate'	=> JWDB::MysqlFuncion_Now()
							,'note'	=> $note
					)
				);
	}


	/*
	 *	删除 idUser 要求 idFriend 添加自己为好友的请求
	 * @param	int	idFriend
	 * @param	int	idUser
	 * @return 
			true: 成功 
			false: 失败
	 */
	static public function Destroy($idUser, $idFriend)
	{
		$idUser		= JWDB::CheckInt($idUser);
		$idFriend	= JWDB::CheckInt($idFriend);

		return JWDB::DelTableRow('FriendRequest', array(	 'idUser'	=> $idUser
															,'idFriend'	=> $idFriend
														)
								);
	}

    static function GetTableRow($idUser, $idFriend) {
        return JWDB::GetTableRow('FriendRequest', array( 'idUser'=>$idUser, 'idFriend'=>$idFriend ) );
    }

	static function IsExist($idUser, $idFriend)
	{
		$idUser 	= JWDB::CheckInt($idUser);
		$idFriend 	= JWDB::CheckInt($idFriend);

		return JWDB::ExistTableRow('FriendRequest', array ( 'idUser'=>$idUser, 'idFriend'=>$idFriend) );
	}


	/**
	 * 	$idUser发出的 希望他人添加自己为好友的请求
	 *	@return array	array of friend id list
	 */
	static function GetFriendIds($idUser, $numMax=9999, $start=0)
	{
		$idUser = JWDB::CheckInt($idUser);
		$numMax = JWDB::CheckInt($numMax);


		$sql = <<<_SQL_
SELECT	idFriend,note,timeCreate
FROM	FriendRequest
WHERE	idUser=$idUser
		AND idFriend IS NOT NULL
LIMIT	$start,$numMax
_SQL_;

		$arr_result = JWDB::GetQueryResult($sql, true);

		if ( empty($arr_result) )
			return array();

		$arr_friend_id = array();
		foreach ( $arr_result as $row )
            $arr_friend_id[$row['idFriend']] = $row ;

		return $arr_friend_id;
	}

	/**
	 * 	$idUser发出的 希望他人添加自己为好友的请求
	 *	@return array	array of friend id list
	 */
	static function GetFollowingIds($idUser, $numMax=9999, $start=0)
	{
		$idUser = JWDB::CheckInt($idUser);
		$numMax = JWDB::CheckInt($numMax);


		$sql = <<<_SQL_
SELECT	idFriend,note,timeCreate
FROM	FriendRequest
WHERE	idUser=$idUser
		AND idFriend IS NOT NULL
LIMIT	$start,$numMax
_SQL_;

		$arr_result = JWDB::GetQueryResult($sql, true);

		if ( empty($arr_result) )
			return array();

		$arr_friend_id = array();
		foreach ( $arr_result as $row )
            $arr_friend_id[$row['idFriend']] = $row ;

		return $arr_friend_id;
	}



	/**
	 * 	$idFriend 收到的他人希望自己加他人为好友的请求
	 *	@return array	array of friend id list
	 */
	static function GetUserIds($idFriend, $numMax=9999, $start=0)
	{
		$idFriend 	= JWDB::CheckInt($idFriend);
		$numMax 	= JWDB::CheckInt($numMax);

		$sql = <<<_SQL_
SELECT	idUser,note,timeCreate
FROM	FriendRequest
WHERE	idFriend=$idFriend
		AND idUser IS NOT NULL
LIMIT	$start,$numMax
_SQL_;

		$arr_result = JWDB::GetQueryResult($sql, true);

		if ( empty($arr_result) )
			return array();

		$arr_friend_id = array();
		foreach ( $arr_result as $row )
            $arr_friend_id[$row['idUser']] = $row;

		return $arr_friend_id;
	}



	/*
	 *	@param	int		$idFriend
	 *	@return	int		$num	$idUser 一共发出了多少请他人添加自己为好友的未被处理请求
	 */
	static public function GetFriendNum($idUser)
	{
		$idUser = JWDB::CheckInt($idUser);

		$sql = <<<_SQL_
SELECT	COUNT(*) as num
FROM	FriendRequest
WHERE	idUser=$idUser
		AND idFriend IS NOT NULL
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}


	/*
	 *	@param	int		$idFriend
	 *	@return	int		$num	一共有多少人希望自己添加他为好友
	 */
	static public function GetUserNum($idFriend)
	{
		$idFriend = JWDB::CheckInt($idFriend);

		$sql = <<<_SQL_
SELECT	COUNT(*) as num
FROM	FriendRequest
WHERE	idFriend=$idFriend
		AND idUser IS NOT NULL
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}

}
?>
