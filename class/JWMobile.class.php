<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Mobile Class
 * SUPPLIES must IN ( MOBILE, UNICOM, PHS )
 */
class JWMobile {
	/**
	 * Instance of this singleton
	 *
	 * @var JWMobile
	 */
	static private $msInstance;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWMobile
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

	/**
	 * Get Mobile Db from mobileNo
	 */
	static public function GetDBRowByMobileNo( $mobileNo ) {

		if( strlen( $mobileNo ) <= 10 ) 
			return array();

		$pre1 = substr( $mobileNo, 0, 1 );

		if( $pre1 == '1' )
			$pre = substr( $mobileNo, 0, 7 );
		else
			$pre = substr( $mobileNo, 0, 4 );

		return self::GetDBRowByPreNum( $pre );

	}

	/**
	 * @param prenum of mobile maybe 7 or 4 len
	 */
	static public function GetDBRowByPreNum( $prenum ) {
		$sql = "SELECT * FROM Mobile WHERE prenum = '$prenum'";

		$row = JWDB::GetQueryResult( $sql, false );

		return empty( $row ) ? array() : $row ;
	}

	/**
	 * @param $idLocations 位置 id 数组
	 * @param $supplier 分 MOBILE | UNICOME | PHS
	 */
	static public function GetDbRowsByIdLocations( $idLocations , $supplier = 'MOBILE' ) {
		settype( $idLocations , 'array' );

		if( empty( $idLocations ) )
			return array();

		$idLocationsString = implode( ',', $idLocations );

		$sql = "SELECT * FROM Mobile WHERE idLocation IN ( $idLocationsString ) AND supplier='$supplier'";

		$rows = JWDB::GetQueryResult( $sql, true );

		if( empty($rows) )
			return array();

		$rtn = array();
		foreach( $rows as $one ) {
			$rtn[ $one['id'] ] = $one ;
		}

		return $rtn;
	}

	/**
	  * 改变相应（位置，供应商）条件的改变特服号
	  */
	static public function UpdateCodeFunc( $idLocations, $supplier='MOBILE', $forceCode = null, $forceFunc=null) {
		settype( $idLocations, 'array' );
		if( empty( $idLocations ) )
			return true;

		if( $forceCode == null )
			$forceCode = 'NULL';
		if( $forceFunc == null )
			$forceFunc = 'NULL';

		$idLocationsString  = implode( ',' , $idLocations );

		$sql = "UPDATE Mobile SET forceCode=$forceCode, forceFunc=$forceFunc WHERE supplier='$supplier' AND idLocation IN ( $idLocationsString ) ";

		return JWDB::Execute( $sql );

	}
	
	/**
	 * Get One by id
	 */
	static public function GetDBRowById( $idMobile ) {
		return array_values( self::GetDbRowsByIds( array( $idMobile ) ));
	}

	/**
	 * Get rows by ids 
	 */
	static public function GetDbRowsByIds( $idMobiles ) {

		settype( $idMobiles, 'array' ) ;

		if( empty( $idMobiles ) )
			return array();
	
		$idsString = implode( ',' , $idMobiles );
		$sql = "SELECT * FROM Mobile WHERE id IN ( $idsString )";

		$rows = JWDB::GetQueryResult( $sql, true );

		if( empty($rows) )
			return array();

		$rtn = array();
		foreach( $rows as $one ) {
			$rtn[ $one['id'] ] = $one ;
		}

		return $rtn;
	}
}
?>
