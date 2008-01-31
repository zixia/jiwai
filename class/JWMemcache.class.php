<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @date	  	2007/07/06
 */

/**
 * JiWai.de File Class
 */
class JWMemcache implements JWMemcache_Interface
{
	/**
	 * Instance of this singleton
	 *
	 * @var JWMemcache
	 */
	static private $msInstances = array();

	static private $msSyslog;


	const NONE		= 1;
	const UDP		= 2;
	const TCP		= 3;

	const DEFAULT_PROTOCOL	= self::TCP;

	private	$msMemcacheProtocol	= null;


	/**
	 *	程序内部的一级 cache，节省网络访问。
	 *
	 *	我们很多时候需要 Get 同一个 key 多次。
	 *	我们在第一次取 key 的时候，就将其放入程序内存的 cache，这样下次获取的时候，
 	 *	如果程序内存的 cache 有，就不用在通过网络获取了。
	 */
	private	static $msUseLocalCache = true;
	private	$msLocalCache = array();


	/**
	 * Instance of this singleton class
	 *
	 * @return JWMemcache
	 */
	static public function &Instance($cluster='default', $protocol=self::DEFAULT_PROTOCOL, $useLocalCache=true)
	{
		if ( empty(self::$msSyslog) )
			self::$msSyslog = JWLog::Instance('Memcache');

		if (!isset(self::$msInstances[$cluster])) {
			$class = __CLASS__;
			self::$msInstances[$cluster] = new $class($cluster, $protocol, $useLocalCache);
		}


		return self::$msInstances[$cluster];
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct($cluster='default', $protocol=self::TCP, $useLocalCache=true)
	{
		self::$msSyslog->LogMsg("connect to [$cluster] cluster with protocol: " . $protocol);

		switch ( $protocol )
		{
			case self::UDP:
				$this->msMemcacheProtocol = JWMemcache_Udp::Instance($cluster);
				break;
			case self::TCP:
				$this->msMemcacheProtocol = JWMemcache_Tcp::Instance($cluster);
				break;
			default:
				//fall to NONE, disable memcache.
			case self::NONE:
				$this->msMemcacheProtocol = null;
				break;
		}

		self::$msUseLocalCache = $useLocalCache;
	}

	public function SetUseLocalCache($useLocalCache=false, $cache_time=0)
	{
		self::$msUseLocalCache = $useLocalCache;
	}  

	/*
	 *	以下参数兼容 PHP Memcache Functions
	 *	@param	string	$key
	 *	@param	mixed	$var
	 *	@param	int		$flag
	 *	@param	int		$expire
	 */
	public function Add($key, $var, $flag=0, $expire=0)
	{
		if ( empty($this->msMemcacheProtocol) )
			return null;

		if ( self::$msUseLocalCache && isset($this->msLocalCache[$key]) )
			return false;
	
		return $this->msMemcacheProtocol->Add($key,$var,$flag,$expire);
	}

	/*
	 *	@param	string	$key
	 *	@param	int		$value
	 */
	public function Dec($key, $value=1)
	{
		if ( empty($this->msMemcacheProtocol) )
			return null;

		return $this->msMemcacheProtocol->Dec($key,$value);
	}

	public function Del($key, $timeout=0)
	{
		if ( empty($this->msMemcacheProtocol) )
			return null;

		if ( self::$msUseLocalCache && isset($this->msLocalCache[$key]) )
			unset($this->msLocalCache[$key]);

		self::$msSyslog->LogMsg("Del($key,$timeout)");

		return $this->msMemcacheProtocol->Del($key,$timeout);
	}

	/*
	 *	@param	mixed	$key	可能是一个 key，也可能是 array of keys
	 *
	 */
	public function Get($key)
	{
		if ( empty($this->msMemcacheProtocol) )
			return null;


		if ( self::$msUseLocalCache 
				&& !is_array($key) && isset($this->msLocalCache[$key]) )
			return $this->msLocalCache[$key];


		//TODO 使用 msLocalCache 处理 array of keys，然后再 diff 后从 memcache 获取，最后 merge 返回

		$val =  $this->msMemcacheProtocol->Get($key);

		if ( false===$val )
			self::$msSyslog->LogMsg("Get($key) NOT hit");
		else
			self::$msSyslog->LogMsg("Get($key) HIT");

		return $val;
	}

	public function Inc($key, $vlaue=1)
	{
		if ( empty($this->msMemcacheProtocol) )
			return null;

		return $this->msMemcacheProtocol->Inc($key,$value);
	}

	public function Replace($key, $var, $flag=0, $expire=0)
	{
		if ( empty($this->msMemcacheProtocol) )
			return null;

		if ( self::$msUseLocalCache && isset($this->msLocalCache[$key]) )
			$this->msLocalCache[$key] = $var;

		return $this->msMemcacheProtocol->Replace($key,$var, $flag, $expire);
	}


	public function Set($key, $var, $flag=0, $expire=0)
	{
		if ( empty($this->msMemcacheProtocol) )
			return null;

		if ( self::$msUseLocalCache )
			$this->msLocalCache[$key] = $var;

		self::$msSyslog->LogMsg("Set($key,$var,$flag,$expire)");

		return $this->msMemcacheProtocol->Set($key,$var,$flag,$expire);
	}
}
?>
