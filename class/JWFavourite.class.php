<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Favourite Class
 */
class JWFavourite {
	/**
	 * Instance of this singleton
	 *
	 * @var JWFavourite
	 */
	static private $msInstance;

	const	DEFAULT_FAVORITE_MAX	= 20;
	/**
	 * Instance of this singleton class
	 *
	 * @return JWFavourite
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
	 * Is idStatus is idUser's favourite?
	 *
	 */
	static function IsFavourite($idUser, $idStatus)
	{
		$idUser 		= intval($idUser);
		$idStatus	 	= intval($idStatus);

		if ( (0>=$idUser) || (0>=$idStatus) )
			throw new JWException('must int');

		return JWDB::ExistTableRow('Favourite', array('idUser'=>$idUser,'idStatus'=>$idStatus));
	}

	/**
	 * 	Get favourite list
	 *	@return array	array of favourite idStatus list
	 */
	static function GetFavouriteData($idUser, $numMax=JWFavourite::DEFAULT_FAVORITE_MAX, $start=0)
	{
		$idUser = JWDB::CheckInt($idUser);
		$numMax = JWDB::CheckInt($numMax);
		$start = intval($start);

		if ( 0>=$idUser || 0>=$numMax || 0>$start )
			throw new JWException('not int');

		$sql = <<<_SQL_
SELECT	id, idStatus
FROM	Favourite
WHERE	idUser=$idUser
ORDER BY id DESC
LIMIT	$start,$numMax
_SQL_;

		$arr_result = JWDB::GetQueryResult($sql, true);

		if ( empty($arr_result) )
		{
			return array();
		}

		$arr_status_id = array();
		$arr_favourite_id = array();
		foreach ( $arr_result as $row ) {
			array_push($arr_status_id, $row['idStatus']);
			array_push($arr_favourite_id, $row['id']);
		}

		return array(
			'status_ids' => $arr_status_id,
			'favourite_ids' => $arr_favourite_id,
		);

		return $arr_status_id;
	}

	/**
	 * 	Get favourite list
	 *	@return array	array of favourite idStatus list
	 */
	static function GetFavourite($idUser, $numMax=JWFavourite::DEFAULT_FAVORITE_MAX, $start=0)
	{
		$arr_favourite_data = self::GetFavouriteData($idUser, $numMax, $start);
		if( empty( $arr_favourite_data ) )
			return array();

		return $arr_favourite_data['status_ids'];
	}

	/*
	 *	取消 idUser 的 favourite idStatus
	 * @param	int	idStatus
	 * @param	int	idUser
	 * @return 
			true: 成功 
			false: 失败
	 */
	static public function Destroy($idUser, $idStatus)
	{
		$idUser 	= intval($idUser);
		$idStatus = intval($idStatus);

		if ( (0>=$idStatus) || (0>=$idUser) )
			throw new JWException("id not int");

		$sql = <<<_SQL_
DELETE FROM	Favourite
WHERE 		idUser=$idUser
			AND idStatus=$idStatus
_SQL_;

		return JWDB::DelTableRow('Favourite', array(	 'idUser'	=> $idUser
														,'idStatus'	=> $idStatus
													)
								);
	}


	/*
	 *	添加 idStatus 为 idUser 收藏
	 * @param	int	idStatus
	 * @param	int	idUser
	 * @return 
			true: 成功 
			false: 失败
	 */
	static public function Create($idUser, $idStatus)
	{
		$idUser 	= JWDB::CheckInt($idUser);
		$idStatus 	= JWDB::CheckInt($idStatus);

		return JWDB::SaveTableRow('Favourite',	array(	 'idUser'	=> $idUser
												,'idStatus'	=> $idStatus
											)
							);
	}

	/** 
	 * return if favorites
	 */
	static public function IsFavourited($user_id, $status_ids=array())
	{
		if (empty($status_ids) )
			return array();

		$rtn = array();
		foreach ($status_ids as $id)
		{
			$rtn[$id] = false;
		}

		if ( null == $user_id )
			return $rtn;

		$id_string = implode(',', $status_ids);

		$user_id = JWDB::CheckInt($user_id);

		$sql = <<<_SQL_
SELECT idStatus
FROM Favourite
WHERE 
	idUser=$user_id
	AND idStatus IN ($id_string)
_SQL_;
	
		$rows = JWDB::GetQueryResult($sql, true);
		if ( empty($rows) )
			return $rtn;
		
		foreach ( $rows as $one )
		{
			$rtn[$one['idStatus']] = true;
		}

		return $rtn;
	}

	/*
	 *	@param	int		$idUser
	 *	@return	int		$favouriteNum for $idUser
	 */
	static public function GetFavouriteNum($idUser)
	{
		$idUser = intval($idUser);

		if ( !is_int($idUser) )
			throw new JWException('must be int');

		$sql = <<<_SQL_
SELECT	COUNT(*) as num
FROM	Favourite
WHERE	idUser=$idUser
		AND idStatus IS NOT NULL
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}
}
?>
