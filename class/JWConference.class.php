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


	static public $smsAlias = array(
			'9911456' => '991188169999',
			'9318456' => '931888169999',
			'9318456' => '931888169999',
			'99318456' =>'000000009999',
			'9501456' => '000000009999',
			);
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
	 * Get Conference ById
	 */
	static public function GetDbRowById($idConference){
		$idConference = JWDB::CheckInt( $idConference );
		$sql = <<<_SQL_
SELECT * FROM Conference
	WHERE id = $idConference
_SQL_;

		$row = JWDB::GetQueryResult( $sql, false );

		return $row;
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
	 * Get Conference Setting By Number
	 */
	static public function GetDbRowFromNumber($number){
		$number = intval( $number );
		if( $number <=  0 ) 
			return array();

		$sql = <<<_SQL_
SELECT * FROM Conference
	WHERE
		`number` = number
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

	/**
	 * Update Row
	 */
	static public function UpdateRow( $idConference, $updatedRow = array() ){
		$idConference = JWDB::CheckInt( $idConference );
		return JWDB::UpdateTableRow( 'Conference' , $idConference, $updatedRow );
	}

	/**
	 * GetConference from serverAddress
	 */
	static public function GetDbRowFromServerAddress( $serverAddress ) {
		if( isset( self::$smsAlias[ $serverAddress ] ) )
			$serverAddress = self::$smsAlias[ $serverAddress ];

		$conference = null;
		if( preg_match("/[0-9]{8}(99|1)(\d+)/", $serverAddress, $matches ) ) {
			if( $matches[1] == 1 ) {
				$conference = self::GetDbRowFromNumber( $matches[2] );		
			}else{
				$conference = self::GetDbRowFromUser( $matches[2] );
			}
		}

		return $conference;
	}
}
require_once( '../jiwai.inc.php' );

JWConference::UpdateRow( 5, array(
				'msgRegister' => '欢迎您参与《午夜心语》节目，本短信服务不收任何信息费，正常通信费除外，为实现您的个性化交流，请把您的昵称作为短信内容直接回复。',
				'msgUpdateStatus' => '《午夜心语》谢谢您的参与！您发送的短信即将播出，请密切关注。',
			));
?>
