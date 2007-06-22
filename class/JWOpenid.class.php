<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Openid Class
 */
class JWOpenid 
{
	/**
	 * Instance of this singleton
	 *
	 * @var JWOpenid
	 */
	static private $msInstance;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWOpenid
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

	static public function Create($urlOpenid,$idUser)
	{
		$idUser 	= JWDB::CheckInt($idUser);

		$urlOpenid 	= JWOpenid::GetCoreUrl($urlOpenid);

		$sql = <<<_SQL_
REPLACE	Openid
SET 	idUser			= $idUser
		, urlOpenid		= '$urlOpenid'
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
	 *	urlOpenid	
	 * @return 
			true: 成功 
			false: 失败
	 */
	static public function Destroy($idOpenid)
	{
		$idOpenid 	= JWDB::CheckInt($idOpenid);

		$sql = <<<_SQL_
DELETE FROM	Openid
WHERE 		id=$idOpenid
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
	 *	@return	int		$idOpenid null 代表没有这个 openid
	 */
	static public function GetIdByUserId($idUser)
	{
		$idUser = JWDB::CheckInt($idUser);

		$row = JWDB::GetTableRow('Openid', array('idUser'=>$idUser));

		if ( empty($row) )
			return null;

		return $row['id'];
	}


	/*
	 *	@param	string	$urlOpenid	openid
	 *	@return	int		$idOpenid	null 代表没有这个 openid
	 */
	static public function GetIdByUrl($urlOpenid)
	{
		$core_url = JWOpenid::GetCoreUrl($urlOpenid);
		$row = JWDB::GetTableRow('Openid', array('urlOpenid'=>$core_url));

		if ( empty($row) )
			return null;

		return $row['id'];
	}

	static public function IsPossibleOpenId($usernameOrEmail)
	{
		if ( preg_match('#\.#',$usernameOrEmail)			// 有 . 则可能是域名
				&& !preg_match('#@#',$usernameOrEmail) )	//	排除 @ ，代表 email
			return true;
	
		return false;
	}

	static public function GetCoreUrl($url)
	{
		$url = preg_replace('#^http://#','',$url);
		return $url;
	}

	static public function GetFullUrl($url)
	{
		if ( !preg_match('#^http://#i',$url) )
			$url = 'http://' . $url;

		return $url;
	}

	/*
	 *	根据 idOpenids 获取 Row 的详细信息
	 *	@param	array	idOpenids
	 * 	@return	array	以 idOpenid 为 key 的 db row
	 * 
	 */
	static public function GetDbRowsByIds ($idOpenids)
	{
		if ( empty($idOpenids) )
			return array();

		if ( !is_array($idOpenids) )
			throw new JWException('must array');

		$idOpenids = array_unique($idOpenids);

		$condition_in = JWDB::GetInConditionFromArray($idOpenids);

		$sql = <<<_SQL_
SELECT
		id as idOpenid
		, idUser
		, urlOpenid
		, UNIX_TIMESTAMP(timeCreate) AS timeCreate
FROM	Openid
WHERE	id IN ($condition_in)
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);


		if ( empty($rows) ){
			$openid_db_rows = array();
		} else {
			foreach ( $rows as $row ) {
				$openid_db_rows[$row['idOpenid']] = $row;
			}
		}

		return $openid_db_rows;
	}

	static public function GetDbRowById ($idOpenid)
	{
		$openid_db_rows = JWOpenid::GetDbRowsByIds(array($idOpenid));

		if ( empty($openid_db_rows) )
			return array();

		return $openid_db_rows[$idOpenid];
	}


	static public function IsUserOwnId($idUser, $idOpenid)
	{
		$db_row = JWOpenid::GetDbRowById($idOpenid);

		return $db_row['idUser']==$idUser;
	}
}
?>
