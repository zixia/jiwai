<?php
/**
 * @package	JiWai.de
 * @copyright	AKA Inc.
 * @author  	shwdai@jiwai.de
 */

/**
 * JiWai.de QuarantineQueue Class
 */
class JWQuarantineQueue {
	const DEFAULT_STATUS_NUM = 20;

	/**
	 * const deal status
	 */
	const DEAL_NONE = 'NONE';
	const DEAL_SAVE = 'SAVE';
	const DEAL_DELE = 'DELE';


	/**
	 * const quarantine type
	 */
	const T_STATUS = 'STATUS';
	const T_CONFERENCE = 'CONFERENCE';  // 会议的Qurantine，是指，已上Status库，但没发出通知，或没显示在Web上的；
	const T_MESSAGE = 'MESSAGE';

	/**
	 * Instance of this singleton class
	 *
	 * @return JWQuarantineQueue
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
	static public function Create( $idUserFrom=null, $idUserTo=null, $type=self::T_STATUS, $metaInfo=array() ) {

		$metaString = self::EncodeBase64Serialize( $metaInfo );

		return JWDB::SaveTableRow('QuarantineQueue', 
						array(
							'idUserFrom' => $idUserFrom,
							'idUserTo' => $idUserTo,
							'type' => $type,
							'metaInfo' => $metaString,
							'timeCreate' => date('Y-m-d H:i:s'),
						));
	}

	static public function GetQuarantineQueueNum($type=self::T_STATUS, $idUsers=array(), $dealStatus=self::DEAL_NONE){

		if( empty( $idUsers ) ) {
			$inCondition = null;
		}else{
			settype($idUsers, 'array');
			if( $type == self::T_STATUS )
				$inCondition = 'AND idUserFrom IN (' . Implode(',', $idUsers) . ')';
			else
				$inCondition = 'AND idUserTo IN (' . Implode(',', $idUsers) . ')';
		}

		$sql = <<<_SQL_
SELECT COUNT(1) AS count 
	FROM QuarantineQueue
	WHERE 
		dealStatus = '$dealStatus'
		AND type = '$type'
		$inCondition
_SQL_;

		$result = JWDB::GetQueryResult( $sql, false );

		if( false == empty($result) ){
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
	static public function GetQuarantineQueue($type=self::T_STATUS, $idUsers=array(), $offset=0, $limit=20, $dealStatus=self::DEAL_NONE ){

		if( empty( $idUsers ) ) {
			$inCondition = null;
		}else{
			settype($idUsers, 'array');
			if( $type == self::T_STATUS )
				$inCondition = 'AND idUserFrom IN (' . Implode(',', $idUsers) . ')';
			else
				$inCondition = 'AND idUserTo IN (' . Implode(',', $idUsers) . ')';
		}
		
		$sql = <<<SQL
SELECT * FROM QuarantineQueue
	WHERE 
		dealStatus = '$dealStatus'
		AND type = '$type'
		$inCondition
	ORDER BY id DESC
	LIMIT $offset , $limit
SQL;

		$result = JWDB::GetQueryResult( $sql, true );
		if( empty( $result ) )
			return array();

		foreach( $result as $k=>$r ) {
			$result[$k]['metaInfo'] = self::DecodeBase64Serialize( $r['metaInfo'] );
		}

		return $result;
	}

	/**
	 * @param status_ids
	 */
	static public function GetQuarantineStatusFromUser($idUser=0, $oStatusIds=array(), $oStatusRows=array() ){

		if( empty($idUser) || empty($oStatusRows) || empty($oStatusIds) )
			return array();

		$timeSince = null;
		$nStatusIds = $oStatusIds;
		$nStatusRows = $oStatusRows;

		foreach($oStatusRows as $id=>$r){
			$timeSince = ($timeSince==null) ? $r['timeCreate'] : ( ($timeSince < $r['timeCreate'] ) ? $timeSince : $r['timeCreate'] );
		}

		$q_ids = self::GetIdsFromIdUserFrom($idUser, self::T_STATUS, self::DEAL_NONE, $timeSince);
		$q_rows = array();
		if( !empty( $q_ids ) ){
			$q_rows = self::GetDbRowsByIds( $q_ids );
		}

		foreach($q_ids as $id)
		{
			$nStatusIds[] = 'QID_'.$id;
		}

		foreach($q_rows as $k=>$row)
		{
			if( empty( $row['metaInfo'] ) )
				continue;

			$metaInfo = $row['metaInfo'] ;

			$metaInfo['idUser'] = $row['idUserFrom'];
			$metaInfo['idUserReplyTo'] = $row['idUserTo'];
			$metaInfo['timeCreate'] = $row['timeCreate'];

			$nStatusRows['QID_'.$k] = $metaInfo;
		}
		
		$sortedBy = array();
		foreach($nStatusIds as $id)
		{
			if( isset( $nStatusRows[$id] ) ) {
				$sortedBy[ $id ] = $nStatusRows[$id]['timeCreate'];
			}
		}

		arsort($sortedBy);
		$nStatusIds = array_keys( $sortedBy );
		
		return array( 
				'status_ids' => $nStatusIds,
				'status_rows' => $nStatusRows,
			    );

	}

	static public function GetDbRowById( $idQuarantine ){

		$idQuarantine = JWDB::CheckInt( $idQuarantine );

		$result = self::GetDbRowsByIds( $idQuarantine );

		if( empty( $result ) )
			return array();

		return $result[ $idQuarantine ];
	}

	/**
	 * 获取Db rows;
	 */
	static public function GetDbRowsByIds( $ids = array() ) {
		if( empty( $ids ) )
			return array();

		settype( $ids, 'array' );
		$idCondition = implode( ',', $ids );

		$sql = <<<_SQL_
SELECT * FROM QuarantineQueue
	WHERE id IN ( $idCondition )
_SQL_;
		$rows = JWDB::GetQueryResult( $sql , true );
		if( empty( $rows ) )
			return array();

		$rtn = array();
		foreach( $rows as $r ){
			$r['metaInfo'] = self::DecodeBase64Serialize( $r['metaInfo'] );
			$rtn[ $r['id'] ] = $r;
		}
		return $rtn;
	}

	/**
	 * @param $dealStatus, int
	 * @param $limit, int
	 * @param $offset, int
	 * @return mixed
	 */
	static public function GetIdsFromIdUserFrom($idUserFrom=0, $type=self::T_STATUS,
							$dealStatus=self::DEAL_NONE, $timeSince=null){

		if( ! $idUserFrom )
			return array();
		
		$timeCondition = null;
		if( $timeSince ) 
			$timeCondition = " AND timeCreate > '$timeSince' ";

		$sql = <<<_SQL_
SELECT  id
	FROM QuarantineQueue
	WHERE 
		idUserFrom = $idUserFrom 
		AND dealStatus = '$dealStatus'
		AND type = '$type'
		$timeCondition
	ORDER BY id DESC
_SQL_;

		$result = JWDB::GetQueryResult( $sql, true );
		
		$returnedArray = array();	
		if( !empty($result) ){
			foreach($result as $r){
				$returnedArray[] = $r['id'];
			}
		}

		return $returnedArray;
	}

	/**
	 * Deal a quarantine
	 */
	static public function DealQueue($idQuarantine, $dealStatus=self::DEAL_DELE ) {

		$idQuarantine = JWDB::CheckInt( $idQuarantine );

		switch( $dealStatus ) {
			case self::DEAL_DELE:
				return JWDB::DelTableRow('QuarantineQueue', array('id'=>$idQuarantine,) );
			case self::DEAL_SAVE:
			case self::DEAL_NONE:
				return JWDB::UpdateTableRow('QuarantineQueue', $idQuarantine, 
						array(
							'dealStatus' => $dealStatus, 
						));
			break;
			default:
				return true;
		}
	}

	static public function FireStatus($quarantine_id)
	{
		$quarantine = self::GetDbRowById( $quarantine_id );
		$callback = array('JWSns', 'UpdateStatus');

		self::DealQueue( $quarantine_id, self::DEAL_DELE );

		call_user_func_array( $callback, $quarantine['metaInfo'] );

		return true;
	}

	/**
	 * fireConference/Status by Id
	 */
	static public function FireConference($idQuarantine, $notify='ALL', $delete=false ) {

		$quarantine = self::GetDbRowById( $idQuarantine );
		if( empty($quarantine) || $quarantine['type'] != self::T_CONFERENCE || empty($quarantine['metaInfo']) )
			return false;
		
		$idSender = $quarantine['idUserFrom'];
		$idUserConference = $quarantine['idUserTo'];
		$metaInfo = $quarantine['metaInfo'];

		$status = $metaInfo['status'];
		$idStatus = $metaInfo['idStatus'];
		$options = $metaInfo['options'];
		$idUserReplyTo = $metaInfo['idUserReplyTo'];

		$idConference = $options['idConference'];


		if( null == $idStatus ) {
			return self::DealQueue( $idQuarantine, self::DEAL_DELE );
		}

		if( $delete == true) {
			/**
			 * 删除 Status上的idConference
			 */
			JWStatus::SetIdConference( $idStatus, null );
		}else {
			if( JWStatus::SetIdConference( $idStatus, $idConference ) ) {
				$metaInfo = array(
					'message' => $status,
					'options' => array(
						'idStatus' => $idStatus,
						'idConference' => $idConference,
						'idUserConference' => $idUserConference,
						'notify' => $notify,
					),
				);
				$queueType = JWNotifyQueue::T_CONFERENCE;
				JWNotifyQueue::Create( $idSender, $idUserReplyTo, $queueType, $metaInfo );
			}
		}

		return self::DealQueue( $idQuarantine, self::DEAL_DELE );
	}
	
	/**
	 * Encode metaInfo
	 */
	static private function EncodeBase64Serialize( $metaInfo = array()){
		return Base64_Encode( serialize( $metaInfo ) );
	}

	/**
	 * Decode metaInfo 
	 */
	static private function DecodeBase64Serialize( $metaString ) {
		return @unserialize( Base64_Decode( $metaString ) );
	}
}
?>
