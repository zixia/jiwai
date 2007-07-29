<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de StatusNotityQueue Class
 */
class JWStatusNotifyQueue {
	/**
	 * Instance of this singleton
	 *
	 * @var JWStatusNotifyQueue
	 */
	static private $instance__;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWStatusNotifyQueue
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

	static public function Create($idUser, $idStatus, $time=null, $extraInfo=array()){
		$idUser = JWDB::CheckInt( $idUser );
		$idStatus = JWDB::CheckInt( $idStatus );

		$time = ( null == $time ) ? time() : $time;
		$timeCreate = date("Y-m-d H:i:s", $time);

		$metaInfo = json_encode( $extraInfo );

		JWDB::SaveTableRow( 'StatusNotifyQueue' , array(
					'idUser' => $idUser,
					'idStatus' => $idStatus,
					'timeCreate' => $timeCreate,
					'metaInfo' => $metaInfo,
					));
	}

	static public function GetUndealStatus( $idSince = 0, $num = 100 ){
		$idSince = JWDB::CheckInt( $idSince );
		$num = JWDB::CheckInt( $num );

		$sql = <<<SQL
SELECT * FROM StatusNotifyQueue
	WHERE id >= $idSince
	ORDER BY id ASC
	LIMIT $num
SQL;
		$result = JWDB::GetQueryResult( $sql );
		return $result;
	}
}
?>
