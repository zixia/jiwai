<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Sms Class
 */
class JWSms {
	/**
	 * Instance of this singleton
	 *
	 * @var JWSms
	 */
	static $msInstance;

	/**
	 * path_config
	 *
	 * @var mQueuePathMo mQueuePathMt
	 */
	static $msQueuePathMo;
	static $msQueuePathMt;


	/**
	 * bad msg here
	 */
	static $msQuarantinePathMo;
	static $msQuarantinePathMt;

	/**
	 * SP GID
	 */
	const GID_CHINAMOBILE 		= 1;   //9911 old china mobile
	const GID_CHINAMOBILE_TWO 	= 85;  //50136 new china mobile
	const GID_UNICOM		= 45;  //9501 new unicom
	const GID_UNICOM_TWO		= 3;   //9318 old unicom
	const GID_PAS			= 52;  //99318
	const GID_UNKNOWN		= 1;



	/**
	 * Instance of this singleton class
	 *
	 * @return JWSms
	 */
	static public function &Instance()
	{
		if (!isset(self::$msInstance)) {
			$class = __CLASS__;
			self::$msInstance = new $class;
		}
		return self::$msInstance;
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
		$config 	= JWConfig::Instance();
		$directory 	= $config->directory;

		self::$msQueuePathMo	= $directory->queue->root 
								. $directory->queue->sms
								. $directory->mo
								;

		self::$msQueuePathMt	= $directory->queue->root 
								. $directory->queue->sms
								. $directory->mt
								;

		self::$msQuarantinePathMo	= $directory->quarantine->root 
									. $directory->quarantine->sms
									. $directory->mo
									;

		self::$msQuarantinePathMt	= $directory->quarantine->root 
									. $directory->quarantine->sms
									. $directory->mt
									;
		self::InitDirectory();

	}


	static function InitDirectory()
	{
		if ( ! file_exists(self::$msQueuePathMo)
				|| ! file_exists(self::$msQueuePathMt)
				|| ! file_exists(self::$msQuarantinePathMo)
				|| ! file_exists(self::$msQuarantinePathMt)
				)
		{
			mkdir(self::$msQueuePathMo,0700,true);
			mkdir(self::$msQueuePathMt,0700,true);
			mkdir(self::$msQuarantinePathMo,0700,true);
			mkdir(self::$msQuarantinePathMt,0700,true);
		}
	
		if ( ! is_writeable(self::$msQueuePathMo) 
				|| !is_writeable(self::$msQueuePathMt)
				|| !is_writeable(self::$msQuarantinePathMo)
				|| !is_writeable(self::$msQuarantinePathMt)
				)
		{
			/*
			echo self::$msQueuePathMo
					 . "<br>\n" . self::$msQueuePathMt
					 . "<br>\n" . self::$msQuarantinePathMo
					 . "<br>\n" . self::$msQuarantinePathMt
					 . "<br>\n" ;
			*/

			throw new JWException("JWSms queue_path not writeable");
		}
		return true;
	}



	/*
	 *
	 * @return true is succeed, false is err
	 */
	static public function ReceiveMo($mobileNo, $serviceNo, $smsMsg, $linkId, $gateId)
	{
		self::Instance();

		$now = strftime("%Y-%m-%d %H:%M:%S",time());

		JWLog::Instance()->Log(LOG_INFO, "$now ReceiveMo: msg [$smsMsg]"
					. " from mobile [$mobileNo] to service [$serviceNo]"
					. " by link [$linkId] through [$gateId]" );
		
		$gateNo = 9911;
		switch( $gateId ){
			case self::GID_UNICOM:
				$gateNo = 9501;
			break;
			case self::GID_UNICOM_TWO:
				$gateNo = 9318;
			break;
			case self::GID_PAS:
				$gateNo = 99318;
			break;
			case self::GID_CHINAMOBILE:
				$gateNo = 9911;
			break;
			case self::GID_CHINAMOBILE_TWO:
				$gateNo = 50136;
			break;
		}

		$robot_msg = new JWRobotMsg();
		$robot_msg->Set($mobileNo, 'sms', $smsMsg, $gateNo.$serviceNo, $linkId);

		$robot_msg->SetFile( self::$msQueuePathMo . $robot_msg->GenFileName() );

		return $robot_msg->Save();
	}

	/*
	 *
	 *
	 */
	static public function SubscribeReport($mobileNo, $isSub, $productId, $gateId)
	{
		self::Instance();

		JWLog::Instance()->Log ( LOG_INFO, "SubscribeReport: mobile [$mobileNo] had $isSub-ed [$productId] @[$gateId]" );
		return true;
	}


	/*
	 *
	 *
	 */
	static public function DeliveReport ($mobileNo, $msgId, $deliveState, $errCode, $gateId)
	{
		self::Instance();

		JWLog::Instance()->Log ( LOG_INFO, "DeliverReport: [$msgId] of [$mobileNo] state [$deliveState], err [$errCode] @[$gateId]" );
		return true;
	}


	/*
	 *
	 *
	 * 9911(chn) & 9318(uni) mt
	 *
	 */
	static public function SendMt ($mobileNo, $smsMsg, $serverAddress='99118816', $linkId=null)
	{
		// 第三方下行接口，只对移动有效
		$MT_HTTP_URL_3RD		= 'http://211.157.106.111:8092/sms/third/submit';

		// 普通下行接口，移动联通小灵通都可以使用。不过要提供 linkId
		$MT_HTTP_URL_LINKID	= 'http://211.157.106.111:8092/sms/submit';

		$gid	= self::GetGidByServerAddressAndNo($serverAddress, $mobileNo); // 移动:1 联通:42

		if( 1 == self::GetLocationByMobileNo( $mobileNo ) ) {
			$gid = self::GID_CHINAMOBILE_TWO;
			$serverAddress = '50136999';
		}

		/* 	
		 *	如果有 linkId，则使用 linkid 参数（移动、联通通用）；
		 *	如果没有 LinkId，则只能给移动用户第三方下行
		 */
		if ( empty($linkId) && $gid == self::GID_CHINAMOBILE )
			$MT_HTTP_URL = $MT_HTTP_URL_3RD;
		else
			$MT_HTTP_URL = $MT_HTTP_URL_LINKID;


		$error_code = array(	0	=> 'HE_ERR_OK'
					, 52	=> 'HE_ERR_MSG'
					, 53	=> 'HE_ERR_USERNUMBER'
					, 54	=> 'HE_ERR_PID'
					, 55	=> 'HE_ERR_MOFLAG'
					, 56	=> 'HE_ERR_GATEWAY'
					, 57	=> 'HE_ERR_MSGTYPE'
					, 58	=> 'HE_ERR_ILLEGAL_IP'
					, 59	=> 'HE_ERR_ILLEGAL_APPID'
					, 124	=> 'HE_ERR_ZIXIA_MOBILE_NO'
				);


		$mt_type = array('MT_TYPE_MO_FIRST' 	=> 0, // MO点播引起的第一条MT消息
				 'MT_TYPE_MO_NOT_FIRST'	=> 1, // MO点播引起的非第一条MT消息
				 'MT_TYPE_NO_MO'	=> 2, // 非MO点播引起的MT消息
				 'MT_TYPE_SYSTEM'	=> 3, // 系统反馈引起的MT消息
				);

		$mt_fee	= array('FEE_FREE'		 =>	0, // 免费消息
				'FEE_NORMAL'		 =>	1, // 正常收费
				'FEE_MONTHLY_LIST'	 =>	2, // 包月话单
				'FEE_MONTHLY_DOWNLOAD'	 =>	3, // 包月下发
				);


		$dst	= $mobileNo;	// 数字,目的手机号 
		$msgfmt		= 0;	// 英文，如果是中文，就去掉这个参数


		list($msg,$msgfmt) = self::FormatSms($smsMsg);

		$msg	= urlencode( iconv('UTF-8','GBK', $msg));		// urlencode的GBK编码消息


		$appid	= 93;	// 数字，应用编号，需分配
		$func 	= self::GetFuncByServerAddressAndNo( $serverAddress, $mobileNo ); // 数字，长号码，只加自己的扩展号
		
		$pid = 0;
		if( $gid == self::GID_UNICOM ) {
			if( $func == 456 ) 
				$pid = 46;
			else
				$pid = 47;
		}
		
		$moflag		= $mt_type['MT_TYPE_NO_MO'];
		$msgtype	= $mt_fee['FEE_FREE'];
		
		$param		= 'nofilter';
		
		// appid=XX&gid=X&dst=1331234567&pid=XX&msg=XXX&linkid=XXX&func=XXX&moflag=X&msgtype=X 
		$rpc_url = $MT_HTTP_URL . "?appid=$appid"
							. "&gid=$gid"
							. "&dst=$dst"
							. "&pid=$pid"
							. "&msg=$msg"
							. "&linkid=$linkId"
							. "&func=$func"
							. "&moflag=$moflag"
							. "&msgtype=$msgtype"
							. "&param=$param"
							//. "&src=99118816" //mobile src ??
						;

		if ( isset($msgfmt) )
			$rpc_url .= "&msgfmt=$msgfmt";

		//error_log( $rpc_url );

		JWLog::Instance()->Log(LOG_INFO,"JWSms::SendMt Calling: [$rpc_url]");

		$retry = 0;

		$return_content = @file_get_contents($rpc_url);

		while ( empty($return_content) && $retry++<3 )
		{
			JWLog::Instance()->Log(LOG_ERR,"JWSms::SendMt connect to sp failed. retry #$retry.");
			$return_content = @file_get_contents($rpc_url);
		}

		if ( empty($return_content) )
		{
			JWLog::Instance()->Log(LOG_CRIT,"JWSms::SendMt connect to sp failed after retry $retry times.");
			return false;
		}


		if ( !preg_match('/^(\d+)\s+(\S+)$/',$return_content,$matches) )
		{
			if ( preg_match('/^(\d+)$/',$return_content,$matches) )
				$ret = $matches[1];

			JWLog::Instance()->Log(LOG_ERR, "JWSms::SendMt return content parse err:[$return_content]($error_code => [$ret])");
			return false;
		}

		$ret	= $matches[1];
		$msgid	= $matches[2];

		JWLog::Instance()->Log(LOG_INFO,"JWSms::SendMt succ. returns: ret[$ret]($error_code[$ret]) / msgid[$msgid]");

		return true;
	}


	/*
	 *	根据手机号，得到 mt url 的 gid 参数
	 *
	 */
	static public function GetGidByMobileNo($mobileNo)
	{
		switch ( JWDevice::GetMobileSP($mobileNo) )
		{
			case JWDevice::SP_CHINAMOBILE: 	return self::GID_CHINAMOBILE;
			case JWDevice::SP_UNICOM: 	return self::GID_UNICOM;

			case JWDevice::SP_PAS: 		return self::GID_PAS;
			case JWDevice::SP_UNKNOWN: 	// fall to default
			default: 					
				JWLog::Instance()->Log(LOG_ERR, "GetGidByMobileNo($mobileNo) Unsupported. ");
				return self::GID_UNKNOWN;
		}
	}

	/*
	 *  根据serverAddress 得到 GId
	 *
	 */
	static public function GetGidByServerAddressAndNo( $serverAddress, $mobileNo ) {

		switch ( JWDevice::GetMobileSP($mobileNo) )
		{
			case JWDevice::SP_CHINAMOBILE: 
				{	
					if( substr( $serverAddress, 0, 4 ) == 9911 )
						return self::GID_CHINAMOBILE;
					else if( substr( $serverAddress , 0, 5 ) == 50136 )
						return self::GID_CHINAMOBILE_TWO;
				}
			break;
			case JWDevice::SP_UNICOM: 
				{
					if( substr( $serverAddress, 0, 4 ) == 9318 )
						return self::GID_UNICOM_TWO;
					else if( substr( $serverAddress , 0, 4 ) == 9501 )
						return self::GID_UNICOM;
				}
			break;
			case JWDevice::SP_PAS: 	
				{	
					return self::GID_PAS;
				}
			break;
			case JWDevice::SP_UNKNOWN: 
			default: 					
				{
					JWLog::Instance()->Log(LOG_ERR, "GetGidByMobileNo($mobileNo) Unsupported. ");
					return self::GID_UNKNOWN;
				}
		}
		return self::GID_UNKNOWN;
	}

	/*
	 *
	 *  得到长号码
	 */
	static public function GetFuncByServerAddressAndNo( $serverAddress , $mobileNo) {
		switch ( JWDevice::GetMobileSP($mobileNo) )
		{
			case JWDevice::SP_CHINAMOBILE: 
				{	
					if( substr( $serverAddress, 0, 4 ) == 9911 )
						return substr( $serverAddress, 4 );
					else if( substr( $serverAddress , 0, 5 ) == 50136 )
						return substr( $serverAddress, 5 );
				}
			break;
			case JWDevice::SP_UNICOM: 
				{
					return substr( $serverAddress, 4 );
				}
			break;
			case JWDevice::SP_PAS: 	
				{	
					return substr( $serverAddress, 5 );
				}
			break;
			case JWDevice::SP_UNKNOWN: 
			default: 					
				{
					JWLog::Instance()->Log(LOG_ERR, "GetGidByServerAddressAndNo($serverAddress , $mobileNo) Unsupported. ");
					return substr( $serverAddress, 4 );
				}
		}
		return substr( $serverAddress, 4 );
	}

	/*
	 * 根据手机号返回地区
	 */

	static public function GetLocationByMobileNo( $mobileNo ) {

		if( preg_match( '/^138((01)|(10)|(11))[0-9]{6}$/', $mobileNo )
			|| preg_match( '/^139((01)|(10)|(11))[0-9]{6}$/', $mobileNo )
			|| preg_match( '/^137((01)|(18)|(16)|(17))[0-9]{6}$/', $mobileNo )
			|| preg_match( '/^136((93)|(91)|(83)|(81)|(01)|(11)|(21)|(41)|(51)|(61)|(71))[0-9]{6}$/', $mobileNo )
			|| preg_match( '/^135((01)|(21)|(20)|(22)|(52)|(81))[0-9]{6}$/', $mobileNo )
			|| preg_match( '/^134((39)|(36)|(66)|(68))[0-9]{6}$/', $mobileNo )
			|| preg_match( '/^15901[0-9]{6}$/', $mobileNo ) 
			|| preg_match( '/^15801[0-9]{6}$/', $mobileNo ) )
		{
			return 1;
		}
		return 0;
	}


	/*
	 * 根据是否包含中文决定发送 140(ascii) 还是 70(中文)
	 * @return array, (one_sms_string, msg_fmt)
	 */
	static function FormatSms($smsMsg)
	{
		$smsMsg = preg_replace('/\r/', "", $smsMsg);
		//$smsMsg = preg_replace('/\n/', "\r\n", $smsMsg);
		// XXX 
		// 1. treo 650 显示 \n 有时候不正常
		// 2. JWRobotMsg->Save()后，文件中为什么会多出\r字符？

		if ( preg_match('/^[\x00-\x7F]+$/', $smsMsg) )
		{ 	// 英文字符

			return array (mb_substr($smsMsg,0,140,'UTF-8'), 0);
		}

		// 有中文

		$smsMsg = mb_substr($smsMsg,0,70,'UTF-8');

		return array ($smsMsg, null);
	}
}
?>
