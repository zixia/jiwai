<?php
/**
 * @package	JiWai.de
 * @copyright	AKA Inc.
 * @author  	shwdai@gmail.com
 */

/**
 * JiWai.de FollowerRequest Class
 *
 */
class JWFollowerRequest 
{
	/**
	 * Constructing method, save initial state
	 *
	 */
	private function __construct()
	{
	}



	/*
	 * 为 idUser 请求 idFollower 添加自己为好友
	 * @param	int	idFollower	请求者
	 * @param	int	idUser		被请求者
	 * @return 
			true: 成功 
			false: 失败
	 */
	static public function Create($idUser, $idFollower, $note='')
	{
		$idUser 	= JWDB::CheckInt($idUser);
		$idFollower	= JWDB::CheckInt($idFollower);

		return JWDB::SaveTableRow('FollowerRequest', array(
			'idUser' => $idUser,
			'idFollower' => $idFollower,
			'timeCreate' => JWDB::MysqlFuncion_Now(),
			'note' => $note,
		));
	}


	/*
	 * 删除 idUser 要求 idFollower 添加自己为好友的请求
	 * @param	int	idFollower
	 * @param	int	idUser
	 * @return 
			true: 成功 
			false: 失败
	 */
	static public function Destroy($idUser, $idFollower)
	{
		$idUser		= JWDB::CheckInt($idUser);
		$idFollower	= JWDB::CheckInt($idFollower);

		return JWDB::DelTableRow('FollowerRequest', array(
			'idUser' => $idUser,
			'idFollower' => $idFollower,
		));
	}

	static function GetTableRow($idUser, $idFollower) {
		return JWDB::GetTableRow('FollowerRequest', array(
			'idUser' => $idUser,
			'idFollower' => $idFollower,
	     	));
	}

	static function IsExist($idUser, $idFollower)
	{
		$idUser 	= JWDB::CheckInt($idUser);
		$idFollower 	= JWDB::CheckInt($idFollower);

		return JWDB::ExistTableRow('FollowerRequest', array (
			'idUser' => $idUser,
			'idFollower' => $idFollower,
		));
	}


	/**
	 * 	$idFollower 发出的 关注请求
	 *	@return array	array of follow id list
	 */
	static function GetOutRequestIds($idFollower, $numMax=9999, $start=0)
	{
		$idFollower = JWDB::CheckInt($idFollower);
		$numMax = JWDB::CheckInt($numMax);


		$sql = <<<_SQL_
SELECT	idUser,note,timeCreate
	FROM	FollowerRequest
	WHERE	
		idFollower=$idFollower
		AND idUser IS NOT NULL
	LIMIT	$start,$numMax
_SQL_;

		$arr_result = JWDB::GetQueryResult($sql, true);

		if ( empty($arr_result) )
			return array();

		$arr_follow_id = array();
		foreach ( $arr_result as $row )
			$arr_follow_id[$row['idUser']] = $row ;

		return $arr_follow_id;
	}

	/**
	 * 	收到的 关注 idUser 的请求
	 *	@return array	array of follow id list
	 */
	static function GetInRequestIds($idUser, $numMax=9999, $start=0)
	{
		$idUser = JWDB::CheckInt($idUser);
		$numMax = JWDB::CheckInt($numMax);


		$sql = <<<_SQL_
SELECT	idFollower,note,timeCreate
	FROM	FollowerRequest
	WHERE	
		idUser=$idUser
		AND idFollower IS NOT NULL
	LIMIT	$start,$numMax
_SQL_;

		$arr_result = JWDB::GetQueryResult($sql, true);

		if ( empty($arr_result) )
			return array();

		$arr_follow_id = array();
		foreach ( $arr_result as $row )
			$arr_follow_id[$row['idFollower']] = $row ;

		return $arr_follow_id;
	}

	/*
	 *	@param	int	$idFollower
	 *	@return	int	$num	$idFollower 一共发出了多少关注请求
	 */
	static public function GetOutRequestNum($idFollower)
	{
		$idUser = JWDB::CheckInt($idFollower);

		$sql = <<<_SQL_
SELECT	COUNT(*) as num
	FROM	FollowerRequest
	WHERE	
		idFollower=$idFollower
		AND idFollower IS NOT NULL
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}


	/*
	 *	@param	int	$idUser
	 *	@return	int	$num 一共收到多少关注请求
	 */
	static public function GetInRequestNum($idUser)
	{
		$idFollower = JWDB::CheckInt($idUser);

		$sql = <<<_SQL_
SELECT	COUNT(*) as num
FROM	FollowerRequest
WHERE	idUser=$idUser
		AND idFollower IS NOT NULL
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}

}
?>
