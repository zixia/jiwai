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


	/**
	 * const quarantine type
	 */
	const T_STATUS = 'STATUS';
	const T_MESSAGE = 'MESSAGE';
	const T_CONFERENCE = 'CONFERENCE';

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

		$metaString = Base64_Encode( serialize( $metaInfo ) );

		return JWDB::SaveTableRow('QuarantineQueue', 
						array(
							'idUserFrom' => $idUserFrom,
							'idUserTo' => $idUserTo,
							'type' => $type,
							'metaInfo' => $metaString,
							'timeCreate' => date('Y-m-d H:i:s'),
						));
	}

	static public function GetQuarantineQueueNum($type=self::T_STATUS, $dealStatus=self::DEAL_NONE){

		$sql = <<<_SQL_
SELECT COUNT(1) AS count 
	FROM QuarantineQueue
	WHERE 
		dealStatus = '$dealStatus'
		AND type = '$type'
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
	static public function GetQuarantineQueue($dealStatus=JWQuarantineQueue::DEAL_NONE, $limit=20,$offset=0){

		$dealCondition = self::GetDealCondition( $dealStatus );

		$sql = <<<SQL
SELECT * FROM QuarantineQueue
	WHERE $dealCondition
	ORDER BY id DESC
	LIMIT $offset , $limit
SQL;
		$result = JWDB::GetQueryResult( $sql, true );
		return $result;
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
			$metaInfo = @unserialize( Base64_Decode( $row['metaInfo'] ) );
			if( empty( $metaInfo ) )
				continue;

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

		return $rows;
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
}
?>
