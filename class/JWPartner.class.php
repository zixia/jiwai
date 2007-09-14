<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Partner Class
 * SUPPLIES must IN ( MOBILE, UNICOM, PAS )
 */
class JWPartner {
	/**
	 * Instance of this singleton
	 *
	 * @var JWPartner
	 */
	static private $msInstance;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWPartner
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
	 * Get One by id
	 */
	static public function GetDbRowById( $idPartner ) {
		return array_values( self::GetDbRowsByIds( array( $idPartner ) ));
	}

	/**
	 * Get rows by ids 
	 */
	static public function GetDbRowsByIds( $idPartners ) {

		settype( $idPartners, 'array' ) ;

		if( empty( $idPartners ) )
			return array();
	
		$idsString = implode( ',' , $idPartners );
		$sql = "SELECT * FROM Partner WHERE id IN ( $idsString )";

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
     * Update Table Row
     */
    static public function Update( $idPartner, $partnerArray = array() ){
        $idPartner = JWDB::CheckInt( $idPartner );
        return JWDB::UpdateTableRow( 'Partner', $idPartner, $partnerArray );
    }
    
    /**
     * Create X_ddd
     */
    static public function Create( $partnerArray = array() ) {
        return JWDB::SaveTableRow( 'Partner', $partnerArray );
    }
}
?>
