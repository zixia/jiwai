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
	static public function &Instance($cluster='default', $protocol=self::TCP)
	{
		if (!isset(self::$msInstances[$cluster])) {
			$class = __CLASS__;
			self::$msInstances[$cluster] = new $class($cluster, $protocol);
		}
		return self::$msInstances[$cluster];
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

		if ( isset($this->msLocalCache[$key]) )
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

		if ( isset($this->msLocalCache[$key]) )
			$this->msLocalCache[$key] = $var;

		return $this->msMemcacheProtocol->Replace($key,$var, $flag, $expire);
	}


	public function Set($key, $var, $flag=0, $expire=0)
	{
		if ( empty($this->msMemcacheProtocol) )
			return null;

		$this->msLocalCache[$key] = $var;

		return $this->msMemcacheProtocol->Set($key,$var,$flag,$expire);
	}


	/*
	 *	根据表名、idPk、条件选择、功能函数名，组合出一个唯一的 memcache key
	 *
	 *	如：
			User(id=1)
			User(nameScreen=zixia)
			Device(address=zixia@zixia.net,type=gtalk)

			Status[GetStatusIdsFromUser(1)]
			Status[GetStatusIdsFromUser(1)]
	 *
	 *
	 */

	static public function DbKeys2McKeys($table, $idPks)
	{
		$keys = array();
		foreach ( $idPks as $pk_id )
		{
			$keys[] = "$table(id=$pk_id)";
		}
		return $keys;
	}

	static public function McKeys2DbKeys($table, $mcKeys)
	{
		$keys = array();
		foreach ( $mcKeys as $mc_key )
		{
			if ( preg_match("/$table\(id=(\d+)\)$/", $mc_key, $matches) )
			{
				$keys[] = $matches[1];
			}
		}
		return $keys;
	}


	static public function GetKeyFromPk($table, $idPk)
	{
		$key_row = self::GetKeysFromPks($table, array($idPk));
		return $key_row[$idPk];
	}

	static public function GetKeyFromCondition($table, $condition, $limit)
	{
	}

	static public function GetKeyFromFunction($table, $function, $param=null)
	{
	}

}
?>
