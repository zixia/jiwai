<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Conference Class
 */
class JWConference {
	/**
	 * Instance of this singleton
	 *
	 * @var JWConference
	 */
	static private $instance__;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWConference
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

	/**
	 * Get User Conference Setting
	 */
	static public function GetDbRowFromUser($idUser){
		$idUser = JWDB::CheckInt( $idUser );
		$sql = <<<_SQL_
SELECT * FROM Conference
	WHERE
		idUser = $idUser
	LIMIT 1
_SQL_;

		$row = JWDB::GetQueryResult( $sql, false );

		return $row;
	}
	
	/**
	 * Create User Conference Setting
	 */
	static public function Create( $idUser, $friendOnly='Y', $deviceAllow='sms,im,web', $number=null, $time=null){
		if(is_numeric( $time ) ) 
			$time = date('Y-m-d H:i:s', $time );
		$timeCreate = ($time==null) ? date('Y-m-d H:i:s') : $time;

		return JWDB::SaveTableRow('Conference', array(
					'idUser' =>  $idUser,
					'friendOnly' => $friendOnly,
					'deviceAllow' => $deviceAllow,
					'number' => $number,
					'timeCreate' => $timeCreate,
					));
	}

	/**
	 * Update User Conference Setting
	 */
	static public function Update( $idConference, $friendOnly='Y', $deviceAllow='sms,im,web', $number=null){
		$idConference = JWDB::CheckInt( $idConference );
		return JWDB::UpdateTableRow( 'Conference' , $idConference, array(
						'friendOnly' => $friendOnly,
						'deviceAllow' => $deviceAllow,
						'number' => $number,
					));
	}
}
?>
