<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Openid Class
 */
class JWOpenid_TrustSite {
	/**
	 * Instance of this singleton
	 *
	 * @var JWOpenid_TrustSite
	 */
	static private $msInstance;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWOpenid_TrustSite
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

	static public function Create($idUser,$urlTrusted)
	{
		$idUser 	= JWDB::CheckInt($idUser);

		return JWDB::SaveTableRow('OpenidTrustSite', array(	 'idUser'	=> $idUser
															,'urlTrusted'	=> $urlTrusted
															,'timeCreate'	=> JWDB::MysqlFuncion_Now()
														)
									);
	}


	/*
	 * @return 
			true: 成功 
			false: 失败
	 */
	static public function Destroy($idTrustSite)
	{
		$idTrustSite = JWDB::CheckInt($idTrustSite);

		return JWDB::DelTableRow('OpenidTrustSite', array( 'id'=>$idTrustSite ));
	}

	static public function IsTrusted($idUser, $urlTrusted)
	{
		$idUser = JWDB::CheckInt($idUser);

		return JWDB::ExistTableRow('OpenidTrustSite', array('idUser'=>$idUser,'urlTrusted'=>$urlTrusted));
	}


	/*
	 *	根据 idOpenidTrustSites 获取 Row 的详细信息
	 *	@param	array	idOpenidTrustSites
	 * 	@return	array	以 idOpenidTrustSite 为 key 的 db row
	 * 
	 */
	static public function GetDbRowsByIds ($idOpenidTrustSites)
	{
		if ( empty($idOpenidTrustSites) )
			return array();

		if ( !is_array($idOpenidTrustSites) )
			throw new JWException('must array');

		$idOpenidTrustSites = array_unique($idOpenidTrustSites);

		$condition_in = JWDB::GetInConditionFromArray($idOpenidTrustSites);

		$sql = <<<_SQL_
SELECT
		 id
		,id as idOpenidTrustSite
		,idUser
		,urlTrusted
		,UNIX_TIMESTAMP(timeCreate) AS timeCreate
FROM	OpenidTrustSite
WHERE	id IN ($condition_in)
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);


		if ( empty($rows) ){
			$db_rows = array();
		} else {
			foreach ( $rows as $row ) {
				$db_rows[$row['idOpenidTrustSite']] = $row;
			}
		}

		return $db_rows;
	}

	static public function GetDbRowById ($idOpenidTrustSite)
	{
		$db_rows = self::GetDbRowsByIds(array($idOpenidTrustSite));

		if ( empty($db_rows) )
			return array();

		return $db_rows[$idOpenidTrustSite];
	}


	/**
	 * 	Get ids of whom is $idUser's trust sites
	 *	@return array	array of TrustSite id list
	 */
	static function GetIdsByUserId($idUser, $numMax=9999, $start=0)
	{
		$idUser = JWDB::CheckInt($idUser);
		$numMax = JWDB::CheckInt($numMax);

		$sql = <<<_SQL_
SELECT	id 
FROM	OpenidTrustSite
WHERE	idUser=$idUser
ORDER BY id DESC
LIMIT	$start,$numMax
_SQL_;

		$arr_result = JWDB::GetQueryResult($sql, true);

		if ( empty($arr_result) )
		{
			return array();
		}

		$arr_friend_id = array();
		foreach ( $arr_result as $row )
			array_push($arr_friend_id, $row['id']);

		return $arr_friend_id;
	}

	static public function IsUserOwnId($idUser, $idOpenidTrustSite)
	{
		$db_row = self::GetDbRowById($idOpenidTrustSite);

		return $db_row['idUser']==$idUser;
	}

}
?>
