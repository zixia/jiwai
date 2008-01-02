<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	seek@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de FeedBack Class
 */
class JWFeedBack {
	/**
	 * Instance of this singleton
	 *
	 * @var JWFeedBack
	 */
	static private $instance__;

	/**
	 * dealStatus
	 */
	const DEAL_NONE = 'NONE';
	const DEAL_WONTFIX = 'WONTFIX';
	const DEAL_FIXED = 'FIXED';

	/**
	 * type
	 */
	const T_MO = 'MO';
	const T_MT = 'MT';
	const T_MOMT = 'MOMT';
	const T_COMPLAIN = 'COMPLAIN';

	/**
	 * Instance of this singleton class
	 *
	 * @return JWFeedBack
	 */
	static public function &instance()
	{
		if (!isset(self::$instance__)) {
			$class = __CLASS__;
			self::$instance__ = new $class;
		}
		return self::$instance__;
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
	}

	static public function Create( $user_id, $device,$type=self::T_MO, $remark=null, $meta_info=array() )
	{

			$user_id = JWDB::CheckInt( $user_id );

			$meta_string = self::EncodeBase64Serialize( $meta_info );

			return JWDB::SaveTableRow( 'FeedBack' , array(
				'idUser' => $user_id,
				'device' => $device,
				'remark' => $remark,
				'timeCreate' => JWDB::MysqlFuncion_Now(),
				'type' => $type,
				'dealStatus' => self::DEAL_NONE,
				'metaInfo' => $meta_string,
			));
	}

	static public function GetUnDealStatus( $num = 100 ){
		$num = JWDB::CheckInt( $num );

		$sql = <<<SQL
SELECT * FROM FeedBack
	WHERE dealStatus='NONE'
	ORDER BY id ASC
	LIMIT $num
SQL;

		$rtn = array();

		$result = JWDB::GetQueryResult( $sql , true);
		if( is_array( $result ) ) {
			foreach( $result as $k=>$one ) {
				$one['metaInfo'] = self::DecodeBase64Serialize( $one['metaInfo'] );
				$rtn[ $k ] = $one;
			}
		}

		return $rtn;
	}

	static public function SetDealStatus( $id, $dealStatus = self::DEAL_FIXED ){
		settype( $id, 'array');
		if( empty( $id ) )
			return;
		$idCondition = implode( ',', $id );
		
		$sql = <<<SQL
UPDATE FeedBack 
	SET dealStatus='$dealStatus'
	WHERE id IN ($idCondition)
SQL;

		return JWDB::Execute( $sql );
	}

	static public function Destroy( $feedback_id ) 
	{
		$feedback_id = JWDB::CheckInt( $feedback_id );
		return JWDB::DelTableRow( 'FeedBack', array( 'id' => $feedback_id ) );
	}

	static public function GetDbRowByCondition( $device=null,$type=null, $deal_status=null, $time_begin=null, $time_end=null ) 
	{
		$condition = null;
		if( $device )
			$condition .=" AND device = '$device'";

		if( $type )
		{
			if ( self::T_MOMT==$type ) 
			{
				$condition .= "AND ( type='MO' OR type='MT' )";
			}
			else
			{
				$condition .= " AND type = '$type'";
			}
		}
				
		if( $deal_status )
			$condition .= " AND dealStatus = '$deal_status'";

		if( $time_begin )
			$condition .= " AND timeCreate >= '$time_begin'";

		$time_end = ( $time_end ) ? 
			$time_end : ( $time_begin ? date('Y-m-d H:i:s', strtotime($time_begin) + 86400 ) : null );

		if( $time_end )
			$condition .= " AND timeCreate <= '$time_end'";

		$sql = "SELECT * FROM FeedBack WHERE 1=1 $condition";

		$rows = JWDB::GetQueryResult( $sql, true );
		if( empty( $rows ) )
			return array();
	
		$rtn_array = array();
		foreach( $rows as $one ) 
		{
			$one['metaInfo'] = self::DecodeBase64Serialize( $one['metaInfo'] );
			$rtn_array[ $one['id'] ] = $one;
		}

		return $rtn_array;
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
