<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de SPCode Class
 * SUPPLIES must IN ( MOBILE, UNICOM, PHS )
 */
class JWSPCode {
    /**
     * SP constant 
     */
    const SP_MOBILE     = 1;
    const SP_UNICOM     = 2;
    const SP_PHS        = 3;
    const SP_UNKNOWN    = 0;

    static private $codeBase = array(
        '9911' => array( 
                    'sp' => self::SP_MOBILE,  
                    'code' => 9911,
                    'gid' => 1,
                    'func' => 8816,
                ),
        '9501' => array(
                    'sp' => self::SP_UNICOM,
                    'code' => 9501,
                    'gid' => 45,
                    'func' => 4567,
                ),
        '50136' => array(
                    'sp' => self::SP_MOBILE,
                    'code' => 50136,
                    'gid' => 85,
                    'func' => 999,
                ),
        '9318' => array(
                    'sp' => self::SP_UNICOM,
                    'code' => 9318,
                    'gid' => 3,
                    'func' => 8816,
                ),
        '99318' => array(
                    'sp' => self::SP_PHS,
                    'code' => 99318,
                    'gid' => 52,
                    'func' => 456,
                ),
        );

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
