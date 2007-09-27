<?php
/**
 * @package	JiWai.de
 * @copyright	AKA Inc.
 * @author	shwdai@jiwai.de
 */

/**
 * JiWai.de JWFollowRecursion Class
 */
class JWFollowRecursion{
	/**
	 * 获得级联follower_ids
	 */
	static public function GetSuperior($idUser, $level=1)
	{
		$user_ids = array( $idUser );
		settype( $idUser, 'array' );
		
		$in_ids = $idUser;
		for( $i=0; $i<$level && false==empty($in_ids) ; $i++ ) {
			$inCondition = implode( ',', $in_ids );
			$sql = "SELECT idUserSuperior FROM FollowRecursion WHERE idUser in ($inCondition)";
			$rows = JWDB::GetQueryResult( $sql, true );
			if( empty($rows ) )
				break;

			$in_ids = array();
			foreach( $rows as $r ) {
				array_push( $in_ids, $r['idUserSuperior'] );
				array_push( $user_ids, $r['idUserSuperior'] );
			}
		}

		$user_ids = array_unique( $user_ids );
		return $user_ids;
	}
	
	/**
	 * 建立用户级联关系
	 */
	static public function Create($idUser, $idUserSuperior, $forceReverse=false)
	{
		$e = array( 'idUser' => $idUserSuperior, 'idUserSuperior' => $idUser, );
		if( $idExist = JWDB::ExistTableRow( 'FollowRecursion', $e ) ) {
			if( $forceReverse ) {
				JWDB::DelTableRow( 'FollowRecursion', array('id'=>$idExist,) );
			}else{
				return false;
			}
		}

		$u = array( 'idUserSuperior' => $idUserSuperior, 'idUser' => $idUser, );
		if( JWDB::ExistTableRow( 'FollowRecursion', $u ) )
			return false;

		return JWDB::SaveTableRow( 'FollowRecursion', $u );
	}
}
?>
