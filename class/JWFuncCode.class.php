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
	 * 股票代码 | 股票行业系统，需要股票代码行业关联关系，叽歪机器人指令不从Conference解析，直接使用
	 * JWRobotLingoBase::GetLingoFuncFromFuncCode；为 88|86 指定专门的指令系统
	 */
	const PRE_STOCK_CODE = '88'; 
	const PRE_STOCK_CATE = '86';

	/**
	 * Mms notification
	 */
	const PRE_MMS_NOTIFY = '20';

	/**
	 * Sms Invitation
	 */
	const PRE_REG_INVITE = '30';
	const PRE_REG_INVITE_13 = '13'; //for sms invite
	const PRE_REG_INVITE_15 = '15'; //for sms invite


	/**
	 * D and Reply
	 */
	const PRE_MESSAGE_D = '40';
	const PRE_USER_RE = '50';

	/**
	 * mycaifu stock cooperation
	 */
	const PRE_MYCAIFU_CODE = '08';

	const PRE_VOTE_TP = '66';
	static public $all_pre_id = array( '08', '10','11', '13','15','20','30', '40','50','66','86','88' );

	/**
	 * pre_len
	 */
	const CONST_PRE_LEN = 2;

	static private $serverAddressAlias = array(
		'9911456'  => '991188161199',
		'50136456' => '5013691199',
		'10669911456' => '1066991188161199',
		'10669318456' => '1066931841199',

		'9318456'  => '931888161199',
		'99318456' => '993184561199',
		'9501456'  => '950145671199',
		//'10669318456' => '1066931841199',
		//--
		'10661518456' => '1066151845671199',

		'50136921' => '5013691128006',
		'95016921' => '950145671128006',
		'99318921' => '993184561128006',

		'10669318921' => '1066931891128006',
		'106615186921' => '1066151845671128006',

		//WYXY
		'10669500432' => '106695001199',
		'10669500431' => '106695001128006',
	);

	static public function GetCodeFunc($mobileNo, $id, $pre=self::PRE_MMS_NOTIFY) {
		$id = JWDB::CheckInt( $id );

		$postFunc = $pre . $id;

		$code = JWSPCode::GetCodeByMobileNo( $mobileNo );

		if( empty($code)  )
			return null;

		return $code['code'] . $code['func'] . $postFunc;
	}

	static public function GetMmsNotifyFunc($mobileNo, $idStatus) {
		return self::GetCodeFunc( $mobileNo, $idStatus, self::PRE_MMS_NOTIFY );
	}

	static public function GetSmsInviteFunc($mobile_no, $user_id) {
		$code = JWSPCode::GetCodeByMobileNo( $mobile_no );

		$code_pre = $code['code'] . $code['func'];

		if ( 9 < strlen($code_pre) )
			return $code_pre . self::PRE_REG_INVITE . $user_id;

		$user_device = JWDevice::GetDeviceRowByUserId( $user_id );
		if ( false==empty($user_device) && isset($user_device['sms']) )
		{
			return $code_pre . $user_device['sms']['address'];
		}

		return $code_pre . self::PRE_REG_INVITE . $user_id;
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
		$user_id = self::FetchFuncId( $serverAddress, $mobileNo, self::PRE_REG_INVITE );
		if ( $user_id ) 
			return $user_id;

		$id = self::FetchFuncId( $serverAddress, $mobileNo, self::PRE_REG_INVITE_13 );
		if ( $id )
		{
			$sender_mobile = '13' . $id;
			$device_row = JWDevice::GetDeviceDbRowByAddress($sender_mobile, 'sms');
			if ( false==empty($device_row) )
				return $device_row['idUser'];
		}

		$id = self::FetchFuncId( $serverAddress, $mobileNo, self::PRE_REG_INVITE_15 );
		if ( $id )
		{
			$sender_mobile = '15' . $id;
			$device_row = JWDevice::GetDeviceDbRowByAddress($sender_mobile, 'sms');
			if ( false==empty($device_row) )
				return $device_row['idUser'];
		}
		return null;
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
			if( strlen($funcCode) > self::CONST_PRE_LEN )
			{
				if( preg_match('/^([\d]{'.self::CONST_PRE_LEN.'})(\d+)$/', $funcCode, $matches ) )
				{
					if ( in_array( $matches[1], self::$all_pre_id ) )
					{
						return array( 'pre' => $matches[1], 'id' => $matches[2], );
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
			case self::PRE_STOCK_CODE:
			case self::PRE_STOCK_CATE:
			case self::PRE_CONF_IDUSER:
				{
					$idUser = $preAndId['id'];
					$userInfo = array();
					if( $preAndId['pre'] == self::PRE_STOCK_CODE 
							|| $preAndId['pre'] == self::PRE_STOCK_CATE ) {
						$userInfo = JWUser::GetUserInfo($preAndId['id'], null, 'nameScreen');
					}
					else
					{
					$userInfo = JWUser::GetUserInfo($idUser);
				}

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
