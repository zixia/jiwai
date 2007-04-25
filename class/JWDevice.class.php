<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Device Class
 */
class JWDevice {
	/**
	 * Instance of this singleton
	 *
	 * @var JWDevice
	 */
	static private $instance__;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWDevice
	 */
	static public function &instance($db_config)
	{
		if (!isset(self::$instance__)) {
			$class = __CLASS__;
			self::$instance__ = new $class;
		}
		return self::$instance__;
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
	}


	static function is_valid( $address, $type )
	{
		if ( strlen($address) > 64 ){ // too long
			JWDebug::trace("device: address[$address] too long");
			return false;
		}

		switch ( $type ){
			case 'sms':
				return preg_match('/^\+?\d+$/',$address);
			case 'qq':
				return preg_match('/^\d+$/',$address);
			case 'msn':		
				// im check email address
			case 'gtalk':	
				// im check email address
			case 'jabber':
				return JWUser::IsValidEmail($address,true);
			default:
				JWDebug::trace("unsupport device address type[$type]");
				return false;
		}
		//XXX unreachable
		return false;
	}

	/*
	 * @return array ( 'idUser'			=> 1
     *					, 'secret'	=>	'zixia' );
	 */
	static public function GetUserStateFromDevice( $address, $type )
	{
		$address	= JWDB::escape_string($address);
		$type		= JWDB::escape_string($type);

		$sql = <<<_SQL_
SELECT	idUser,secret
FROM	Device
WHERE	address='$address' AND type='$type'
LIMIT	1;
_SQL_;
		return JWDB::get_query_result ($sql);
	}


	static public function GetDeviceInfo( $idUser )
	{
		if ( ! is_numeric($idUser) ){
			return null;
		}

		$sql = <<<_SQL_
SELECT	id as idDevice,address,type,secret
FROM	Device
WHERE	idUser=$idUser
LIMIT	2;
_SQL_;
		if ( ! $listDeviceInfo = JWDB::get_query_result ($sql, true) ){
			$listDeviceInfo = array();
		}

		$aDeviceInfo = array();

		foreach ( $listDeviceInfo as $v ){
			if ( $v['type'] === 'sms' ){
				$aDeviceInfo['sms'] = $v;
				$aDeviceInfo['sms']['verified'] = empty($v['secret']);
			}else{ // qq/msn/gtalk/jaber...
				$aDeviceInfo['im'] = $v;
				$aDeviceInfo['im']['verified'] = empty($v['secret']);
			}
		}

		return $aDeviceInfo;
	}

	static public function del( $idDevice )
	{
		if ( !is_numeric($idDevice) )
			return false;


		$sql = <<<_SQL_
DELETE FROM	Device
WHERE		id=$idDevice
_SQL_;

		$result = JWDB::Execute($sql) ;
		return !empty( $result );
	}

	/*
	 *	建立用户的 Device 信息，并设置激活码
	 * @return 
			true: 成功 
			false: 已经被占用 
			null: 非法address/type
	 */
	static public function Create( $idUser, $address, $type )
	{
		if ( ! self::is_valid($address,$type) ){
			return null;
		}

		// 存在，并且验证已经通过(secret='')
		if ( JWDB::ExistTableRow('Device', array (	'type'		=> $type
												, 'address'	=> $address
												, 'secret'	=> ''
											)
							) )
		{
			return false;
		}
		
		$secret = self::GenSecret();

		// 慎用 REPLACE，会改变主键值！(replace = delete & insert)
		// 使用REPLACE的原因：如果有其他用户误填写了地址，需要帮助用户更新到自己名下。
		$sql = <<<_SQL_
REPLACE Device
SET 	idUser=$idUser
		, type='$type'
		, address='$address'
		, secret='$secret'
_SQL_;

		try
		{
			// 如果已经存在 $address / $type，会和uniq key冲突，产生exception
			$result = JWDB::Execute($sql) ;
		}
		catch(Exception $e)
		{
			return false;
		}

		return true;
	}


	static function GenSecret($len=6)
	{
		$secret = '';
		for ($i = 0; $i < $len;  $i++) {
			//$secret .= chr(rand(65, 90));
			$secret .= chr(rand(97, 122));
		}
		return $secret;
	}


	static function Verify($address, $type, $secret)
	{
		//XXX MySQL 5.0 比较英文字符的时候忽略大小写
		$is_exist = JWDB::ExistTableRow('Device',array(	'address'	=> $address
													,'type'		=> $type
													,'secret'	=> $secret
								) );

		$ret = false;

		if ( $is_exist ) // Verify PASS
		{
			$sql = <<<_SQL_
UPDATE	Device
SET		secret=''
WHERE	address='$address' AND type='$type'
_SQL_;

			$query = JWDB::Execute($sql);
			if ( empty($query) )
			{
				throw new JWException("update address[$address] type[$type] secret[$secret] fail!");
			}

			$ret = true;
		}
		else // Verify FAIL
		{
			$ret = false;
		}

		echo "JWDevice::Verify([$address],[$type],[$secret]) " + $ret?'SUCC':'FAIL' + "\n";

		return $ret;
	}

	
	/*
	 * @param	string	Mobile NO
	 * @return	int		SP Name
	 *					0: 不祥 1: 移动 2: 联通 3: 小灵通
	 * 					FIXME: use define here, such as JWDevice::CHINAMOBILE
	 */
	static function GetMobileSP($mobileNo)
	{
		if ( preg_match('/^13[4-9]\d+$/',$mobileNo ) 
				|| preg_match('/^159\d+$4/',$mobileNo)
				)
			return 1;

		if ( preg_match('/^13[0-3]\d+$/',$mobileNo ) )
			return 2;

		if ( preg_match('/^\d{8}$/',$mobileNo ) )
			return 3;

		return 0;
	}

	/*
	 * @param	string	Mobile No
	 * @return 	string	SP No
	 */
	static function GetMobileSpNo($mobileNo)
	{
		switch ( JWDevice::GetMobileSP($mobileNo) )
		{
			case 0: return '99118816(移动) / 93188816(联通)';
			case 1: return '99118816';
			case 2: return '93188816';
			case 3: return '暂时尚不支持小灵通';
			default: return '99118816(移动) / 93188816(联通)';
		}
	}
}
?>
