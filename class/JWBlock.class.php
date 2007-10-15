<?php
/**
 * @package	JiWai.de
 * @copyright	AKA Inc.
 * @author	zixia@zixia.net
 * @version	$Id$
 */

/**
 * JiWai.de Block Class
 */
class JWBlock {

	/**
	 * Constructing method, save initial state
	 *
	 */
	private function __construct()
	{
	}

	/**
	 * 	Get ids of whom blocked the idUser.
	 *	@return array	array of friend id list
	 */
	static function GetIdUsersByIdUserBlock($idUserBlock, $limit=9999, $start=0)
	{

		$idUserBlock = JWDB::CheckInt( $idUserBlock );
		$limit = JWDB::CheckInt( $limit );

		$start  = intval($start);

		$sql = <<<_SQL_
SELECT	idUser
	FROM
		Block
	WHERE
		idUserBlock=$idUserBlock
		AND idUser IS NOT NULL
	LIMIT $start, $limit
_SQL_;

		$rows = JWDB::GetQueryResult($sql, true);

		if ( empty($rows ) )
			return array();

		$rtn = array();
		foreach ( $rows as $r )
			array_push($rtn, $r['idUser']);

		return $rtn;
	}


	/**
	 * 	Get ids of whom is blocked the $idUser
	 *	@return array	array of be-friend id list
	 */
	static function GetIdUserBlocksByIdUser($idUser, $limit=40, $start=0)
	{
		$idUser = JWDB::CheckInt( $idUser );
		$limit = JWDB::CheckInt( $limit );

		$start  	= intval($start);

		$sql = <<<_SQL_
SELECT	idUserBlock
	FROM
		Block
	WHERE
		idUser=$idUser
		AND idUserBlock IS NOT NULL
	LIMIT $start,$limit
_SQL_;

		$rows = JWDB::GetQueryResult($sql, true);

		if ( empty($rows ) )
			return array();

		$rtn = array();
		foreach ( $rows as $r )
			array_push($rtn, $r['idUserBlock']);

		return $rtn;
	}



	/**
	 * 删除
	 */
	static public function Destroy($idUser, $idUserBlock)
	{
		$idUser = JWDB::CheckInt($idUser);
		$idUserBlock = JWDB::CheckInt($idUserBlock);

		return JWDB::DelTableRow( 'Block', array(
				'idUser' => $idUser,
				'idUserBlock' => $idUserBlock,
		));
	}

	/**
	 * Check isBlocked
	 */
	static public function IsBlocked( $idUser, $idUserBlock ) {
		$exArray = array( 'idUser' => $idUser, 'idUserBlock' => $idUserBlock, );
		if( JWDB::ExistTableRow( 'Block', $exArray ) )
			return true;

		return false;
	}


	/**
	 * idUser block idUserBlock
	 */
	static public function Create($idUser, $idUserBlock)
	{
		$idUser = JWDB::CheckInt($idUser);
		$idUserBlock = JWDB::CheckInt($idUserBlock);

		$exArray = array(
			'idUser' => $idUser,
			'idUserBlock' => $idUserBlock,
		);

		if( $idExist = JWDB::ExistTableRow( 'Block', $exArray ) )
			return $idExist;

		$exArray['timeCreate'] = date('Y-m-d H:i:s');

		return JWDB::SaveTableRow( 'Block', $exArray );
	}
}
?>
