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

	/**
	 * Mms Invitation
	 */
	const PRE_REG_INVITE = '30';

	/**
	 * pre_len
	 */
	const CONST_PRE_LEN = 2;

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

	static public function GetCodeFunc($mobileNo, $id, $pre=self::PRE_MMS_NOTIFY) {
		$id = JWDB::CheckInt( $id );

		$postFunc = $pre . $id;

		$code = JWSPCode::GetCodeByMobileNo( $mobileNo, true );

		if( empty($code)  )
			return null;

		return $code['code'] . $code['func'] . $postFunc;
	}

	static public function GetMmsNotifyFunc($mobileNo, $idStatus) {
		return self::GetCodeFunc( $mobileNo, $idStatus, self::PRE_MMS_NOTIFY );
	}

	static public function FetchFuncId( $serverAddress, $mobileNo, $pre=self::PRE_MMS_NOTIFY ){

		if( isset( self::$serverAddressAlias[ $serverAddress ] ) )
			$serverAddress = self::$serverAddressAlias[ $serverAddress ];

		$code = JWSPCode::GetCodeByServerAddressAndMobileNo( $serverAddress, $mobileNo );
		if( empty($code) )
			return null;

		$preCode = $code['code'] . $code['func'];

		if( 0 === strpos( $serverAddress, $preCode ) )
		{
			$funcCode = substr( $serverAddress, strlen($preCode) );
			if( 0 === strpos( $funcCode, $pre ) )
			{
				return substr($funcCode, strlen($pre) );
			}
		}

		return null;
	}

	static public function FetchMmsIdStatus($serverAddress, $mobileNo){
		return self::FetchFuncId( $serverAddress, $mobileNo, self::PRE_MMS_NOTIFY );
	}

	static public function FetchRegIdUser($serverAddress, $mobileNo){
		return self::FetchFuncId( $serverAddress, $mobileNo, self::PRE_REG_INVITE );
	}


	static public function FetchPreAndId($serverAddress, $mobileNo){

		if( isset( self::$serverAddressAlias[ $serverAddress ] ) )
			$serverAddress = self::$serverAddressAlias[ $serverAddress ];

		$rtn = array();
		$code = JWSPCode::GetCodeByServerAddressAndMobileNo( $serverAddress, $mobileNo );
		if( empty($code) )
			return $rtn;

		$preCode = $code['code'] . $code['func'];

		if( 0 === strpos( $serverAddress, $preCode ) )
		{
			$funcCode = substr( $serverAddress, strlen($preCode) );
			if( strlen($funcCode) > self::CONST_PRE_LEN ){
				if( preg_match('/^([\d]{'.self::CONST_PRE_LEN.'})(\d+)$/', $funcCode, $matches ) ){
					switch( $matches[1] ) {
						case self::PRE_REG_INVITE:
						case self::PRE_MMS_NOTIFY:
						case self::PRE_CONF_IDUSER:
						case self::PRE_CONF_CUSTOM:
						{
							return array( 'pre' => $matches[1], 'id' => $matches[2], );
						}
						break;
					}
				}
			}
		}

		return $rtn;
	}


	static public function FetchConference($serverAddress, $mobileNo){

		$preAndId = self::FetchPreAndId( $serverAddress, $mobileNo );

		if( empty($preAndId) )
			return array();

		switch( $preAndId['pre'] ){
			case self::PRE_CONF_IDUSER:
			{
				$idUser = $preAndId['id'];
				$userInfo = JWUser::GetUserInfo( $idUser );
				if( empty($userInfo) || null==$userInfo['idConference'] )
					return array();

				$conference = JWConference::GetDbRowById( $userInfo['idConference'] );

				return array(
						'user' => $userInfo,
						'conference' => $conference,
					);
			}
			break;
			case self::PRE_CONF_CUSTOM:
			{
				$conferenceNum = $preAndId['id'];
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
			break;
		}

		return array();
	}
}
?>