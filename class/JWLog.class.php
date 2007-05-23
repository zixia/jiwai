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
	static private $msInstance;

	/**
	 *
	 *	@var LOG_* for syslog. LOG_DEBUG is max.
	 */
	static private $msVerbose;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWLog
	 */
	static public function &Instance($ident="JWPhp")
	{
		if (!isset(self::$msInstance)) 
		{
			$class = __CLASS__;
			self::$msInstance = new $class($ident);
		}
		return self::$msInstance;
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct($ident="JWPhp", $facility=null)
	{
		define_syslog_variables();

		if ( !isset($facility) )
			$facility = LOG_LOCAL0;

		self::$msVerbose	= LOG_DEBUG;

		openlog($ident, (LOG_PID | LOG_CONS), $facility);
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
		// we use JWLog::Log directly.
		self::Instance();

		if ( $priority > self::$msVerbose )
			return;

		return syslog($priority, $message);
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

		self::Log($priority, "$prefix $message");
	}
}
?>
