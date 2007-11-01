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


	/*
	 *					0: 不祥 1: 移动 2: 联通 3: 小灵通
	 */
	const SP_UNKNOWN 	= 0;
	const SP_CHINAMOBILE 	= 1;
	const SP_UNICOM		= 2;
	const SP_PAS		= 3;

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


	static function IsValid( &$address, $type )
	{
		if ( strlen($address) > 64 ){ // too long
			JWLog::Instance()->Log(LOG_CRIT, "device: address[$address] too long");
			return false;
		}

		switch ( $type ){
			case 'sms':
				return preg_match('/^\d{11}$/',$address) or preg_match('/^0\d{10,11}$/', $address);
			case 'qq':
				return preg_match('/^\d+$/'				,$address);
			case 'newsmth':
				return preg_match('/^\w+@newsmth.net$/'	,$address);
			case 'yahoo':
                // Strip the yahoo.com(|.(cn|hk|tw|...)) suffix
				if (JWUser::IsValidEmail($address, true)) {
                    list($address) = split('@', $address);
                }
                return preg_match('/^[\w\.\-_]+$/', $address);
			case 'skype':
				return preg_match('/^[\w\.\-_]+$/', $address);
			case 'msn':		
				// im check email address
			case 'gtalk':	
				// im check email address
			case 'jabber':
				// im check email address
			case 'email':
				// email check email address，为了兼容邮件检查，Device表中没有这种类型
				return JWUser::IsValidEmail($address,true);
			case 'web':
			case 'wap':
			case 'api':
			case 'facebook':
				return true;
            case 'all':
                if(false != self::IsValid($address, 'sms'))
                {
                    return true;
                }
                else if(false != self::IsValid($address, 'qq'))
                {
                    return true;
                }
                else if(false != self::IsValid($address, 'msn'))//gtalk, jabber, email
                {
                    return true;
                }
                else if(false != self::IsValid($address, 'skype'))
                {
                    return true;
                }
                else if(false != self::IsValid($address, 'yahoo'))
                {
                    return true;
                }
                else if(false != self::IsValid($address, 'newsmth'))
                {
                    return true;
                }
                else
                {
                    return false;
                }

			default:
				JWLog::Instance()->Log(LOG_CRIT, "unsupport device address type[$type]");
				return false;
		}
		//XXX unreachable
		throw new JWException('unreachable');
	}

	/*
	 * @return array 	$device_db_row;
	 */
	static public function GetDeviceDbRowByAddress( $address, $type )
	{

		if( self::IsAllowedNonRobotDevice($type) ) {
			return array(
				'idUser' => $address,
				'secret' => '',
				'type' => $type,
				'idDevice' => 0,
			);
		}

		$device_ids = JWDevice::GetDeviceIdsByAddresses(	
								array( 
									array('address'=>$address,'type'=>$type ),
								));

		$device_db_row 	= array();

		if ( ! empty($device_ids) ) 
		{
			$device_id	= array_shift($device_ids);
			$device_db_row	= JWDevice::GetDeviceDbRowById($device_id);
		}

		return $device_db_row;
	}


	/*
	 *	批量处理用户的 device 信息，返回一个较为复杂结构的数组，结构如下
	 *	@return 	device_info
	 *					[$idUser][$type]
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

		$condition_in = JWDB::GetInConditionFromArray($idUsers, 'int');

		$sql = <<<_SQL_
SELECT	*,id as idDevice
FROM	Device
WHERE	idUser IN ( $condition_in )
_SQL_;

		if ( ! $db_rows = JWDB::GetQueryResult ($sql, true) ){
			$db_rows = array();
		}

		$device_rows = array();

		foreach ( $db_rows as $db_row )
		{
			$user_id 	= $db_row['idUser'];
			$type 		= $db_row['type'];

			if ( empty($db_row['enabledFor']) )
				$db_row['enabledFor'] = 'nothing';


			$device_rows[$user_id][$type] 				= $db_row;
			$device_rows[$user_id][$type]['verified'] 	= empty($db_row['secret']);
		}

		return $device_rows;
	}


	/*
	 *	@return 	device_info
	 *					[type][idDevice]
	 *					[type][address]
	 *					[type][secret]
	 *					[type][verified]
						[type][enabledFor]
	 */
	static public function GetDeviceRowByUserId( $idUser )
	{
		$device_address_rows = JWDevice::GetDeviceRowsByUserIds( array($idUser) );

		if ( empty($device_address_rows) )
			return array();

		return $device_address_rows[$idUser];
	}

	static public function Destroy( $idDevice )
	{
		$idDevice = JWDB::CheckInt($idDevice);

		return JWDB::DelTableRow("Device", array('id'=>$idDevice));
	}

	/*
	 *	建立用户的 Device 信息，并设置激活码
	 * @return 
			int(>0): 成功, 为新建立的 device_id
			false: 已经被占用 
			null: 非法address/type
	 */
	static public function Create( $idUser, $address, $type, $isVerified=false )
	{
		if ( ! self::IsValid($address,$type) ){
			return null;
		}

		// 存在，并且验证已经通过(secret='')
		if ( self::IsExist($address,$type,true) ){
			return false;
		}
		
		// 建立的时候可以指定免验证
		if ( $isVerified ) {
		 	$secret = '';
		} else {
			switch ($type)
			{
				case 'sms':

					/*
					 *	保证不生成全部为 0 的验证码：在移动方面，这个验证码是退订的指令
					 */
					do { 
						$secret = self::GenSecret(4,JWDevice::CHAR_NUM);
					} while ( preg_match('/^0+$/',$secret) );

					break;
				default:
					$secret = self::GenSecret();
					break;
			}
		}

		try
		{
			// 如果已经存在 $address / $type，会和uniq key冲突，产生exception
			JWDB::DelTableRow('Device',array( 
						'type' => $type ,
						'address' => $address,
						));

			JWDB::SaveTableRow('Device',array(
						'idUser' => $idUser,
						'type' => $type,
						'address' => $address,
						'secret' => $secret,
						'timeCreate' => JWDB::MysqlFuncion_Now(),
						));
		}
		catch(Exception $e)
		{
			return false;
		}

		return JWDB::GetInsertedId();
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


	/*
	 *
		FIXME: 不应该返回 idUser，应该返回 idDevice
	 *	@return 	int	idUser	成功返回 idUser，失败返回 false;
	 */
	static function Verify($address, $type, $secret, $idUser=0)
	{
		//XXX MySQL 5.0 比较英文字符的时候忽略大小写
		if ($idUser) {
			$device_row = JWDB::GetTableRow('Device',array(	
						'idUser' => $idUser,
						'type' => $type,
						'secret' => $secret,
						));
		} else {
			$device_row = JWDB::GetTableRow('Device',array(
						'address' => $address,
						'type' => $type,
						'secret' => $secret,
						));
		}
		$ret = false;

		if ( !empty($device_row) ) // Verify PASS
		{
			$ret = JWDB::UpdateTableRow('Device', intval($device_row['id']), array(
										'secret' => '',
										'address' => $address,
										));

		}
		else // Verify FAIL
		{
			$ret = false;
		}

		JWLog::Instance()->Log(LOG_INFO,"JWDevice::Verify([$address],[$type],[$secret]) " + $ret?'SUCC':'FAIL' );

		if ( ! $ret )
			return false;


		return $device_row['idUser'];
	}

	
	/*
	 * @param	string	Mobile NO
	 * @return	int		SP Name
	 */
	static function GetMobileSP($mobileNo)
	{
		if ( preg_match('/^13[4-9]\d{8}$/',$mobileNo ) 
				|| preg_match('/^15[0-9]\d{8}$/',$mobileNo)
				)
			return self::SP_CHINAMOBILE;

		if ( preg_match('/^13[0-3]\d{8}$/',$mobileNo ) )
			return self::SP_UNICOM;

		if ( preg_match('/^\d{11,12}$/',$mobileNo ) )
			return self::SP_PAS;

		return self::SP_UNKNOWN;
	}

	/*
	 * @param	string	Mobile No
	 * @return 	string	SP No
	 */
	static function GetMobileSpNo($mobileNo)
	{
		switch ( JWDevice::GetMobileSP($mobileNo) )
		{
			case self::SP_CHINAMOBILE: 	
			{
				$code = JWMobile::GetSpCode( $mobileNo, null );
				if( false == empty( $code ) ){
					return $code['code'] . $code['func'] . $code['funcPlus'];
				}
				return '99118816';
			}
			case self::SP_UNICOM:
			{
				$code = JWMobile::GetSpCode( $mobileNo, null );
				if( false == empty( $code ) ){
					return $code['code'] . $code['func'] . $code['funcPlus'];
				}
				return '95014567';
			}
			case self::SP_PAS:
			{
				$code = JWMobile::GetSpCode( $mobileNo, null );
				if( false == empty( $code ) ){
					return $code['code'] . $code['func'] . $code['funcPlus'];
				}
				return '99318456';
			}
			case self::SP_UNKNOWN: 
			default:
				return '99118816(移动) / 95014567(联通) / 99318456(小灵通)';
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
		$device_id = JWDevice::GetDeviceIdByAddress( array('address'=>$address,'type'=>$type) );

		if ( empty($device_id) )
			return false;

		if ( ! self::IsValid($address,$type) ){
			return null;
		}

		$device_db_row = JWDevice::GetDeviceDbRowById($device_id);

		// 用户都不在了，删之
		if ( empty($device_db_row['idUser']) )
		{
			JWDevice::Destroy($device_id);
			return false;
		}

		// 参数要求已经激活，但是记录中的验证码还没有验证过（验证过应该为空）
		if ( $isActive && !empty($device_db_row['secret']) )
			return false;

		return true;
	}


	static public function IsUserOwnDevice($idUser, $idDevice)
	{
		$device_row = JWDevice::GetDeviceDbRowById($idDevice);

		if ( empty($device_row) )
			return false;

		return $idUser==$device_row['idUser'];
	}

	static public function GetDeviceEnableFor($idDevice)
	{
		$device_row = JWDevice::GetDeviceDbRowById($idDevice);

		if ( empty($device_row) )
			return 'nothing';

		$enabled_for	= $device_rows['enabledFor'];
			
		if ( empty($enabled_for) )
			$enabled_for = 'nothing';

		return $enabled_for;
	}


	static public function SetDeviceEnabledFor($idDevice, $enabledFor, $isSignatureRecord=null)
	{
		$idDevice	= JWDB::CheckInt($idDevice);
        $device_row	= JWDevice::GetDeviceDbRowById($idDevice);

		switch ( $enabledFor )
		{
			case 'everything':
				$device_row	= JWDevice::GetDeviceDbRowById($idDevice);
				$user_id 	= $device_row['idUser'];
				$type		= $device_row['type'];

				JWUser::SetSendViaDevice($user_id, $type);

				break;

			case 'direct_messages':
				break;

			case 'nothing':
				break;

			default:
				$enabledFor = '';
				break;
		}

        $updateRow = array();
        if( false == empty( $isSignatureRecord ) )
            $updateRow['isSignatureRecord']  = $isSignatureRecord;
        if( false == empty( $enabledFor ) )
            $updateRow['enabledFor']  = $enabledFor;

        if( empty( $updateRow ) )
            return true;

		return JWDB::UpdateTableRow('Device', $idDevice, $updateRow );
	}


	/*
	 *	根据 idDevice 获取 Row 的详细信息
	 *	@param	array	idDevices
	 * 	@return	array	以 idDevice 为 key 的 device row
	 * 
	 */
	static public function GetDeviceDbRowsByIds( $idDevices)
	{
		if ( empty($idDevices) )
			return array();

		if ( !is_array($idDevices) )
			throw new JWException('must array');

		$condition_in = JWDB::GetInConditionFromArray($idDevices);

		$sql = <<<_SQL_
SELECT	*, id as idDevice
FROM	Device
WHERE	id IN ($condition_in)
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);


		$device_map = array();

		if ( empty($rows) )
			return $device_map;

		foreach ( $rows as $row )
			$device_map[$row['idDevice']] 	= $row;


		return $device_map;
	}

	static public function GetDeviceDbRowById($idDevice)
	{
		$device_rows = JWDevice::GetDeviceDbRowsByIds(array($idDevice));

		if ( empty($device_rows) )
			return array();

		return $device_rows[$idDevice];
	}

	/*
	 *	根据 array(array('address'=>'','type'=>''),...) 获取 DeviceRow
	 *
	 *	@param	array		$addresses	array(array('address'=>'','type'=>''),...) 
	 *
	 *	@return	array		$device_ids
	 */
	static public function GetDeviceIdsByAddresses( $addresses )
	{
		if ( empty($addresses) )
			return array();

		if ( !is_array($addresses) )
			throw new JWException('must array');

		$condition_in = JWDB::GetInConditionFromArrayOfArray($addresses, array('address','type'), 'char');

		$sql = <<<_SQL_
SELECT	id as idDevice
FROM	Device
WHERE	(address,type) IN ($condition_in)
_SQL_;

		try {
			$rows = JWDB::GetQueryResult($sql,true);
		} catch ( Exception $e ) {
			$sql_string = preg_replace("/\n/"," ",$sql);
			JWLog::LogFuncName(LOG_CRIT, "JWDB::GetQueryResult($sql_string,true) exception " . $e->getMessage());
			return array();
		}

		if ( empty($rows) )
			return array();


		$device_ids = array();
		foreach ( $rows as $row )
			array_push($device_ids, $row['idDevice']);


		return $device_ids;
	}

	/*
	 *	@param	array	$address	array('address'=>'','type'=>'');
	 *
	 *	@return	array	$device_ids
	 */
	static public function GetDeviceIdByAddress($address)
	{
		$device_ids = JWDevice::GetDeviceIdsByAddresses( array($address) );

		if ( empty($device_ids) )
			return array();

		return array_pop($device_ids);
	}

	/*
	 *	根据 idDevices 获取 以 type->address 方式哈希的 device_row 数组
	 *
	 *	@param	array		$Ids	idDevices
	 *
	 *	@return	array		$device_rows_by_address	ie. $device_row = $device_rows_by_address['msn']['zixia@zixia.net']
								type
									address
										device_row
	 */
	static public function GetDeviceAddressRowsByIds( $idDevices )
	{
		if ( empty($idDevices) )
			return array();

		if ( !is_array($idDevices) )
			throw new JWException('must array');

		$device_rows 	= JWDevice::GetDeviceDbRowsByIds($idDevices);


		$device_address_rows = array();
		foreach ( $device_rows as $device_row )
			$device_address_rows[$device_row['type']][$device_row['address']] = $device_row;

		
		return $device_address_rows;
	}

	static public function GetDeviceInfoByAddress($key, $type='sms', $field=null){
		settype($type, 'array');
		$in_type_string = implode("','", $type);

		$sql = <<<_SQL_
SELECT *
FROM Device
WHERE address='$key' and type in ('$in_type_string');
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);

		if( $field ) {
			$field_array = array();

			if( empty($rows) || false == isset($rows[0][$field] ) )
				return array();

			foreach ( $rows as $row )
			{
				array_push($field_array,$row['idUser']);
			}
			return $field_array;
		}

		return $rows;
	}


	/*
	 *	根据 Device Type 返回机器人帐号
	 */
	static public function GetRobotFromType($type, $address=null)
	{
		switch ( $type )
		{
			case 'sms':
				if( $address ) {
					$code = JWMobile::GetSpCode( $address, null );
					if( false == empty( $code ) ){
						$name = $code['code'] . $code['func'];
					}
				}
				if( false == isset( $name ) ) {
					$name='99118816(移动) 83188816(联通)';
				}
				break;
			case 'newsmth':
				$name='JiWai';
				break;
			case 'qq':
				$name='229516989';
				$name='742721768';
				break;
			case 'skype':
				$name='wo.jiwai.de';
				break;
			case 'yahoo':
				$name='jiwai001';
				break;
			case 'msn':
				$row = null;
				if( false == empty($address) ) {
					$shortcut = "$type:$address";
					$row = JWIMOnline::GetDbRowByShortcut( $shortcut );
				}
				if( empty( $row ) ){
					$name = JWIMOnline::GetServerAddressByType($type);
					if( false == empty($address) ) {
						$shortcut = "$type:$address";
						JWIMOnline::SetIMOnline( $address, $type, $name, 'OFFLINE' );
					}
				} else {
					$name = $row['serverAddress'];
				}
				break;
			default:
				$name='wo@jiwai.de';
		}
		return $name;
	}


	/*
	 *	根据 Device Type 返回好看的字符串
	 */
	static public function GetNameFromType($type, $idPartner=null)
	{
		if( intval( $idPartner ) ){
			if( $partner = JWPartner::GetDbRowById( intval($idPartner) ) ) {
				if( $partner['link'] ) {
					return "<a href='$partner[link]' target='_blank'>$partner[nameDevice]</a>";
				}else{
					return $partner['nameDevice']; 
				}
			}
		}
		switch ( $type )
		{
			case 'sms':
				$name='手机';
				break;
			case 'gtalk':
				$name='GTalk';
				break;
			case 'jabber':
				$name='Jabber';
				break;
			case 'newsmth':
				$name='水木社区';
				break;
			case 'facebook':
				$name='Facebook';
				break;
			case 'web':
				$name='网页';
				break;
			case 'skype':
				$name='Skype';
				break;
			case 'yahoo':
				$name='Yahoo!';
				break;
			default:
				$name=strtoupper($type);
		}

		return $name;
	}
	
	/**
	  * 检查签名是否一次合法需被记录的更新
	  */
	static public function IsSignatureChanged($idUser, $device, $status){
		//Sinature logic
		if( self::IsSignatureRecordDevice($device) ){

			$device_row = JWDevice::GetDeviceRowByUserId( $idUser );

			$device_data = isset($device_row[$device]) ? $device_row[$device] : null;

			if ( $device_data['isSignatureRecord'] != 'Y' )
				return false;

			$status = JWStatus::HtmlEntityDecode( $status );

			if( false == empty( $device_data ) 
					&& strncasecmp($device_data['signature'],$status,140)
					&& false == self::IsHistorySignature( $idUser, $status )
			  )
			{
				JWDB::UpdateTableRow('Device', intval($device_data['idDevice']), array(
							'signature'=>$status
							));
				return true;
			}
		}
		return false;
	}

	/*
	 *	检查 device 是否已经由用户验证激活
	 */
	static public function IsActived($idDevice)
	{
		$device_db_row = JWDevice::GetDeviceDbRowById($idDevice);
		return empty($device_db_row['secret']);
	}

	/*
	 * 允许执行指令的非Robot设备
	 */
	static public function IsAllowedNonRobotDevice($type='web'){
		return in_array( $type, array( 'web', 'wap', 'api' ) );	
	}

	/*
	 * 允许记录签名的设备
	 */
	static public function IsSignatureRecordDevice($type='msn'){
		return in_array( $type, array( 'msn', 'gtalk', 'qq', 'skype' ) );	
	}

	static public function GetSupportedDeviceTypes()
	{
		return array ( 'sms', 'qq' ,'msn' ,'gtalk', 'skype', 'newsmth', 'facebook', 'yahoo' /*, 'jabber'*/ );
	}

	static public function IsHistorySignature($idUser, $signature){
		$idUser = JWDB::CheckInt( $idUser );
		$signature = trim( $signature );
		$md5Value = md5( $signature );

		$sigKey = JWDB_Cache::GetCacheKeyByFunction( array( 'JWDevice', 'IsHistorySignature'), array($idUser) );
		$memcache = JWMemcache::Instance();
		$result = $memcache->Get( $sigKey );
		
		$isHistory = false;
		if( false == $result ) {
			$row = array( $md5Value );
			$memcache->Set( $sigKey, $row );
		}else if( is_array( $result ) ){
			if( in_array( $md5Value, $result ) ) {
				$isHistory = true;
			} else{
				array_push( $result, $md5Value );
				$memcache->Set( $sigKey, $result );
			}
		}

		return $isHistory;
	}

	/*
	 * 获取设备分类 [ Conference 用 ]
	 */
	static public function GetDeviceCategory( $type='sms' ) {
		switch( strtolower($type) ) {
			case 'web':
			case 'wap':
				return 'web';
			case 'msn':
			case 'gtalk':
			case 'qq':
			case 'skype':
			case 'yahoo':
				return 'im';
			case 'sms':
				return 'sms';
			default:
				return 'other';
		}
	}
}
?>
