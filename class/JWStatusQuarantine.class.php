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
	 * @var JWStatus
	 */
	static private $msInstance = null;

	const	DEFAULT_STATUS_NUM	= 20;

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

	/*
	 *	@param	int	$time	unixtime
	 */
	static public function Create( $idUser, $status, $device='web',$time=null,$isSignature='N')
	{
		$db = JWDB::Instance()->GetDb();

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

		return JWDB_Cache::SaveTableRow('Status_Quarantine',
							array(	 'idUser'	=> $idUser
									,'status'	=> $status
									,'device'	=> $device
									,'timeCreate'	=> $time
									,'idStatusReplyTo'	=> $reply_status_id
									,'idUserReplyTo'	=> $reply_user_id
									,'idPicture'		=> $picture_id
									,'isSignature'		=> $isSignature
							)
						);
	}
	
	/**
	 * @param $limit, int
	 * @param $offset, int
	 * @return mixed
	 */
	static public function GetStatusQuarantine($limit=20,$offset=0){
		$sql = <<<SQL
SELECT * FROM Status_Quarantine
	ORDER BY id ASC
	LIMIT $offset , $limit
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

		echo $sql;

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
	static public function DestroyById ($idStatus)
	{
		$idStatus = JWDB::CheckInt( $idStatus );
		return JWDB::DelTableRow('Status_Quarantine', array (	'id'	=> intval($idStatus) ));
	}

	/**
	 * @param $ids
	 */
	static public function AllowByIds($idStatuses){
		settype($idStatuses, 'array');
		foreach( $idStatuses as $id ){
			self::AllowById( $id );
		}
	}

	/**
	 * @param string allow
	 */
	static public function AllowById($idStatus){

		$statusRow = self::GetStatusDbRowById( $idStatus );

		if( empty( $statusRow ) )
			return true;
					
		$createFlag = JWStatus::Create( $statusRow );
		if( $createFlag ) {
			self::DestroyById( $statusRow['idStatus'] );
		}else{
			return false;
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
