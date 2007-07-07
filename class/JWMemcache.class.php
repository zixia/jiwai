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
class JWMemcache {
	/**
	 * Instance of this singleton
	 *
	 * @var JWMemcache
	 */
	static private $msInstance = array();

	const NONE		= 1;
	const UDP		= 2;
	const TCP		= 3;

	private	$msMemcacheProtocol	= null;


	/**
	 *	程序内部的一级 cache，节省网络访问。
	 *
	 *	我们很多时候需要 Get 同一个 key 多次。
	 *	我们在第一次取 key 的时候，就将其放入程序内存的 cache，这样下次获取的时候，
 	 *	如果程序内存的 cache 有，就不用在通过网络获取了。
	 */
	private	$msLocalCache = array();


	/**
	 * Instance of this singleton class
	 *
	 * @return JWMemcache
	 */
	static public function &Instance($cluster='default', $protocol=self::UDP)
	{
		if (!isset(self::$msInstance[$cluster])) {
			$class = __CLASS__;
			self::$msInstance[$cluster] = new $class($cluster, $protocol);
		}
		return self::$msInstance[$cluster];
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct($cluster='default', $protocol=self::UDP)
	{
		switch ( $protocol )
		{
			case self::UDP:
				$this->msMemcacheProtocol = JWMemcacheUdp::Instance($cluster);
				break;
			case self::TCP:
				$this->msMemcacheProtocol = JWMemcacheTcp::Instance($cluster);
				break;
			default:
				//fall to NONE, disable memcache.
			case self::NONE:
				$this->msMemcacheProtocol = null;
				break;
		}
	}


	/*
	 *	以下参数兼容 PHP Memcache Functions
	 *	@param	string	$key
	 *	@param	mixed	$var
	 *	@param	int		$flag
	 *	@param	int		$expire
	 */
	public function Add($key, $var, $flag, $expire)
	{
		if ( empty($this->msMemcacheProtocol) )
			return null;

		if ( isset($this->msLocalCache[$key]) )
			return false;
	
		return $this->msMemcacheProtocol->Add($key,$var,$flag,$expire);
	}

	/*
	 *	@param	string	$key
	 *	@param	int		$value
	 */
	public function Dec($key, $value)
	{
		if ( empty($this->msMemcacheProtocol) )
			return null;

		return $this->msMemcacheProtocol->Dec($key,$value);
	}

	public function Del($key, $timeout=0)
	{
		if ( empty($this->msMemcacheProtocol) )
			return null;

		if ( isset($this->msLocalCache[$key]) )
			unset($this->msLocalCache[$key]);

		return $this->msMemcacheProtocol->Del($key,$timeout);
	}

	public function Get($key)
	{
		if ( empty($this->msMemcacheProtocol) )
			return null;

		if ( isset($this->msLocalCache[$key]) )
			return $this->msLocalCache[$key];

		return $this->msMemcacheProtocol->Get($key);
	}

	public function Inc($key, $vlaue)
	{
		if ( empty($this->msMemcacheProtocol) )
			return null;

		return $this->msMemcacheProtocol->Inc($key,$value);
	}

	public function Replace($key, $var, $flag, $expire)
	{
		if ( empty($this->msMemcacheProtocol) )
			return null;

		if ( isset($this->msLocalCache[$key]) )
			$this->msLocalCache[$key] = $var;

		return $this->msMemcacheProtocol->Replace($key,$var, $flag, $expire);
	}


	public function Set($key, $var, $flag, $expire)
	{
		if ( empty($this->msMemcacheProtocol) )
			return null;

		$this->msLocalCache[$key] = $var;

		return $this->msMemcacheProtocol->Set($key,$var,$flag,$expire);
	}

}
?>
