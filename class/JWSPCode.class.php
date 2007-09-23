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
	const SP_MOBILE 	= 1;
	const SP_UNICOM 	= 2;
	const SP_PAS		= 3;
	const SP_UNKNOWN	= 0;

	static private $codeBase = array(
		array( 'sp' => self::SP_MOBILE,  'code' => 9911, 'gid' => 1, 
			'func' => 8816, 'funcPlus' => '', 'default' => true,
		),
		array( 'sp' => self::SP_UNICOM, 'code' => 9501, 'gid' => 45, 
			'func' => 4567, 'funcPlus' => '', 'default' => true,
		),
		array( 'sp' => self::SP_MOBILE, 'code' => 50136, 'gid' => 85, 
			'func' => 9, 'funcPlus' => '99', 'default' => false,
		),
		array( 'sp' => self::SP_UNICOM, 'code' => 9318, 'gid' => 3, 
			'func' => 8816, 'funcPlus' => '', 'default' => false,
		),
		array( 'sp' => self::SP_PAS, 'code' => 99318, 'gid' => 52, 
			'func' => 456, 'funcPlus' => '', 'default' => true,
		),
	);

	static public function GetSupplier($supplierString='MOBILE'){
		switch($supplierString){
			case 'UNICOM':
				return self::SP_UNICOM;
			case 'PAS':
				return self::SP_PAS;
			case 'MOBILE':
				return self::SP_MOBILE;
			default:
				return self::SP_MOBILE;
		}
	}

	static public function GetCodeByCodeNumAndSupplier( $codeNum, $supplier=self::SP_MOBILE ) {
		
		if( false == is_numeric($supplier) )
			$supplier = self::GetSupplier( $supplier );

		$code = array();
		foreach( self::$codeBase as $c ){
			if( $c['sp'] == $supplier && ( $codeNum==null || $c['code'] == $codeNum ) )
			{
				if ( isset($c['default']) && $c['default']==true ) 
				{
					return $c;
				}
				$code = $c;
			}
		}
		return $code;
	}

	static public function GetCodeByServerAddressAndMobileNo($serverAddress, $mobileNo, $forceFunc=true) {
		$supplier = self::GetSupplierByMobileNo( $mobileNo );
		return self::GetCodeByServerAddressAndSupplier( $serverAddress, $supplier , $forceFunc );
	}

	static public function GetCodeByServerAddressAndSupplier($serverAddress,$supplier=self::SP_MOBILE,$forceFunc=true){

		if( false == is_numeric($supplier) )
			$supplier = self::GetSupplier( $supplier );

		foreach( self::$codeBase as $c ){
			$preCode = strval( ($forceFunc == true) ? $c['code'].$c['func'] : $c['code'] );
			if( $c['sp'] == $supplier && 0 === strpos($serverAddress, $preCode) ){
				return $c;
			}
		}
		return array();
	}

	static public function GetCodeBySupplier( $supplier=self::SP_MOBILE ) {
		return self::GetCodeByCodeNumAndSupplier( null, $supplier );
	}

	/**
	 * if $needForce==true, use mobileRow['force'] forceCode to sent mt message
	 */
	static public function GetCodeByMobileNo( $mobileNo , $needForce=true) {

		$supplier = self::GetSupplierByMobileNo( $mobileNo );

		if( $needForce == true )
			$mobileRow = JWMobile::GetDbRowByMobileNo( $mobileNo );
		else 
			$mobileRow = array();

		if( empty( $mobileRow ) )
			return self::GetCodeByCodeNumAndSupplier( null, $supplier );

		return self::GetCodeByCodeNumAndSupplier( $mobileRow['forceCode'], $supplier );
	}

	static public function GetCodeByGid( $gid ) {

		$gid = JWDB::CheckInt( $gid );

		foreach( self::$codeBase as $c )
		{
			if( $c['gid'] ==  $gid ) 
				return $c;
		}
		return array();
	}

	static public function GetSupplierByMobileNo( $mobileNo ) {
		if ( preg_match('/^13[4-9]\d{8}$/',$mobileNo ) 
				|| preg_match('/^15[0-9]\d{8}$/',$mobileNo)
		){
			return self::SP_MOBILE;
		}

		if ( preg_match('/^13[0-3]\d+$/',$mobileNo ) ) {
			return self::SP_UNICOM;
		}

		if ( preg_match('/^\d{11,12}$/',$mobileNo ) ) {
			return self::SP_PAS;
		}

		return self::SP_UNKNOWN;
	}
}
?>
