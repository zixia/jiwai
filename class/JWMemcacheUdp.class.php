<?php
/**
 * @package     JiWai
 * @copyright   JiWai.de Inc.
 * @author      FreeWizard
 */

/**
 * JiWai Memcache Class
 */
class JWMemcacheUdp implements JWMemcacheInterface{
    /**
     * Instance of this singleton
     *
     * @var array
     */
    static private $msInstances=array();

    private $msPool;

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
		if ( ! function_exists(memc_server_pool) )
			throw new JWException("can't find memc_server_pool function, php ext not loaded?");

        $this->msPool = memc_server_pool(CONFIG_ROOT.'/memcache.'.$cluster);
    }


    function Get($key) {
        if (is_array($key)) {
            $r = memc_get_keys($this->msPool, $key);
            foreach ($r as $k=>$v) $r[$k] = unserialize($v['value']);
            return $r;
        } else {
            $r = memc_get_keys($this->msPool, array($key));
            return isset($r[$key]) ? unserialize($r[$key]['value']) : false;
        }
    }


    function Add($key, $var, $flag=0, $expire=0) {
        return memc_set_key($this->msPool, $key, serialize($var), $flag, $expire);
    }

 
	function Dec($key, $value)
	{
        return memc_dec_key($this->msPool, $key, $value);
	}


	function Inc($key, $value)
	{
        return memc_inc_key($this->msPool, $key, $value);
	}

	function Replace($key, $var, $flag=0, $expire=0)
	{
        return memc_replace_key($this->msPool, $key, serialize($var), $flag, $expire);
	}


    function Set($key, $var, $flag=0, $expire=0) {
        return memc_set_key($this->msPool, $key, serialize($var), $flag, $expire);
    }

    function Del($key, $timeout=0) {
        if (is_array($key)) {
            foreach ($key as $k) memc_del_key($this->msPool, $k, $timeout);
        } else {
            memc_del_key($this->msPool, $key, $timeout);
        }
    }
}

?>
