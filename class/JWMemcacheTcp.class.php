<?php
/**
 * @package     JiWai
 * @copyright   JiWai.de Inc.
 * @author      zixia@zixia.net
 * @date		2007/07/06
 */

/**
 * JiWai MemcacheTcp Class
 */
class JWMemcacheTcp implements JWMemcacheInterface{
    /**
     * Instance of this singleton
     *
     * @var array
     */
    static private $msInstances=array();

    private $msMemcache	= null;

    /**
     * Instance of this singleton class
     *
     * @return MemcacheUdp
     */
    static public function &Instance ($cluster='default')
    {
        if (!isset(self::$instances__[$cluster])) {
            $class = __CLASS__;
            self::$instances__[$cluster] = new $class($cluster);
        }
        return self::$instances__[$cluster];
    }
    

    /**
     * 
     *
     * @param string $cluster
     */
    private function __construct($cluster='default') {
		if ( ! function_exists(memcache_add_server) )
			throw new JWException("can't find memcache_add_server function, php ext not loaded?");

        //$this->msPool = memc_server_pool(
		$config_file = CONFIG_ROOT.'/memcache.'.$cluster;
		
		$this->msMemcache = new Memcache;

		if ( ! $this->LoadMemcacheServer($config_file) )
			throw new JWException("can't load memcache server from file [$config_file]");
    }

	function LoadMemcacheServer($file)
	{
		if ( empty($this->msMemcache) )
			throw new JWException("class not init!");

		$config_data	= file_get_contents($file);

		$lines	= explode("\n",$config_data);
		$server_count	= 0;

		foreach( $lines as $line )
		{
			if ( preg_match('/^([\d\.]+):(\d+)\s+(\d+)$/',$line,$matches) )
			{
				$ip 	= $matches[1];
				$port 	= $matches[2];
				$weight	= $matches[3];

				$this->msMemcache->addServer(	 $ip
												,$port
												,true
												,$weight
												,1
												,15
												,true
												,array('JWMemcacheTcp','FailureCallback')
											);
				$server_count++;
			}
		}

		return $server_count;
	}

	function FailureCallback($ip, $port)
	{
		JWLog::Log(LOG_CRIT, "Memcache[$ip:$port] failure!");
	}


    function Get($key) 
	{
		return $this->msMemcache->get($key);	
    }


    function Add($key, $var, $flag=0, $expire=0) {
		return $this->msMemcache->add($key,$var,$flag,$expire);
    }

 
	function Dec($key, $value=1)
	{
        return $this->msMemcache->decrement($key, $value);
	}


	function Inc($key, $value=1)
	{
        return $this->msMemcache->increment($key, $value);
	}

	function Replace($key, $var, $flag=0, $expire=0)
	{
        return $this->msMemcache->replace($key, serialize($var), $flag, $expire);
	}


    function Set($key, $var, $flag=0, $expire=0) {
        return $this->msMemcache->set($key, $var, $flag, $expire);
    }

    function Del($key, $timeout=0) {
        if (is_array($key)) {
            foreach ($key as $k) $this->msMemcache->delete($k, $timeout);
        } else {
            $this->msMemcache->delete($k, $timeout);
        }
    }
}

?>
