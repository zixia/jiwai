<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de StatusQuarantine Class
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
	static public function Create( $idUser, $status=null, $device='web', $isSignature='N', $options= array() ) {
		$idUser = JWDB::CheckInt( $idUser );
		$timeCreate = $options['timeCreate'];
		$idStatusReplyTo = $options['idStatusReplyTo'];
		$idUserReplyTo = $options['idUserReplyTo'];
		$idConference = $options['idConference'];

		return JWDB::SaveTableRow('StatusQuarantine', 
						array(
							'idUser' => $idUser,
							'status' => $status,
							'device' => $device,
							'timeCreate' => $timeCreate,
							'idStatusReplyTo' => $idStatusReplyTo,
							'idUserReplyTo'	=> $idUserReplyTo,
							'isSignature' => $isSignature,
						));
	}

	/**
	 * @param $dealStatus, int
	 * @return mixed
	 */
	static public function GetStatusQuarantineNum($dealStatus=JWStatusQuarantine::DEAL_NONE){

		$dealCondition = self::GetDealCondition( $dealStatus );

		$sql = <<<SQL
SELECT COUNT(1) AS count FROM StatusQuarantine
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

		$dealCondition = self::GetDealCondition( $dealStatus );

		$sql = <<<SQL
SELECT * FROM StatusQuarantine
	WHERE $dealCondition
	ORDER BY id DESC
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
	static public function GetDbIdsFromUser($idUser=0, $dealStatus=JWStatusQuarantine::DEAL_ALLOWED, $timeSince=null){

		if( ! $idUser )
			return array();
		
		$dealCondition = self::GetDealCondition( $dealStatus );
		
		$timeCondition = null;
		if( $timeSince ) 
			$timeCondition = " AND timeCreate > '$timeSince' ";

		$sql = <<<SQL
SELECT  id
	FROM StatusQuarantine
	WHERE idUser = $idUser AND $dealCondition
 	$timeCondition
	ORDER BY id DESC
SQL;

		$result = JWDB::GetQueryResult( $sql, true );
		
		$returnedArray = array();	
		if( !empty($result) ){
			foreach($result as $r){
				$returnedArray[] = $r['id'];
			}
		}

		return $returnedArray;
	}

	/*
	 *	根据 idStatus 获取 Row 的详细信息
	 *	@param	array	idStatuses
	 * 	@return	array	以 idStatus 为 key 的 status row
	 * 
	 */
	static public function GetDbRowsByIds ($idStatuses)
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
		, timeCreate
		, device
		, idUserReplyTo
		, idStatusReplyTo
		, idPicture
		, isSignature
FROM	StatusQuarantine
WHERE	StatusQuarantine.id IN ($condition_in)
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

	static public function GetDbRowById ($idStatus)
	{
		$status_db_rows = self::GetDbRowsByIds(array($idStatus));

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

		$sql = <<<__SQL__
UPDATE StatusQuarantine 
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
		return JWDB::DelTableRow('StatusQuarantine', array (	'id'	=> intval($idStatus) ));
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

		$statusRow = self::GetDbRowById( $idStatus );

		if( empty( $statusRow ) )
			return true;
					
		if( true===$updateFlag ) {
			self::SetDealStatusByIds( $idStatus, JWStatusQuarantine::DEAL_ALLOWED );
		}

		$options = array(
				'idUserReplyTo' => $statusRow['idUserReplyTo'],
				'idStatusReplyTo' => $statusRow['idStatusReplyTo'],
				'idConference' => $statusRow['idConference'],
				'nofilter' => true,
			);

		$ret = JWSns::UpdateStatus( 
				$statusRow['idUser'], 
				$statusRow['status'], 
				$statusRow['device'], 
				strtotime($statusRow['timeCreate']), 
				$statusRow['isSignature'], 
				null, 
				$options );

		return ( $ret ) ? true : false;
	}

	/**
	 * @param status_ids
	 */
	static public function GetMergedQuarantineStatusFromUser($idUser=0, $oStatusIds=array(), $oStatusRows=array() ){

		if( empty($idUser) || empty($oStatusRows) || empty($oStatusIds) )
			return array();

		$timeSince = null;
		$nStatusIds = $oStatusIds;
		$nStatusRows = $oStatusRows;

		foreach($oStatusRows as $id=>$r){
			$timeSince = ($timeSince==null) ? $r['timeCreate'] : ( ($timeSince < $r['timeCreate'] ) ? $timeSince : $r['timeCreate'] );
		}

		$q_ids = JWStatusQuarantine::GetDbIdsFromUser($idUser, JWStatusQuarantine::DEAL_NONE, $timeSince);
		$q_rows = array();
		if( !empty( $q_ids ) ){
			$q_rows = JWStatusQuarantine::GetDbRowsByIds( $q_ids );
		}

		foreach($q_ids as $id){
			$nStatusIds[] = 'QID_'.$id;
		}
		foreach($q_rows as $k=>$row){
			$nStatusRows['QID_'.$k] = $row;
		}
		
		$sortedBy = array();
		foreach($nStatusIds as $id){
			if( isset( $nStatusRows[$id] ) )
				$sortedBy[ $id ] = $nStatusRows[$id]['timeCreate'];
		}
		arsort($sortedBy);
		$nStatusIds = array_keys( $sortedBy );
		
		return array( 
				'status_ids' => $nStatusIds,
				'status_rows' => $nStatusRows,
			    );

	}

	static private function GetDealCondition( $dealStatus = JWStatusQuarantine::DEAL_ALLOWED ){
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

		return $dealCondition;
	}
}
?>
