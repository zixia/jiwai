<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Location Class
 */
class JWLocation {
	/**
	 * Instance of this singleton
	 *
	 * @var JWLocation
	 */
	static private $msInstance;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWLocation
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
	 * 获取父id下所有子记录
	 */
	static public function GetDBRowsByIdParent( $idParent ) {

		$idParent = JWDB::CheckInt( $idParent );

		$sql = "SELECT * FROM Location WHERE idParent=$idParent";

		$rows = JWDB::GetQueryResult( $sql , true );

		if( empty($rows) )
			return array();

		$rtn = array();
		foreach( $rows as $one ) {
			$rtn[ $one['id'] ] = $one ;
		}

		return $rtn;
	}
	
	/**
  	 * Get one by id
	 */	 
	static public function GetDbRowById( $idLocation ) {

		$idLocation = JWDB::CheckInt( $idLocation );
		return array_values( self::GetDbRowsByIds( array($idLocation) ) );
	}

	/**
  	 * Get multi by ids
	 */	 
	static public function GetDbRowsByIds( $idLocations ) {
		settype( $idLocations, 'array' ) ;

		if( empty( $idLocations ) )
			return array();
	
		$idsString = implode( ',' , $idLocations );
		$sql = "SELECT * FROM Location WHERE id IN ( $idsString )";

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
