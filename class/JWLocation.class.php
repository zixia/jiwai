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

		$idParent = abs( intval( $idParent ) );

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
		$result = self::GetDbRowsByIds( array($idLocation) );

		if( empty( $result ) )
			return array();

		return $result[ $idLocation ];
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

    static public function GetLocationName($location){
        $pid = $cid = $pname = $cname = null;
        @list( $pid, $cid ) = explode('-', $location );
        if( intval($pid) ) {
            $prov = self::GetDbRowById( intval($pid) );
            $pname = ( empty($prov) ) ? null : $prov['name'];
        }
        if( intval($cid) ) {
            $city = self::GetDbRowById( intval($cid) );
            $cname = ( empty($city) ) ? null : $city['name'];
        }

        return trim( "$pname $cname" );
    }
}
?>
