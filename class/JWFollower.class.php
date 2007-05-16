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

	const	DEFAULT_FOLLOWER_MAX	= 20;
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
		$idUser 		= intval($idUser);
		$idFollower 	= intval($idFollower);

		if ( (0>=$idUser) || (0>=$idFollower) )
			throw new JWException('must int');

		return JWDB::ExistTableRow('Follower', array('idUser'=>$idUser,'idFollower'=>$idFollower));
	}


	/**
	 * 	Get follower list
	 *	@return array	array of follower id list
	 */
	static function GetFollower($idUser, $numMax=JWFollower::DEFAULT_FOLLOWER_MAX)
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
LIMIT	$numMax
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
		$idUser 	= intval($idUser);
		$idFollower = intval($idFollower);

		if ( (0>=$idFollower) || (0>=$idUser) )
			throw new JWException("id not int");

		$sql = <<<_SQL_
DELETE FROM	Follower
WHERE 		idUser=$idUser
			AND idFollower=$idFollower
_SQL_;

		try {
			$result = JWDB::Execute($sql) ;
		} catch(Exception $e) {
			JWLog::Instance()->Log(LOG_ERR, $e );
			return false;
		}
		return true;
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
		$idUser = intval($idUser);
		$idFollower = intval($idFollower);

		if ( 0>=$idUser || 0>=$idFollower )
			throw new JWException('not int');

		$sql = <<<_SQL_
INSERT INTO	Follower
SET 		idUser			= $idUser
			, idFollower	= $idFollower
_SQL_;

		try {
			$result = JWDB::Execute($sql) ;
		} catch(Exception $e) {
			JWLog::Instance()->Log(LOG_ERR, $e->getTraceAsString() );
			return false;
		}
		return true;
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
