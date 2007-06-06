<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de LogUserAction Class
 */
class JWLogUserAction {
	/**
	 * Instance of this singleton
	 *
	 * @var JWLogUserAction
	 */
	static private $msInstance;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWLogUserAction
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

	static public function OnLogin($idUser)
	{
		$idUser = JWDB::CheckInt($idUser);

		$sql = <<<_SQL_
INSERT INTO	LogUserAction
SET			 action='login'
			,idUser	= $idUser
_SQL_;

		JWDB::Execute($sql);
	}

	static public function OnRememberLogin($idUser)
	{
		$idUser = JWDB::CheckInt($idUser);

		$sql = <<<_SQL_
INSERT INTO	LogUserAction
SET			 action='rememberlogin'
			,idUser	= $idUser
_SQL_;

		JWDB::Execute($sql);

	}


	static public function OnLogout($idUser)
	{
		$idUser = JWDB::CheckInt($idUser);

		$sql = <<<_SQL_
INSERT INTO	LogUserAction
SET			 action='logout'
			,idUser	= $idUser
_SQL_;

		JWDB::Execute($sql);

	}

	static public function OnRememberMe($idUser)
	{
		$idUser = JWDB::CheckInt($idUser);

		$sql = <<<_SQL_
INSERT INTO	LogUserAction
SET			 action='rememberme'
			,idUser	= $idUser
_SQL_;

		JWDB::Execute($sql);

	}


	static public function OnForgetMe($idUser)
	{
		$idUser = JWDB::CheckInt($idUser);

		$sql = <<<_SQL_
INSERT INTO	LogUserAction
SET			 action='forgetme'
			,idUser	= $idUser
_SQL_;

		JWDB::Execute($sql);

	}


}
?>
