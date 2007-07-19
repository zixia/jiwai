<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Status_Quarantine Class
 */
class JWStatusQuarantine {
	/**
	 * Instance of this singleton
	 *
	 * @var JWStatusQuarantine
	 */
	static private $msInstance = null;

	const	DEFAULT_STATUS_NUM	= 20;

	/**
	 * const quarantine type
	 */
	const DEAL_NONE = 1;
	const DEAL_ALLOWED = 2;
	const DEAL_DELETED = 3;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWStatusQuarantine
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

	/*
	 *	@param	int	$time	unixtime
	 */
	static public function Create( $idUser, $status=null, $device='web',$time=null,$isSignature='N')
	{
		$status = preg_replace('[\r\n]',' ',$status);

		$time = intval($time);

		if ( 0>=$time )
			$time = time();
		
		$statusPost = JWRobotLingo::ConvertCorner($status);
		$reply_info = JWStatus::GetReplyInfo($statusPost);

		if ( empty($reply_info) )
		{ 
			$reply_status_id	= null;
			$reply_user_id		= null;
		}
		else
		{
			$status = $statusPost;
			$reply_status_id	= $reply_info['status_id'];
			$reply_user_id		= $reply_info['user_id'];
		}

		$user_db_row = JWUser::GetUserDbRowById($idUser);

		$picture_id = $user_db_row['idPicture'];

		return JWDB::SaveTableRow('Status_Quarantine',
							array(	 'idUser'	=> $idUser
									,'status'	=> $status
									,'device'	=> $device
									,'timeCreate'	=> date('Y-m-d H:i:s', $time)
									,'idStatusReplyTo'	=> $reply_status_id
									,'idUserReplyTo'	=> $reply_user_id
									,'idPicture'		=> $picture_id
									,'isSignature'		=> $isSignature
							)
						);
	}

	/**
	 * @param $dealStatus, int
	 * @return mixed
	 */
	static public function GetStatusQuarantineNum($dealStatus=JWStatusQuarantine::DEAL_NONE){

		switch($dealStatus){
			case JWStatusQuarantine::DEAL_NONE:
				$dealCondition = "dealStatus = 'NONE'";
			break;
			case JWStatusQuarantine::DEAL_ALLOWED:
				$dealCondition = "dealStatus = 'ALLOWED'";
			break;
			case JWStatusQuarantine::DEAL_DELETED:
				$dealCondition = "dealStatus = 'DELETED'";
			break;
			default:
				$dealCondition = "dealStatus = 'NONE'";
		}

		$sql = <<<SQL
SELECT COUNT(1) AS count FROM Status_Quarantine
	WHERE $dealCondition
SQL;
		$result = JWDB::GetQueryResult( $sql, false );
		if( !empty($result) ){
			return $result['count'];
		}
		return 0;
	}
	
	/**
	 * @param $dealStatus, int
	 * @param $limit, int
	 * @param $offset, int
	 * @return mixed
	 */
	static public function GetStatusQuarantine($dealStatus=JWStatusQuarantine::DEAL_NONE, $limit=20,$offset=0){

		switch($dealStatus){
			case JWStatusQuarantine::DEAL_NONE:
				$dealCondition = "dealStatus = 'NONE'";
			break;
			case JWStatusQuarantine::DEAL_ALLOWED:
				$dealCondition = "dealStatus = 'ALLOWED'";
			break;
			case JWStatusQuarantine::DEAL_DELETED:
				$dealCondition = "dealStatus = 'DELETED'";
			break;
			default:
				$dealCondition = "dealStatus = 'NONE'";
		}

		$sql = <<<SQL
SELECT * FROM Status_Quarantine
	WHERE $dealCondition
	ORDER BY id ASC
	LIMIT $offset , $limit
SQL;
		$result = JWDB::GetQueryResult( $sql, true );
		return $result;
	}

	/**
	 * @param $dealStatus, int
	 * @param $limit, int
	 * @param $offset, int
	 * @return mixed
	 */
	static public function GetStatusQuarantineFromUser($idUser=0, $timeSince=null){

		if( ! $idUser )
			return array();
		
		$timeCondition = null;
		if( $timeSince ) 
			$timeCondition = " AND timeCreate > '$timeSince' ";

		$sql = <<<SQL
SELECT * FROM Status_Quarantine
	WHERE idUser = $idUser $timeCondition
	ORDER BY id DESC
SQL;
		$result = JWDB::GetQueryResult( $sql, true );
		return $result;
	}

	/*
	 *	根据 idStatus 获取 Row 的详细信息
	 *	@param	array	idStatuses
	 * 	@return	array	以 idStatus 为 key 的 status row
	 * 
	 */
	static public function GetStatusDbRowsByIds ($idStatuses)
	{
		if ( empty($idStatuses) )
			return array();

		if ( !is_array($idStatuses) )
			throw new JWException('must array');

		$idStatuses = array_unique($idStatuses);

		$condition_in = JWDB::GetInConditionFromArray($idStatuses);

		$sql = <<<_SQL_
SELECT
		id as idStatus
		, idUser
		, status
		, UNIX_TIMESTAMP(Status_Quarantine.timeCreate) AS timeCreate
		, device
		, idUserReplyTo
		, idStatusReplyTo
		, idPicture
		, isSignature
FROM	Status_Quarantine
WHERE	Status_Quarantine.id IN ($condition_in)
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);


		if ( empty($rows) ){
			$status_map = array();
		} else {
			foreach ( $rows as $row ) {
				$status_map[$row['idStatus']] = $row;
			}
		}

		return $status_map;
	}

	static public function GetStatusDbRowById ($idStatus)
	{
		$status_db_rows = self::GetStatusDbRowsByIds(array($idStatus));

		if ( empty($status_db_rows) )
			return array();

		return $status_db_rows[$idStatus];
	}

	/**
	 * @param $ids
	 */
	static public function SetDealStatusByIds($idStatuses, $dealStatus=JWStatusQuarantine::DEAL_ALLOWED){
		settype($idStatuses, 'array');
		if( empty( $idStatuses ) )
			return true;
		$idStatusesString = implode( ',', $idStatuses );
		
		switch($dealStatus){
			case JWStatusQuarantine::DEAL_ALLOWED:
				$dealStatusString = 'ALLOWED';
			break;
			case JWStatusQuarantine::DEAL_DELETED:
				$dealStatusString = 'DELETED';
			break;
			default:
				return true;
		}

		$sql = <<< __SQL__
UPDATE Status_Quarantine 
	SET dealStatus = '$dealStatusString' 
	WHERE id IN ( $idStatusesString )
__SQL__;

		JWDB::Execute( $sql );

	}

	/**
	 * @param $ids
	 */
	static public function DestroyByIds($idStatuses){
		settype($idStatuses, 'array');
		foreach( $idStatuses as $id ){
			self::DestroyById( $id );
		}
	}

	/**
	 * @param	int
	 * @return	bool
	 */
	static public function DestroyById ($idStatus, $updateFlag=true)
	{
		$idStatus = JWDB::CheckInt( $idStatus );
		return JWDB::DelTableRow('Status_Quarantine', array (	'id'	=> intval($idStatus) ));
	}

	/**
	 * @param $ids
	 */
	static public function DeleteByIds($idStatuses){
		settype($idStatuses, 'array');
		self::SetDealStatusByIds( $idStatuses, JWStatusQuarantine::DEAL_DELETED);
	}

	/**
	 * @param	int
	 * @return	bool
	 */
	static public function DeleteById ($idStatus)
	{
		$idStatus = JWDB::CheckInt( $idStatus );
		self::SetDealStatusByIds( $idStatus, JWStatusQuarantine::DEAL_DELETED);
	}

	/**
	 * @param $ids
	 */
	static public function AllowByIds($idStatuses){
		settype($idStatuses, 'array');
		
		self::SetDealStatusByIds( $idStatuses, JWStatusQuarantine::DEAL_ALLOWED);

		foreach( $idStatuses as $id ){
			self::AllowById( $id , false);
		}
	}

	/**
	 * @param string allow
	 */
	static public function AllowById($idStatus , $updateFlag=true){

		$statusRow = self::GetStatusDbRowById( $idStatus );

		if( empty( $statusRow ) )
			return true;
					
		$createFlag = JWStatus::Create( $statusRow );
		if( $createFlag ) {
			//	self::DestroyById( $statusRow['idStatus'] );
		}else{
			return false;
		}

		if( true===$updateFlag ) {
			self::SetDealStatusByIds( $idStatus, JWStatusQuarantine::DEAL_ALLOWED );
		}
			
		/** Nudge Friends */

		if( $statusRow['idUserReplyTo'] ){
			$follow_ids = array( $statusRow['idUserReplyTo'] );
		}else{
			$follow_ids = JWFollower::GetFollowerIds( $statusRow['idUser'] );
		}

		if( !empty( $follow_ids ) ) {
		$userInfo = JWUser::GetUserInfo( $statusRow['idUser'] );
			$message = $userInfo['nameScreen'].': '.$statusRow['status'];
			JWNudge::NudgeUserIds( $follow_ids, $message ) ;
		}

		return true;	
	}
}
?>
