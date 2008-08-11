<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	freewizard@gmail.com
 * @version		$Id$
 */

/**
 * JiWai.de Vender Class
 */
class JWVender {
	static function Bind($user_id, $vender_id, $vender_user_id, $vender_meta=array()) {
		$exist_id = JWDB::ExistTableRow('Vender', array( 
				'idUser' => $user_id, 
				'vender' => $vender_id,
			));

		$up_array = array(
				'idUser' => $user_id, 
				'vender' => $vender_id,
				'venderUser' => $vender_user_id,
				'venderMeta' => json_encode($vender_meta),
			);
		if( $exist_id ) 
			{
				JWDB::UpdateTableRow( 'Vender', $exist_id, $up_array );
			}
		else
			{
				$exist_id = JWDB::SaveTableRow( 'Vender', $up_array );
			}
		JWDB_Cache_Vender::_dirty($user_id , $vender);
		return $exist_id;
	}

	static function Unbind($user_id, $vender_id) {
		$r = JWDB::DelTableRow( 'Vender', array(
				'vender'=>$vender_id,
				'idUser'=>$user_id
			));
		JWDB_Cache_Vender::_dirty($user_id , $vender);
		return $r;
	}

	static public function Query( $user_id , $vender=null) 
	{
		$user_id = JWDB::CheckInt( $user_id );
		$sql = "SELECT * FROM Vender WHERE idUser=$user_id";
		if ($vender) $sql.=" AND vender='$vender'"; //FIXME SQL Injection
		$r = JWDB::GetQueryResult( $sql, true );
		if( empty($r) ) 
			return array();

		$rtn = array();
		foreach( $r as $one ) {
			$one['venderMeta'] = json_decode($one['venderMeta'], true);
			$rtn[ $one['vender'] ] = $one;
		}

		if ($vender) $rtn=$rtn[$vender];
		return $rtn;
	}
}

?>
