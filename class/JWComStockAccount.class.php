<?php
/**
 * @package	JiWai.de
 * @copyright	AKA Inc.
 * @author	shwdai@jiwai.de
 */

/**
 * JiWai.de JWComStockAccount Class
 */
class JWComStockAccount {

	/**
	 * CONST
	 */
	const T_STOCK = 'STOCK';
	const T_CATE = 'CATE';

	/**
	 * Get DbRowsByType
	 */
	static public function GetIdUsersByType($type = self::T_STOCK){

		$sql = <<<_SQL_
SELECT * FROM ComStockAccount WHERE type='$type'
_SQL_;
		$rows = JWDB::GetQueryResult( $sql, true );
		if( empty( $rows ) )
			return array();

		$rtn = array();
		foreach($rows as $r){
			array_push( $rtn, $r['idUser'] );
		}

		return $rtn;
	}

	/*
	 * CREATE OR UPDATE
	 */

	static public function Set( $idUser, $type=self::T_STOCK ) {

		$idUser = JWDB::CheckInt($idUser);

		$idExist = JWDB::ExistTableRow( 'ComStockAccount', array(
					'idUser', $idUser,
				));

		if( $idExist ) {
			JWDB::UpdateTableRow( 'ComStockAccount', $idExist, array( 'type'=>$type,) );
			return $idExist;
		}else{
			return JWDB::SaveTableRow( 'ComStockAccount', array(
					'idUser' => $idUser,
					'type' => $type,
				));
		}
	}
}
?>
