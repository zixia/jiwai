<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Mobile Class
 * SUPPLIES must IN ( MOBILE, UNICOM, PAS )
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
	static public function GetDbRowByMobileNo( $mobileNo ) {

		if( strlen( $mobileNo ) <= 10 ) 
			return array();

		$pre1 = substr( $mobileNo, 0, 1 );

		if( $pre1 == '1' )
			$pre = substr( $mobileNo, 0, 7 );
		else
			$pre = substr( $mobileNo, 0, 4 );

		return self::GetDbRowByPreNum( $pre );

	}

	/**
	 * @param prenum of mobile maybe 7 or 4 len
	 */
	static public function GetDbRowByPreNum( $prenum ) {
		$sql = "SELECT * FROM Mobile WHERE prenum = '$prenum'";

		$row = JWDB::GetQueryResult( $sql, false );

		return empty( $row ) ? array() : $row ;
	}

	/**
	 * @param $idLocations 位置 id 数组
	 * @param $supplier 分 MOBILE | UNICOME | PAS
	 */
	static public function GetDbRowsByIdLocationProvince( $idLocationProvince , $supplier = 'MOBILE' ) {

        $idLocationProvince = JWDB::CheckInt( $idLocationProvince );

		$sql = "SELECT * FROM Mobile WHERE idLocationProvince = $idLocationProvince AND supplier='$supplier'";

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
	static public function UpdateCodeFunc( $idLocationProvince, $supplier='MOBILE', $forceCode = null, $forceFunc=null) {
        $idLocationProvince = JWDB::CheckInt( $idLocationProvince );

		if( $forceCode == null )
			$forceCode = 'NULL';
		if( $forceFunc == null )
			$forceFunc = 'NULL';

		$idLocationsString  = implode( ',' , $idLocations );

		$sql = "UPDATE Mobile SET forceCode=$forceCode, forceFunc=$forceFunc WHERE supplier='$supplier' AND idLocationProvince = $idLocationProvince ";

		return JWDB::Execute( $sql );

	}
	
	/**
	 * Get One by id
	 */
	static public function GetDbRowById( $idMobile ) {
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

    static public function Create( $mobileArray = array() ) {
        return JWDB::SaveTableRow( 'Mobile', $mobileArray );
    }

    static public function GetSpCode( $mobileNo, $serverAddress = null ) {
        
        $code = array();

        if( null == $serverAddress || 0 == $serverAddress ) 
        {
            $forceRow = self::GetDbRowByMobileNo( $mobileNo );
            if( false == empty($forceRow) ) {
                if( $forceRow['forceCode'] ){
                    $code = JWSPCode::GetCodeByCodeNum( $forceRow['forceCode'] );
                    if( false == empty( $code ) && $forceRow['forceFunc'] ) 
                    {
                        $code['func'] = $forceRow['forceFunc'];
                    }
                }else{
                    $code = JWSPCode::GetCodeByMobileNo( $mobileNo );
                }
            }
        }
        else
        {
            $code = JWSPCode::GetCodeByServerAddress( $serverAddress );
        }

        return $code;
    }
}
?>
