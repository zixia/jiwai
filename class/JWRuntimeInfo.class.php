<?php
/**
 * @package	JiWai.de
 * @copyright	AKA Inc.
 * @author	shwdai@jiwai.de
 */

/**
 * JiWai.de JWRuntimeInfo Class
 */
class JWRuntimeInfo {

	/**
	 * Get Value
	 */
	static public function Get($name){
		
		$name = JWDB::EscapeString( strval($name) );

		$sql = <<<_SQL_
SELECT value FROM RuntimeInfo WHERE name = '$name'
_SQL_;
		$row = JWDB::GetQueryResult( $sql, false );

		if( empty( $row ) )
			return false;

		return self::DecodeBase64Serialize( $row['value'] );
	}

	/**
	 * Create / UPDATE 都用这个，该表有唯一索引
	 */
	static public function Set( $name, $value ) {

		if( $idExist = JWDB::ExistTableRow( 'RuntimeInfo', array( 'name' => $name ) ) ){
			JWDB::UpdateTableRow( 'RuntimeInfo', $idExist, array(
						'value' => self::EncodeBase64Serialize( $value ) ,
					));

			return $idExist;
		}

		return JWDB::SaveTableRow( 'RuntimeInfo', array(
					'name' => $name,
					'value' => self::EncodeBase64Serialize( $value ),
				) );
	}

	/**
	 * Encode metaInfo
	 */
	static private function EncodeBase64Serialize( $metaInfo = null ){
		if( is_array( $metaInfo ) )
			return Base64_Encode( serialize( $metaInfo ) );

		return $metaInfo;
	}

	/**
	 * Decode metaInfo 
	 */
	static private function DecodeBase64Serialize( $metaString ) {

		if( preg_match( '/^[a-zA-Z0-9\=\/]+$/', $metaString ) ) {
			$result = @unserialize( Base64_Decode( $metaString ) );
			if( is_array( $result ) ) {
				return $result;
			}
		}

		return $metaString;
	}
}
?>
