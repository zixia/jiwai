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
		$config 	= JWConfig::instance();
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

		error_log ( "$now ReceiveMo: msg [$smsMsg]"
					. " from mobile [$mobileNo] to service [$serviceNo]"
					. " by link [$linkId] through [$gateId]" );

		$robot_msg = new JWRobotMsg();
		$robot_msg->Set($mobileNo, 'sms', $smsMsg);

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

		error_log ( "SubscribeReport: mobile [$mobileNo] had $isSub-ed [$productId] @[$gateId]" );
		return true;
	}


	/*
	 *
	 *
	 */
	static public function DeliveReport ($mobileNo, $msgId, $deliveState, $errCode, $gateId)
	{
		self::Instance();

		error_log ( "DeliverReport: [$msgId] of [$mobileNo] now [$deliveState], err [$errCode] @[$gateId]" );
		return true;
	}


	/*
	 *
	 *
	 * 9911(chn) & 9318(uni) mt
	 *
	 */
	static public function SendMt ($mobileNo, $smsMsg)
	{

		$MT_HTTP_URL_3RD	= 'http://211.157.106.111:8092/sms/third/submit';
		//define ('MT_HTTP_URL_TEST',	'http://beta.jiwai.de/wo/dump');

		$error_code = array(	0		=> 'HE_ERR_OK'
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


		$mt_type	= array(	'MT_TYPE_MO_FIRST'		=>	0 // MO点播引起的第一条MT消息
								, 'MT_TYPE_MO_NOT_FIRST'=>	1 // MO点播引起的非第一条MT消息
								, 'MT_TYPE_NO_MO'		=>	2 // 非MO点播引起的MT消息
								, 'MT_TYPE_SYSTEM'		=>	3 // 系统反馈引起的MT消息
							);

		$mt_fee		= array(	'FEE_FREE'				=>	0 // 免费消息
								, 'FEE_NORMAL'			=>	1 // 正常收费
								, 'FEE_MONTHLY_LIST'	=>	2 // 包月话单
								, 'FEE_MONTHLY_DOWNLOAD'=>	3 // 包月下发
							);


		$dst				= $mobileNo;	// 数字,目的手机号 
		$msgfmt				= 0;	// 英文，如果是中文，就去掉这个参数


		list($msg,$msgfmt)	= self::FormatSms($smsMsg);
		$msg				= urlencode(
									iconv('UTF-8','GBK', $msg)
							);		// urlencode的GBK编码消息


		$appid	= null;	// 数字，应用编号，需分配
		$gid	= null;	// 数字，网关ID
		$pid	= null;	// 数字,产品ID
		$linkid	= null;	// 如果mo里面有带下来，(没有不填，不要乱填)
		$func	= 1234; // 数字，长号码，只加自己的扩展号

		$func	= '8816';

		/*
		$pid	= 
		$linkid = 
		*/

		$appid	= 93;
		$gid	= 1; // 移动:1 联通:3
		
		$moflag		= $mt_type['MT_TYPE_NO_MO'];
		$msgtype	= $mt_fee['FEE_FREE'];
		
		
		// appid=XX&gid=X&dst=1331234567&pid=XX&msg=XXX&linkid=XXX&func=XXX&moflag=X&msgtype=X 
		$rpc_url = $MT_HTTP_URL_3RD . "?appid=$appid"
							. "&gid=$gid"
							. "&dst=$dst"
							. "&pid=$pid"
							. "&msg=$msg"
							. "&linkid=$linkid"
							. "&func=$func"
							. "&moflag=$moflag"
							. "&msgtype=$msgtype"
							. "&src=9911881612345" //XXX src ??
						;

		if ( !empty($msgfmt) )
			$rpc_url .= "&msgfmt=$msgfmt";


		error_log("Calling: [$rpc_url]");

		$return_content = file_get_contents($rpc_url);

		if ( empty($return_content) )
		{
			error_log("MT connect to sp failed. pls retry later.");
			return false;
		}

		if ( !preg_match('/^(\d+)\s+(\S+)$/',$return_content,$matches) )
		{
			if ( preg_match('/^(\d+)$/',$return_content,$matches) )
				$ret = $matches[1];

			error_log("return content parse err:[$return_content]($error_code[$ret])");
			return false;
		}

		$ret	= $matches[1];
		$msgid	= $matches[2];

		error_log("mt succ. returns: ret[$ret]($error_code[$ret]) / msgid[$msgid]");

		return true;
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
