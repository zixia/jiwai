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
	 * Is idFriend is idUser's friend?
	 *
	 */
	static function IsFriend($idUser, $idFriend)
	{
		if ( !is_int($idUser) )
			$idUser 	= intval($idUser);

		if ( !is_int($idFriend) )
			$idFriend 	= intval($idFriend);

		if ( 0===$idUser || 0===$idFriend )
			throw new JWException('must int');

		return JWDB::ExistTableRow('Friend', array('idUser'=>$idUser,'idFriend'=>$idFriend));
	}


	/**
	 * Get friend list
	 *	@return array	array of friend id list
	 */
	static function GetFriend($idUser, $numMax=40)
	{
		$idUser = intval($idUser);
		$numMax = intval($numMax);

		if ( 0==$idUser || 0==$numMax )
			throw new JWException('not int');

		$sql = <<<_SQL_
SELECT	idFriend
FROM	Friend
WHERE	idUser=$idUser
LIMIT	$numMax
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
		if ( !is_int($idFriend) || !is_int($idUser ) )
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
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}



}
?>
