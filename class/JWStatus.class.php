<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Status Class
 */
class JWStatus {
	/**
	 * Instance of this singleton
	 *
	 * @var JWStatus
	 */
	static private $instance__;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWStatus
	 */
	static public function &instance()
	{
		if (!isset(self::$instance__)) {
			$class = __CLASS__;
			self::$instance__ = new $class;
		}
		return self::$instance__;
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
	}


	static public function update( $user_id, $status, $device='web' )
	{
		$db = JWDB::instance()->get_db();

		if ( $stmt = $db->prepare( "INSERT INTO Status (idUser,status,device) "
								. " values (?,?,?)" ) ){
			if ( $result = $stmt->bind_param("iss"
											, $user_id
											, $status
											, $device
								) ){
				if ( $stmt->execute() ){
					//JWDebug::trace($stmt->affected_rows);
					//JWDebug::trace($stmt->insert_id);
					$stmt->close();
					return true;
				}else{
					JWDebug::trace($db->error);
				}
			}
		}else{
			JWDebug::trace($db->error);
		}
		return false;
	}

	static public function get_status_list_timeline ()
	{
		$sql = <<<_SQL_
SELECT 
	Status.id as idStatus
	, Status.status
	, UNIX_TIMESTAMP(Status.timestamp) AS timestamp
	, Status.device
	, User.id as idUser
	, User.nameScreen
	, User.nameFull
	, User.photoInfo
FROM
	Status, User
WHERE
	User.photoInfo<>''
	AND Status.idUser=User.id
ORDER BY
	timestamp desc
LIMIT 100;
_SQL_;
		$aStatusList = JWDB::get_query_result($sql,true);
		return $aStatusList;

	}


	static public function get_status_list_network ($idUser)
	{
	}

	// idStatus, idUser, nameScreen, nameFull, photoUrl,status,timestamp,device
	static public function get_status_list_user ($idUser)
	{
		if ( !is_numeric($idUser) ){
			JWDebug::trace("idUser[$idUser] is not number");
			return null;
		}

		$sql = <<<_SQL_
SELECT 
	Status.id as idStatus
	, Status.status
	, UNIX_TIMESTAMP(Status.timestamp) AS timestamp
	, Status.device
	, User.id as idUser
	, User.nameScreen
	, User.nameFull
	, User.photoInfo
FROM
	Status, User
WHERE
	User.id=$idUser
	AND Status.idUser=User.id
ORDER BY
	timestamp desc
LIMIT 100;
_SQL_;
		$aStatusList = JWDB::get_query_result($sql,true);
		return $aStatusList;
	}


	static public function get_time_desc ($unixtime)
	{

		$duration = time() - $unixtime;
		if ( $duration > 2*86400 ){
			return strftime("%Y-%m-%d 周%a %H:%M",$unixtime);
		}else if ( $duration > 86400 ){
			return strftime("%Y-%m-%d %H:%M",$unixtime);
			//return "1 天前";
		}else if ( $duration > 3600 ){ // > 1 hour
			$duration = intval($duration/3600);
			return "$duration 小时前";
		}else if ( $duration > 60 ){ // > 1 min
			$duration = intval($duration/60);
			return "$duration 分钟前";
		}else{ // < 1 min
			if ( $duration > 30 ){
				return "半分钟前";
			}else if ( $duration > 20 ){
				return "20 秒前";
			}else if ( $duration > 10 ){
				return "10 秒前";
			}else if ( $duration > 5 ){
				return "5 秒前";
			}else{
				return "就在刚才";
			}
		}
	}

}
?>
