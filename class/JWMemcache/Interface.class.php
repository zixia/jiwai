<?php
/**
 * @package     JiWai
 * @copyright   JiWai.de Inc.
 * @author      zixia@zixia.net
 * @date		2007/7/6
 */

/**
 * JiWai Memcache Interface
 *
 *	为 TCP / UDP 的 memcache 提供统一的接口
 */
interface JWMemcache_Interface
{
    function Add	($key, $var, $flag=0, $expire=0);
    function Set	($key, $var, $flag=0, $expire=0);
	function Replace($key, $var, $flag=0, $expire=0);

    function Get($key);

	function Dec($key, $value);
	function Inc($key, $value);

    function Del($key, $timeout=0);
}

?>
