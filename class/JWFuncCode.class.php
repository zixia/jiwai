<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de FuncCode Class
 * SUPPLIES must IN ( MOBILE, UNICOM, PAS )
 */
class JWFuncCode {

	/**
	 * Conference number/iduser
	 */
	const PRE_CONF_CUSTOM = '10';
	const PRE_CONF_IDUSER = '11';

	/**
	 * Mms notification
	 */
	const PRE_MMS_NOTIFY = '20';

	static private $serverAddressAlias = array(
			'9911456'  => '991188161199',
			'50136456' => '5013691199',

			'9318456'  => '931888161199',
			'99318456' => '993184561199',
			'9501456'  => '950145671199',

			'50136921' => '5013691128006',
			'95016921' => '950145671128006',
			'99318921' => '993184561128006',
		);

	static public function GetMmsNotifyFunc($mobileNo, $idStatus) {
		$idStatus = JWDB::CheckInt( $idStatus );

		$postFunc = self::PRE_MMS_NOTIFY . $idStatus;

		$code = JWSPCode::GetCodeByMobileNo( $mobileNo, true );

		if( empty($code)  )
			return null;

		return $code['code'] . $code['func'] . $postFunc;
	}

	static public function FetchMmsIdStatus($serverAddress, $mobileNo){
		//parse alias
		if( isset( self::$serverAddressAlias[ $serverAddress ] ) )
			$serverAddress = self::$serverAddressAlias[ $serverAddress ];

		$code = JWSPCode::GetCodeByServerAddressAndMobileNo( $serverAddress, $mobileNo );
		if( empty($code) )
			return null;

		$preCode = $code['code'] . $code['func'];

		if( 0 === strpos( $serverAddress, $preCode ) )
		{
			$funcCode = substr( $serverAddress, strlen($preCode) );
			if( 0 === strpos( $funcCode, self::PRE_MMS_NOTIFY ) )
			{
				return substr($funcCode, strlen(self::PRE_MMS_NOTIFY) );
			}
		}

		return null;
	}

	static public function FetchConference($serverAddress, $mobileNo){

		//parse alias
		if( isset( self::$serverAddressAlias[ $serverAddress ] ) )
			$serverAddress = self::$serverAddressAlias[ $serverAddress ];

		$code = JWSPCode::GetCodeByServerAddressAndMobileNo( $serverAddress, $mobileNo );
		if( empty($code) )
			return array();

		$preCode = $code['code'] . $code['func'];
		
		//parse serverAddress
		if( 0 === strpos( $serverAddress, $preCode ) ) {
			$funcCode = substr( $serverAddress, strlen($preCode) );

			if( 0 === strpos( $funcCode, self::PRE_CONF_IDUSER ) ){
				$idUser = substr( $funcCode, strlen( self::PRE_CONF_IDUSER ) );
				$userInfo = JWUser::GetUserInfo( $idUser );
				if( empty($userInfo) || null==$userInfo['idConference'] )
					return array();

				$conference = JWConference::GetDbRowById( $userInfo['idConference'] );

				return array(
						'user' => $userInfo,
						'conference' => $conference,
					);
			}

			if( 0 === strpos( $funcCode, self::PRE_CONF_CUSTOM ) ){
				$conferenceNum = substr( $funcCode, strlen( self::PRE_CONF_CUSTOM ) );
				$conference = JWConference::GetDbRowFromNumber( $conferenceNum	);
				if( empty($conference) )
					return array();

				$userInfo = JWUser::GetUserInfo( $conference['idUser'] );
				if( empty($userInfo) || null==$userInfo['idConference'] 
						|| $userInfo['idConference'] != $conference['id'] )
					return array();

				return array(
						'user' => $userInfo,
						'conference' => $conference,
					);
			}
		}
		return array();
	}
}
?>
