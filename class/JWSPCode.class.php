<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de SPCode Class
 * SUPPLIES must IN ( MOBILE, UNICOM, PAS )
 */
class JWSPCode {
    /**
     * SP constant 
     */
    const SP_MOBILE     = 1;
    const SP_UNICOM     = 2;
    const SP_PAS        = 3;
    const SP_UNKNOWN    = 0;

    static private $codeBase = array(
        '9911' => array( 
                    'sp' => self::SP_MOBILE,  
                    'code' => 9911,
                    'gid' => 1,
                    'func' => 8816,
		    'default' => true,
                ),
        '9501' => array(
                    'sp' => self::SP_UNICOM,
                    'code' => 9501,
                    'gid' => 45,
                    'func' => 4567,
		    'default' => true,
                ),
        '50136' => array(
                    'sp' => self::SP_MOBILE,
                    'code' => 50136,
                    'gid' => 85,
                    'func' => 999,
		    'default' => false,
                ),
        '9318' => array(
                    'sp' => self::SP_UNICOM,
                    'code' => 9318,
                    'gid' => 3,
                    'func' => 8816,
		    'default' => false,
                ),
        '99318' => array(
                    'sp' => self::SP_PAS,
                    'code' => 99318,
                    'gid' => 52,
                    'func' => 456,
		    'default' => true,
                ),
        );

    static public function GetCodeBySupplier( $supplier=self::SP_MOBILE , $forceDefault = true) {
	    foreach( self::$codeBase as $c ){
		    if( $c['sp'] == $supplier && ( 
					    $forceDefault == false 
					    || ( isset($c['default']) && $c['default']==true ) 
					    )
		      )
		    {
			return $c;
		    }
	    }
	    return array();
    }
    static public function GetCodeByServerAddress( $serverAddress )
    {
        $keys = array_keys( self::$codeBase );
        $keysPat = implode( '|', $keys );
        if( preg_match( '/^('.$keysPat.')/', $serverAddress, $matches ) )
        {
            $code = self::$codeBase[ $matches[1] ];
            $code['func'] = substr( $serverAddress, strlen( $matches[1] ) );
            return $code;
        }
        return array();
    }

    static public function GetCodeByMobileNo( $mobileNo ) {
	if ( preg_match('/^13[4-9]\d+$/',$mobileNo ) 
			|| preg_match('/^159\d+$/',$mobileNo)
			|| preg_match('/^158\d+$/',$mobileNo)
	){
		return self::GetCodeBySupplier( self::SP_MOBILE );
			return self::SP_CHINAMOBILE;
	}

	if ( preg_match('/^13[0-3]\d+$/',$mobileNo ) ) {
		return self::GetCodeBySupplier( self::SP_UNICOM );
	}
	if ( preg_match('/^\d{11,12}$/',$mobileNo ) ) {
		return self::GetCodeBySupplier( self::SP_PAS, false );
	}

	return array();
    }

    static public function GetCodeByCodeNum( $codeNum )
    {
        $codeNum = JWDB::CheckInt( $codeNum );

        if( isset( self::$codeBase[ $codeNum ] ) )
        {
            return self::$codeBase[ $codeNum ];
        }
        return array();
    }

    static public function GetCodeByGid( $gid )
    {
        $gid = JWDB::CheckInt( $gid );

        foreach( self::$codeBase as $c )
        {
            if( $c['gid'] ==  $gid ) 
                return $c;
        }
        return array();
    }
}
?>
