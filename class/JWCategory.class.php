<?php
/**
 * @package	JiWai.de
 * @copyright	AKA Inc.
 * @author	shwdai@jiwai.de
 */

/**
 * JiWai.de JWCategory Class
 */
class JWCategory {

	/**
	 * CONST
	 */
	const T_STOCK = 'STOCK';

	/**
	 * Get DbRowById
	 */
	static public function GetDbRowById($idCategory){
		$idCategory = JWDB::CheckInt( $idCategory );

		$result = self::GetDbRowsByIds( array($idCategory) );
		if( empty( $result ) )
			return array();

		return $result[ $idCategory ];
	}

	/**
	 * GetDbRowsByIds
	 */
	static public function GetDbRowsByIds($idCategory){
		if( empty( $idCategory ) )
			return array();
		settype( $idCategory, 'array' );

		$inCondition = Implode( ',', $idCategory );

		$sql = <<<_SQL_
SELECT * FROM Category WHERE id IN ($inCondition)
_SQL_;

		return self::GetDbRowsBySQL( $sql, true );
	}

	
	/**
	 * Get Son Category
	 */
	static public function GetSonCategory($idCategory, $type=self::T_STOCK)
	{
		$idCategory = intval( $idCategory );
		$sql = <<<_SQL_
SELECT * FROM Category 
	WHERE 
		idParent = $idCategory
		AND type = '$type'
_SQL_;

		return self::GetDbRowsBySQL( $sql, true );
	}
	
	/**
	 * Get Db Rows By SQL
	 */
	static public function GetDbRowsBySQL( $sql, $moreThanOne = false ) {

		$rows = JWDB::GetQueryResult( $sql, $moreThanOne );
		if( empty( $rows ) )
			return array();

		if( $moreThanOne == false )
			return $rows;

		$rtn = array();
		foreach($rows as $r){
			$r['metaInfo'] = self::DecodeBase64Serialize( $r['metaInfo'] );
			$rtn[ $r['id'] ] = $r;
		}

		return $rtn;
	}

	static public function Create( $name, $idParent=0, $type=self::T_STOCK, $metaInfo=array() ) {
		$idParent = intval( $idParent );

		if( $idParent ){
			$parent = self::GetDbRowById( $idParent );
			if( empty( $parent ) ) 
				return false;
			$type = $parent['type'];
		}

		return JWDB::SaveTableRow( 'Category', array(
					'name' => $name,
					'type' => $type,
					'idParent' => $idParent,
					'metaInfo' => self::EncodeBase64Serialize( $metaInfo ),
				) );
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
