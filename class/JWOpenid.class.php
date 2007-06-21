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
	static public function Destroy($urlOpenid)
	{
		$urlOpenid 	= JWOpenid::GetCoreUrl($urlOpenid);

		$sql = <<<_SQL_
DELETE FROM	Openid
WHERE 		urlOpenid='$urlOpenid'
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
	 *	@param	string	$urlOpenid	openid
	 *	@return	int		$idUser		null 代表没有这个 openid
	 */
	static public function GetUserIdFromOpenid($urlOpenid)
	{
		$core_url = JWOpenid::GetCoreUrl($urlOpenid);
		$row = JWDB::GetTableRow('Openid', array('urlOpenid'=>$core_url));

		if ( empty($row) )
			return null;

		return $row['idUser'];
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
		if ( !preg_match('#^http://',$url) )
			$url = 'http://' . $url;

		return $url;
	}

}
?>
