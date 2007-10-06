<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de OfflineMessage Class
 */
class JWOfflineMessage {

	/**
	 * Get OfflineMessage ById
	 */
	static public function GetDbRowById($idOfflineMessage){
		$idOfflineMessage = JWDB::CheckInt( $idOfflineMessage );
		$sql = <<<_SQL_
SELECT * FROM OfflineMessage
	WHERE id = $idOfflineMessage
_SQL_;

		$row = JWDB::GetQueryResult( $sql, false );

		return $row;
	}

	/**
	 * Get Rows By idUser
	 */
	static public function GetDbRowsByIdUser( $idUser, $offset=0, $limit=20 ) {
		$idUser = JWDB::CheckInt( $idUser );
		$sql = <<<_SQL_
SELECT * FROM OfflineMessage
	WHERE 
		idUser = $idUser
	ORDER BY 
		id DESC 
	LIMIT $offset, $limit
_SQL_;
		$rows = JWDB::GetQueryResult( $sql, true );

		if( empty( $rows ) )
			return array();

		$rtn = array();
		foreach( $rows as $r ){
			$rtn[ $r['id'] ] = $r;
		}
		
		return $rtn;
	}

	/**
	 * Delete expired message
	 */
	 static public function DeleteExpiredMessage( $day = 7 ) {
		$day = intval( $day );
		if( $day <= 0 )
			$day = 7;
		
		if( $day > 30 )
			$day = 30;

		$sql = <<<_SQL_
DELETE FROM OfflineMessage
	WHERE 
		timeCreate < DATE_SUB( CURDATE(), INTEVAL $day DAY )
_SQL_;
		
		return JWDB::Execute( $sql );
	 }

	/**
	 * Delete OfflineMessage by idUser
	 */
	 static public function DeleteUserMessage( $idUser ){
		 $idUser = JWDB::CheckInt( $idUser );

		 return JWDB::DelTableRow( 'OfflineMessage', array( 'idUser' => $idUser, ) );
	 }

	/**
	 * Create OfflineMessage Setting
	 */
	static public function Create( $idUser, $message=null, $device='msn', $options=array() ){

		$idUser = JWDB::CheckInt( $idUser );
		$timeCreate = time();

		return JWDB::SaveTableRow('OfflineMessage', array(
			'idUser' => $idUser,
			'device' => $device,
			'message' => $message,
			'timeCreate' => date('Y-m-d H:i:s', $timeCreate),
		));
	}

}
?>
