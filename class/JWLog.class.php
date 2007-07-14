<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Log Class
 */
class JWLog {
	/**
	 * Instance of this singleton
	 *
	 * @var JWLog
	 */
    static private $msInstances=array();


	/**
	 *	用来将模块名对应到相应的 LOG FACILITY 中的映射表
	 *	如： 'SMS' => LOG_LOCAL1
	 */
	static private $msLogFacilityMap ;

	/**
	 *	当前 syslo 的 facility
	 */
	static private $msCurrentFacility;

	

	/**
	 *	每个 Instance 存储自己 Facility 的变量
	 */
	private $msFacility;

	/**
	 *	每个 Instance 存储自己 Indent 的变量
	 */
	private $msIndent;


	/**
	 *
	 *	@var LOG_* for syslog. LOG_DEBUG is max.
	 */
	private $msVerbose;



	/**
	 * Instance of this singleton class
	 *
	 * @return JWLog
	 */
	static public function &Instance($module="Php")
	{
		if ( empty(self::$msLogFacilityMap) )
		{
			define_syslog_variables();

			self::$msLogFacilityMap = array(
					 'Php'		=> LOG_LOCAL0
					,'Sns'		=> LOG_LOCAL1
					,'Sms'		=> LOG_LOCAL2
					,'Memcache'	=> LOG_LOCAL3
					,'Robot'	=> LOG_LOCAL4
				);
		}

		if ( isset(self::$msLogFacilityMap[$module]) )
			$facility = self::$msLogFacilityMap[$module];
		else
			$facility = LOG_LOCAL0;

		if (!isset(self::$msInstances[$facility])) 
		{
			$class = __CLASS__;
			self::$msInstances[$facility] = new $class($facility,$module);
		}
		return self::$msInstances[$facility];
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct($facility=null, $module="Php")
	{
		if ( null===$facility )
			$facility = LOG_LOCAL0;

		$this->msFacility 	= $facility;
		$this->msIndent		= $module;

		$this->msVerbose	= LOG_DEBUG;
	}

	function __destruct()
	{
		closelog();
	}


	/*
	 *	设置过滤，只有高于 verbose 的 log message 才会发送。
	 *	@param $verbose	>0 <7 的一个值。，使用 syslog 的const替代
	 */
	static public function SetVerbose($verbose=LOG_WARNING)
	{
		self::$msVerbose = $verbose;
	}

	
	/*
	 *	发送syslog，参数兼容syslog。使用前要建立 Instance
	 *	@param	string	$priority
Constant Description 
LOG_EMERG system is unusable 
LOG_ALERT action must be taken immediately 
LOG_CRIT critical conditions 
LOG_ERR error conditions 
LOG_WARNING warning conditions 
LOG_NOTICE normal, but significant, condition 
LOG_INFO informational message 
LOG_DEBUG debug-level message 

	*	syslog is fast after we disable sync. "
	*	Prepending filenames in /etc/syslog.conf with a "-", e.g., "/var/log/maillog" becomes "-/var/log/maillog",
	*/
	static public function Log($priority, $message)
	{
		return syslog($priority,$message);
	}

	/**	
	 *	1、参数更方便：经常忘记写 priority，所以放到后面设置个缺省值
	 *	2、不是 static 调用，支持不同的 facility & indent
	 */
	public function LogMsg($message,$priority=LOG_INFO)
	{
		return $this->Syslog($priority,$message);
	}

	static public function LogFuncName($priority, $message)
	{
		$backtrace = debug_backtrace();
	
		if ( 1<count($backtrace) )
		{
			$curr_trace = $backtrace[1];

			if ( isset($curr_trace['class']) )	$prefix = $curr_trace['class'];
			else								$prefix = $curr_trace['file'] . ':' . $curr_trace['line'];

			$prefix .=  '::' . $curr_trace['function'] . ' ';
		}
		else
		{
			$curr_trace = $backtrace[0];
			$prefix = $curr_trace['file'] . ':' . $curr_trace['line'] . ' ';
		}

		return self::Log($priority, "$prefix $message");
	}

	private function Syslog($priority, $message)
	{
		if ( empty(self::$msCurrentFacility) )
		{
			openlog($this->msIndent, (LOG_PID | LOG_CONS), $this->msFacility);
			self::$msCurrentFacility = $this->msFacility;
		}
		elseif ( self::$msCurrentFacility!=$this->msFacility )
		{
			closelog();
			openlog($this->msIndent, (LOG_PID | LOG_CONS), $this->msFacility);
			self::$msCurrentFacility = $this->msFacility;
		}

		return syslog($priority,$message);
	}
}
?>
