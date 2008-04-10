<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Robot Class
 */
class JWRobot {
	/**
	 * Instance of this singleton
	 *
	 * @var JWRobot
	 */
	static private $mInstance;

	/**
	 * idle 
	 *
	 * @var mSleepUsec
	 */
	static private $mSleepUsec		= 0;
	static private $mSleepUsecMax	= 300000; // 0.3s

	/**
	 * path_config
	 *
	 * @var mQueuePathMo mQueuePathMt
	 */
	static private $mQueuePathMo;
	static private $mQueuePathMt;
	static private $mQueuePathTmp;

	/**
	 * bad msg here
	 */
	static private $mQuarantinePathMo;
	static private $mQuarantinePathMt;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWDB
	 */
	static public function &Instance()
	{
		if (!isset(self::$mInstance)) {
			$class = __CLASS__;
			self::$mInstance = new $class;
		}
		return self::$mInstance;
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
		$directory = JWConfig::Instance()->directory;
		
		self::$mQueuePathMo	= $directory->queue->root 
								. $directory->queue->robot 
								. $directory->mo
								;

		self::$mQueuePathMt	= $directory->queue->root 
								. $directory->queue->robot 
								. $directory->mt
								;

		/*
		 * 	JWRobotMsg 在没有完成时先把文件写在这里，然后 link 过去。
		 *	避免 delive 空文件
		 */
		self::$mQueuePathTmp	= $directory->queue->root 
								. $directory->queue->tmp
								;
	
		self::$mQuarantinePathMo	= $directory->quarantine->root 
									. $directory->quarantine->robot 
									. $directory->mo
									;

		self::$mQuarantinePathMt	= $directory->quarantine->root 
									. $directory->quarantine->robot 
									. $directory->mt
									;
		self::InitDirectory();

	}


	static function InitDirectory()
	{
		if ( ! file_exists(self::$mQueuePathMo)
				|| ! file_exists(self::$mQueuePathMt)
				|| ! file_exists(self::$mQueuePathTmp)
				|| ! file_exists(self::$mQuarantinePathMo)
				|| ! file_exists(self::$mQuarantinePathMt)
				)
		{
			JWLog::Log(LOG_NOTICE, "JWRobot::InitDirectory ing...");

			mkdir(self::$mQueuePathMo,0700,true);
			mkdir(self::$mQueuePathMt,0700,true);
			mkdir(self::$mQueuePathTmp,0700,true);
			mkdir(self::$mQuarantinePathMo,0700,true);
			mkdir(self::$mQuarantinePathMt,0700,true);
		}
	
		if ( ! is_writeable(self::$mQueuePathMo) 
				|| !is_writeable(self::$mQueuePathMt)
				|| !is_writeable(self::$mQueuePathTmp)
				|| !is_writeable(self::$mQuarantinePathMo)
				|| !is_writeable(self::$mQuarantinePathMt)
				)
		{
			throw new JWException("JWRobot queue_path not writeable");
		}
		return true;
	}

	/**
	 * send robot msg to message queue, then mt robot will got it;
	 */
	static public function SendMtQueue($robot_msg)
	{
		if ( empty($robot_msg) || false==is_object($robot_msg) )
			return true;

		$type = $robot_msg->GetType();
		$address = $robot_msg->GetAddress();
		$message = $robot_msg->GetBody();

		$server_address = $robot_msg->GetHeader('serverAddress');
		$link_id = $robot_msg->GetHeader('linkid');
		$resource = $robot_msg->GetHeader('resource');

		return self::SendMtRawQueue($address, $type, $message, $server_address, $link_id, $resource);
	}

	/**
	 * send robot msg to message queue, then mt robot will got it;
	 */
	static public function SendMtRawQueue($address, $type, $message, $server_address=null, $link_id=null, $resource=null)
	{
		$channel = "/robot/mt/$type";

		JWPubSub::Instance('spread://localhost/')->Publish($channel, array(
			'type' => $type,
			'address' => $address,
			'server_address' => $server_address,
			'message' => $message,
			'link_id' => $link_id,
			'resource' => $resource,
			));

		return true;
	}



	/**
	 *	@param	robotMsgs	array of RobotMsg / one RobotMsg
	 *	@return	true/false
	 */
	static public function SendMt ($robotMsg)
	{
		self::Instance();

		if ( empty($robotMsg) ){
			throw new JWException("empty msg");
		}
		
		$ret = true;

		do{
			$filename = self::$mQueuePathMt . $robotMsg->GenFileName();
		}while (file_exists($filename) );

		/*
	 	 *	为了提供队列目录中，文件出现的原子性
		 *	（即inode被创建之时，文件内容已经ready），需要先写入tmp目录一个临时文件
		 */
		do{
			$filename_tmp = self::$mQueuePathTmp . $robotMsg->GenFileName();
		}while (file_exists($filename_tmp) );


		JWLog::Log(LOG_ERR, "JWRobot::SendMt "
					."Address[" . $robotMsg->GetAddress() . "] "
					." Type[" . $robotMsg->GetType() . "]"
					." File [" . $filename . "]"
		);


		if ( ! $robotMsg->Save($filename, $filename_tmp) )
		{	// can't save file
			JWLog::Log(LOG_ERR, "save msg err: " 
						.$robotMsg->GetAddress() 
						." @" . $robotMsg->GetType() 
						." : [" . $robotMsg->GetBody() . "]"
						." file [" . $filename . "]"
			);

			self::QuarantineMsg($robotMsg);
			$ret = false;
		}

		self::LogMoMt( $robotMsg->GetType(), $robotMsg->GetAddress(), false );

		return $ret;
	}


	static function SendMtRaw ($address, $type, $msg, $serverAddress=null, $linkId=null, $resource=null)
	{
		if( trim($msg) == null ) {
			JWLog::Instance()->Log(LOG_ERR, "Try to send null msg to $type://$address. [dropped.]");
			return false;
		} 

		if( trim($address) == null ) {
			JWLog::Instance()->Log(LOG_ERR, "Try to send null address with type://$type. [dropped.]");
			return false;
		} 

		$robot_msg = new JWRobotMsg();
		$robot_msg->Set( $address, $type, $msg );

		$robot_msg->SetHeader( 'resource', $resource );
		$robot_msg->SetHeader( 'linkid', $linkId );
		$robot_msg->SetHeader( 'serveraddress', $serverAddress );

		return self::SendMt($robot_msg);
	}


	static function GetMo ( $returnNumMax = 1 )
	{
		$handle=opendir(self::$mQueuePathMo);

		$counter = 0;

		if ( !$handle ){
			JWLog::Instance()->Log(LOG_ERR, "mo opendir [" . self::$mQueuePathMo . "] failure.");
			throw new JWException ( "JWRobot opendir[" . self::$mQueuePathMo . "] failure" );
		}

		$arr_robot_msgs = array();

		while ( false !== ($file=readdir($handle)) ) {
			// must like "msn__*" or "sms__*", etc.
			if ( ! preg_match('/^\w+__/', $file) ){
				continue;
			}

			$file = self::$mQueuePathMo . $file;

			$robot_msg = new JWRobotMsg($file);
			
		
			/*
			 * 只需要一个，返回。
			 */
			if ( 1==$returnNumMax ){
   				closedir($handle);
				return $robot_msg;
			}

			array_unshift ($arr_robot_msgs, $robot_msg);

			$counter++;
			if ( $counter>=$returnNumMax ){
				break;
			}
		}

   		closedir($handle);

		return $arr_robot_msgs;
	}


	static public function Run ()
	{
		self::Instance();

		echo "JiWai control robot enter in mainloop, now can deal MO/MT...\n";

		while ( true ){
			try{
				self::MainLoop();
			}catch(Exception $e){
 				JWLog::Instance()->Log(LOG_ERR, 'main_loop exception' );
				echo "Exception: " .  $e->getMessage() . $e->getTraceAsString() . "\n";
				sleep(1);
			}
		}
	}


	static function MainLoop()
	{
		$robot = self::Instance();

		$arr_robot_msgs = $robot->GetMo( 100 );

		if ( empty($arr_robot_msgs) )
		{
			self::IdleCircle();
		}
		else
		{
			# It's busy now:
			self::$mSleepUsec = 0;
			// print "*";

			foreach ( $arr_robot_msgs as $robot_msg )
			{
				try {
					$robot_reply_msg = JWRobotLogic::ProcessMo($robot_msg);

					if ( false===$robot_reply_msg )
					{	// some err occur
						JWLog::Instance()->Log(LOG_ERR, "unvalid msg from " . $robot_msg->GetAddress());
						self::QuarantineMsg($robot_msg);

						JWLog::Instance()->Log(LOG_ERR, 'JWRobotLogic::process_mo failed, quarantined.');
					}
					else if ( null===$robot_reply_msg )
					{
						self::LogMoMt( $robot_msg->GetType(), 
								$robot_msg->GetAddress(), 
								$robot_msg->GetCreateTime(), true );

						// no need to reply. just keep silence
						$robot_msg->Destroy();
					}
					else
					{	// some msg returned
						if ( self::SendMtQueue($robot_reply_msg) )
						{	
							self::LogMoMt( $robot_msg->GetType(), 
									$robot_msg->GetAddress(), 
									$robot_msg->GetCreateTime(), true );
							// msg only be destroied when be delivered successful.
							$robot_msg->Destroy();
						}
						else
						{
							JWLog::Instance()->Log(LOG_ERR, 'SendMt failed');
						}
					}
				}catch(Exception $e){
					JWLog::Instance()->Log(LOG_ERR, "unvalid msg from " . $robot_msg->GetAddress());
					self::QuarantineMsg($robot_msg);

					JWLog::Instance()->Log(LOG_ERR, 'JWRobotLogic::process_mo failed, quarantined.');
				}
			}
		}
	}


	/*
	 *	FIXME: 好像从来没看到过隔离成功的？
	 */
	static function QuarantineMsg($rRobotMsg)
	{
		$file_path= $rRobotMsg->GetFile();

		if ( isset($file_path) )
		{
			if ( ! preg_match('/([^\/]+)$/',$file_path,$matches) )
			{
				JWLog::Log(LOG_ERR, "file[$file] format err?");
				return false;
			}

			$filename = $matches[1];

			$quarantine_file_path = self::$mQuarantinePathMo . $filename;

			$n = 0;
			while (file_exists($quarantine_file_path))
			{
				// check for double filename
				$quarantine_file_path = self::$mQuarantinePathMo . $filename . $n++;
			}

			$ret = link($file_path, $quarantine_file_path);
			$ret = $ret && unlink($file_path);
		}

		return $ret;
	}


	static function IdleCircle()
	{
		//print ".";
		if (self::$mSleepUsec)
		{
			usleep (self::$mSleepUsec);
			self::$mSleepUsec *= 2;

			if ( self::$mSleepUsec > self::$mSleepUsecMax )
			{
				self::$mSleepUsec = self::$mSleepUsecMax;
			}

		}
		else
		{
			self::$mSleepUsec = 1;
		}
	}

	static public function LogMoMt( $type=null, $address=null, $message = true, $mo = true ) {
		if( null == $type || null == $address )
			return true;

		if( is_bool( $message ) ) $mo = $message ;

		$device = $type . '://'. $address;
		$message = $device . ( is_bool($message) ? null : ' time://'.$message );


		define_syslog_variables();
		$ident = ( $mo ) ? 'RobotMO' : 'RobotMT';
		if ( openLog( $ident, LOG_PID | LOG_CONS, LOG_LOCAL6 ) ) {
			syslog( LOG_INFO, $message );
			closelog();
			return true;
		}

		return false;
	}

}
?>
