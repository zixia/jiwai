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
		$directory = JWConfig::instance()->directory;
		
		self::$mQueuePathMo	= $directory->queue->root 
								. $directory->queue->robot 
								. $directory->mo
								;

		self::$mQueuePathMt	= $directory->queue->root 
								. $directory->queue->robot 
								. $directory->mt
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
				|| ! file_exists(self::$mQuarantinePathMo)
				|| ! file_exists(self::$mQuarantinePathMt)
				)
		{
			echo "JWRobot::InitDirectory ing...\n";

			mkdir(self::$mQueuePathMo,0700,true);
			mkdir(self::$mQueuePathMt,0700,true);
			mkdir(self::$mQuarantinePathMo,0700,true);
			mkdir(self::$mQuarantinePathMt,0700,true);
		}
	
		if ( ! is_writeable(self::$mQueuePathMo) 
				|| !is_writeable(self::$mQueuePathMt)
				|| !is_writeable(self::$mQuarantinePathMo)
				|| !is_writeable(self::$mQuarantinePathMt)
				)
		{
			throw new JWException("JWRobot queue_path not writeable");
		}
		return true;
	}

	/**
	 *	@param	robotMsgs	array of RobotMsg / one RobotMsg
	 *	@return	true/false
	 */
	static function SendMt ($robotMsg)
	{
		if ( empty($robotMsg) ){
			throw new JWException("empty msg");
		}
		
		$ret = true;

		do{
			$filename = self::$mQueuePathMt
						. $robotMsg->GenFileName();
		}while (file_exists($filename) );

		if ( ! $robotMsg->Save($filename) )
		{	// can't save file
			JWLog::Instance()->Log(LOG_ERR
							,"save msg err: " 
								.$robotMsg->GetAddress() 
								." @" . $robotMsg->GetType() 
								." : [" . $robotMsg->GetBody() . "]"
								." file [" . $filename . "]"
							);

			self::QuarantineMsg($robotMsg);
			$ret = false;
		}
//die(var_dump($ret));
		return $ret;
	}


	static function SendMtRaw ($address, $type, $msg)
	{
		$robot_msg = new JWRobotMsg();
		$robot_msg->Set($address,$type,$msg);
		self::SendMt($robot_msg);
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

			if ( 1==$returnNumMax ){
   				closedir($handle);
				return $robot_msg;
			}else{
				array_push ($arr_robot_msgs, $robot_msg);

				$counter++;
				if ( $counter>=$returnNumMax ){
					break;
				}
			}
		}

   		closedir($handle);

		return $arr_robot_msgs;
	}


	static public function Run ()
	{
		self::Instance();

		while ( true ){
			try{
				self::MainLoop();
			}catch(Exception $e){
 				JWLog::Instance()->Log(LOG_ERR, 'main_loop exception' );
				echo "Exception: " .  $e . "\n";
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
			print "*";

			foreach ( $arr_robot_msgs as $robot_msg )
			{
				$robot_reply_msg = JWRobotLogic::ProcessMo($robot_msg);

				if ( false===$robot_reply_msg )
				{	// some err occur
					JWLog::Instance()->Log(LOG_ERR, "unvalid msg from " . $robot_msg->GetAddress());
					self::QuarantineMsg($robot_msg);

					JWLog::Instance()->Log(LOG_ERR, 'JWRobotLogic::process_mo failed, quarantined.');
				}
				else if ( null===$robot_reply_msg )
				{	// no need to reply. just keep silence
					$robot_msg->Destroy();
				}
				else
				{	// some msg returned
					if ( self::SendMt($robot_reply_msg) )
					{	// msg only be destroied when be delivered successful.
						$robot_msg->Destroy();
					}
					else
					{
						JWLog::Instance()->Log(LOG_ERR, 'SendMt failed');
					}
				}
			}
		}
	}


	static function QuarantineMsg($rRobotMsg)
	{
		$file_path= $rRobotMsg->GetFile();

		if ( isset($file_path) )
		{
			if ( ! preg_match('/([^\/]+)$/',$file_path,$matches) )
			{
				JWLog::Instance()->Log(LOG_ERR, "file[$file] format err?");
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
		print ".";
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

}
?>
