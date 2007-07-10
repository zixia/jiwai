<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	shwdai@Jiwai.de
 * @version		$Id$
 */

/**
 * JiWai.de Status Class
 */
class JWStatusCopyForManager {
	/**
	 * Instance of this singleton
	 *
	 * @var JWStatus
	 */
	static private $msInstance = null;

	const	DEFAULT_STATUS_NUM	= 20;

	const	TREAT_NOT = 0;
	const	TREAT_ALLOW = 1;
	const	TREAT_DELETE = 2;
	const	TREAT_REPLACE = 3;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWStatus
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

	static public function UpdateToStatus()
	{
		$result = array();
		$result['countBegin'] = JWStatusCopyForManager::GetStatusNum();

		$timeBegin = microtime(true);
		$sql = <<<SQL
REPLACE INTO Status_CopyForManager 
	(
	 SELECT id,idUser,status,device,timeCreate,isSignature,'NOT' 
	 	FROM Status 
		WHERE id > 
			(
			 SELECT MAX(id) FROM Status_CopyForManager
			)
	)
SQL;
		JWDB::Execute( $sql );
		$timeEnd = microtime(true);

		$result['timeCost'] =  $timeEnd - $timeBegin;
		$result['countEnd'] = JWStatusCopyForManager::GetStatusNum();
		
		return $result;
	}

	static public function GetStatus($type=JWStatusCopyForManager::TREAT_NOT, $limit=JWStatusCopyForManager::DEFAULT_STATUS_NUM, $offset=0){
		switch($type){
			case JWStatusCopyForManager::TREAT_NOT:
				$typeString = 'NOT';
			break;
			case JWStatusCopyForManager::TREAT_ALLOW:
				$typeString = 'ALLOW';
			break;
			case JWStatusCopyForManager::TREAT_DELETE:
				$typeString = 'DELETE';
			break;
			case JWStatusCopyForManager::TREAT_REPLACE:
				$typeString = 'REPLACE';
			break;
			default:
				$typeString = 'NOT';
		}
		$sql = <<<SQL
SELECT * FROM Status_CopyForManager
	WHERE treatStatus='$typeString'
	ORDER BY id ASC
	LIMIT $offset , $limit
SQL;

		$result = JWDB::GetQueryResult( $sql , true );
		return $result;
	}

	static public function GetStatusNum(){
		$sql = "SELECT COUNT(1) AS count FROM Status_CopyForManager";
		$row = JWDB::GetQueryResult( $sql );
		return $row['count'];
	}
}
?>
