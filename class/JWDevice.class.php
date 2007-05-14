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

	/*
	 * GenSecret 使用的参数：字母、数字、全部
	 */
	const	CHAR_WORD	= 1;
	const	CHAR_NUM	= 2;
	const	CHAR_ALL	= 3;

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


	static function IsValid( $address, $type )
	{
		if ( strlen($address) > 64 ){ // too long
			JWLog::Instance()->Log(LOG_CRIT, "device: address[$address] too long");
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
				// im check email address
			case 'email':
				// email check email address，为了兼容邮件检查，Device表中没有这种类型
				return JWUser::IsValidEmail($address,true);
			default:
				JWLog::Instance()->Log(LOG_CRIT, "unsupport device address type[$type]");
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
		return JWDB::GetQueryResult ($sql);
	}


	/*
	 *	批量处理用户的 device 信息，返回一个较为复杂结构的数组，结构如下
	 *	@return 	device_info
	 *					[$idUser][sms]
	 *					[$idUser][im]
										[idDevice]	int
	 *									[address]	string
	 *									[secret]	string
	 *									[verified]	bool
										[enabledFor]	string
	 */
	static public function GetDeviceRowsByUserIds( $idUsers )
	{
		if ( empty($idUsers) )
		{
			JWLog::Log(LOG_ERR, 'JWDevice::GetDeviceRowsByUserIds([$idUsers]) got empty param');
			return array();
		}

		$in_condition = JWDB::GetInConditionFromArray($idUsers, 'int');

		$sql = <<<_SQL_
SELECT	*,id as idDevice
FROM	Device
WHERE	idUser IN ( $in_condition )
_SQL_;

		if ( ! $db_rows = JWDB::GetQueryResult ($sql, true) ){
			$db_rows = array();
		}

		$device_rows = array();

		foreach ( $db_rows as $db_row )
		{
			$user_id = $db_row['idUser'];

			if ( empty($db_row['enabledFor']) )
				$db_row['enabledFor'] = 'nothing';

			// 按照 sms / msn / gtalk 等进行分类数组存放
			if ( $db_row['type'] === 'sms' ){
				$device_rows[$user_id]['sms'] 				= $db_row;
				$device_rows[$user_id]['sms']['verified'] 	= empty($db_row['secret']);
			}else{ // qq/msn/gtalk/jaber...
				$device_rows[$user_id]['im'] 				= $db_row;
				$device_rows[$user_id]['im']['verified'] 	= empty($db_row['secret']);
			}
		}

		return $device_rows;
	}


	/*
	 *	@desprecited 废弃函数 //FIXME 这个英文怎么拼写？
	 *	@return 	device_info
	 *					[sms][idDevice]
	 *					[sms][address]
	 *					[sms][secret]
	 *					[sms][verified]
						[sms][enabledFor]
	 *				so as [im]
	 */
	static public function GetDeviceInfo( $idUser )
	{
		$idUser = intval($idUser);

		if ( 0==$idUser )
			throw new JWException('must int');

		$sql = <<<_SQL_
SELECT	id as idDevice,address,type,secret,enabledFor
FROM	Device
WHERE	idUser=$idUser
LIMIT	2;
_SQL_;
		if ( ! $listDeviceInfo = JWDB::GetQueryResult ($sql, true) ){
			$listDeviceInfo = array();
		}

		$aDeviceInfo = array();

		foreach ( $listDeviceInfo as $v ){
			if ( empty($v['enabledFor']) )
				$v['enabledFor'] = 'nothing';

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
		if ( ! self::IsValid($address,$type) ){
			return null;
		}

		// 存在，并且验证已经通过(secret='')
		if ( self::IsExist($address,$type,true) ){
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
		, timeCreate=NOW()
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


	static function GenSecret($len=6, $type=self::CHAR_WORD)
	{
		$secret = '';
		for ($i = 0; $i < $len;  $i++) {
			if ( self::CHAR_NUM==$type ){
				// number
				$secret .= chr(rand(48, 57));
			}else if ( self::CHAR_WORD==$type ){
				// word
				$secret .= chr(rand(97, 122));
			}else{
				if ( 0==$i ){	// 混合时，第一个字符定为 word，为了可以当作 nameScreen
					$secret .= chr(rand(97, 122));
				} else {
					$secret .= (0==rand(0,1)) ? chr(rand(97, 122)) : chr(rand(48,57));
				}
			}
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

		JWLog::Instance()->Log(LOG_INFO,"JWDevice::Verify([$address],[$type],[$secret]) " + $ret?'SUCC':'FAIL' );

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


	/*
	 *	检查 Device 是否被绑定，可以区分已经激活，和未被激活的设备
	 *
	 *	@param	string	$type		Device 的 type - 查找 Device 表
	 *	@param	bool	$isActive	true	只查找已经被激活的
									false	查找所有
		@return	bool	$isExist	是否存在
	 */
	static function IsExist($address, $type, $isActive=true)
	{
		if ( ! self::IsValid($address,$type) ){
			return null;
		}

		$condition = array (	 'address'	=> $address
								,'type'		=> $type
							);
		if ( $isActive ){
			$condition['secret'] = '';
		}

		return JWDB::ExistTableRow('Device', $condition);
	}


	static public function IsUserOwnDevice($idUser, $idDevice)
	{
		$idUser	= JWDB::CheckInt($idUser);
		$idDevice	= JWDB::CheckInt($idDevice);

		return JWDB::ExistTableRow('Device', array (	'id'		=> intval($idDevice)
														,'idUser'	=> intval($idUser)
											) );
	}

	static public function GetDeviceEnableFor($idDevice)
	{
		$idDevice	= JWDB::CheckInt($idDevice);

		$row  		= JWDB::GetTableRow('Device', array('id'=>$idDevice) );

		$enabled_for	= $row['enabledFor'];
			
		if ( empty($enabled_for) )
			$enabled_for = 'nothing';

		return $enabled_for;
	}

	static public function SetDeviceEnableFor($idDevice, $enabledFor)
	{
		$idDevice	= JWDB::CheckInt($idDevice);

		switch ( $enabledFor )
		{
			case 'everything':
				break;
			case 'direct_messages':
				break;
			case 'nothing':
				break;
			default:
				$enabledFor = 'nothing';
				break;
		}

		return JWDB::UpdateTableRow('Device', $idDevice, array('enabledFor'=>$enabledFor));
	}
}
?>
