<?php
/**
 * @package     JiWai.de
 * @copyright   AKA Inc.
 * @author      zixia@zixia.net
 * @version     $Id$
 */

/**
 * JWFunction
 *	当我们 create_function 的时候，会将其安装一定命名空间的规则进行命名，
 *	然后通过 JWFunction 类，把动态创建的函数名和我们的有规则名称进行对应
 *	防止多次重复生成造成内存泄露
 */

Class JWFunction {
    /**
     * Instance of this singleton
     *
     * @var JWFunction
     */
    static private $msInstance;

	/**
	 *	存放 动态生成的函数名 与 规则命名 之间的对应关系
	 */
    static private $msFunctionList;


    /**
     * Instance of this singleton class
     *
     * @return JWFunction
     */
    static public function &Instance()
    {
        if (!isset(self::$msInstance)) {
            $class = __CLASS__;
            self::$msInstance = new $class;
        }
        return self::$msInstance;
    }


    function __construct() 
	{
		self::$msFunctionList = array();
    }


	public static function Set($key, $funcName)
	{
		self::Instance();

		if ( isset(self::$msFunctionList[$key]) )
			throw new JWException('func exists!');

		if ( ! is_callable($funcName) ) {
			JWLog::Log(LOG_ERR, "JWFunction::Set($key, $funcName) is not callable");
			return false;
		}

		self::$msFunctionList[$key] = $funcName;
		return true;
	}

	/*
	 *	@param	string	$key	函数的有意义名字
	 *	
	 *	@return	string	$func_name	可调用的函数的名字
	 */
	public static function Get($key)
	{
		self::Instance();

		if ( ! isset(self::$msFunctionList[$key]) )
			return null;

		$func_name	= self::$msFunctionList[$key];

		if ( ! is_callable($func_name) )
			throw new JWException("JWFunction::Get($key) found uncallable [$func_name]");

		return $func_name;
	}

	public static function Dump()
	{
		print var_dump(self::$msFunctionList);
	}
}
?>
