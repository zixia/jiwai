<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	seek@jiwai.com
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
		self::SP_MOBILE => array(
			// LinkTone 
			array(
				'code' => '10668228',
				'gid' => '',
				'func' => '66',
				'funcPlus' => '',
				'default' => true,
			),
			array(
				'code' => '1065055',
				'gid' => null,
				'func' => '16889',
				'funcPlus' => '',
				'default' => false,
			),
			array( 
				'code' => '10669911', 
				'gid' => 1, 
				'func' => '8816',
				'funcPlus' => '',
				'default' => false,
			),
			array(
				'code' => '10669500',
				'gid' => 111,
				'func' => '',
				'funcPlus' => '',
				'default' => false,
			),
			// ShiJiZongKai 10605328 
			array(
				'code' => '10605328',
				'gid' => '',
				'func' => '',
				'funcPlus' => '',
				'default' => false,
			),
		),
		self::SP_UNICOM => array(
			array(
				'code' => '10605328',
				'gid' => '',
				'func' => '',
				'funcPlus' => '',
				'default' => false,
			),
			array( 
				'code' => '10661518',
				'gid' => 45, 
				'func' => '4567',
				'funcPlus' => '',
				'default' => false,
			),
			array(
				'code' => '10669500',
				'gid' => 112,
				'func' => '',
				'funcPlus' => '',
				'default' => false,
			),
			// LinkTone 
			array(
				'code' => '10668228',
				'gid' => '',
				'func' => '66',
				'funcPlus' => '',
				'default' => false,
			),
		),
		self::SP_PAS => array(
			array(
				'code' => '10669500',
				'gid' => 113,
				'func' => '',
				'funcPlus' => '',
				'default' => false,
			),
			// LinkTone 
			array(
				'code' => '10668228',
				'gid' => '',
				'func' => '88',
				'funcPlus' => '',
				'default' => false,
			),
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

	static public function GetCodeByServerAddressAndMobileNo( $serverAddress, $mobileNo ) 
	{
		$supplier = self::GetSupplierByMobileNo( $mobileNo );

		if( false == is_numeric($supplier) )
			$supplier = self::GetSupplier( $supplier );

		$code = array();
		if( isset( self::$codeBase[ $supplier ] ) )
		{
			$cs = self::$codeBase[ $supplier ];
			foreach( $cs as $c )
			{
				if( $serverAddress == null ) 
				{
					return $c;
				}
				
				$codeString = strval( $c['code'] );
				if( 0 === strpos($serverAddress, $codeString) )
				{
					return $c;
				}
			}
		}
		return array();
	}

	/**
	 * if $needForce==true, use mobileRow['force'] forceCode to sent mt message
	 */
	static public function GetCodeByMobileNo( $mobileNo ) 
	{
		$mobileNo = strval( $mobileNo );
		$mobileRow = JWMobile::GetDbRowByMobileNo( $mobileNo );
		$serverAddress = empty( $mobileRow ) ? null : $mobileRow['forceCode'] ;

		return self::GetCodeByServerAddressAndMobileNo( $serverAddress, $mobileNo );
	}

	/**
	 * Get Code By Code And MobileNo
	 */
	static public function GetCodeByGidAndMobileNo( $gid, $mobileNo ) 
	{
		$supplier = self::GetSupplierByMobileNo( $mobileNo );

		if( false == is_numeric($supplier) )
			$supplier = self::GetSupplier( $supplier );

		$code = array();
		if( isset( self::$codeBase[ $supplier ] ) )
		{
			$cs = self::$codeBase[ $supplier ];
			foreach( $cs as $c )
			{
				if( $c['gid'] == $gid )
				{
					return $c;
				}
			}
		}
		return array();
	}

	static public function GetSupplierByMobileNo( $mobileNo ) 
	{
		if ( preg_match('/^13[4-9]\d{8}$/',$mobileNo ) 
			|| preg_match('/^15(0|8|9)\d{8}$/',$mobileNo)
		){
			return self::SP_MOBILE;
		}

		if ( preg_match('/^13[0-3]\d+$/',$mobileNo ) 
			|| preg_match('/^15(3|6)\d{8}$/',$mobileNo)
		) {
			return self::SP_UNICOM;
		}

		if ( preg_match('/^\d{11,12}$/',$mobileNo ) ) {
			return self::SP_PAS;
		}

		return self::SP_UNKNOWN;
	}
}
?>
