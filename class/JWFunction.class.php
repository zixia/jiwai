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

	/*
	 *	2007-06-14
	 *	从一个 Rows 数据结构中，将所有的 column name 抽取出来成为一个 array
	 *	如： 	$rows[1] = array ( k1=>v1, k2=>v2 )
	 *			$rows[2] = array ( k1=>v3, k2=>v4 )
	 *	调用 GetColArrayFromRows($rows, 'k1') 则会得到 array ( v1, v3 );
	 */
	public static function GetColArrayFromRows($rows, $colKeyName)
	{
		$func_key_name 		= "JWFunction::GetColArrayFromRows_$colKeyName";
		$func_callable_name	= JWFunction::Get($func_key_name);

		if ( empty($func_callable_name) )
		{
			$reduce_function_content = 'return $row["' . $colKeyName . '"];';
			$reduce_function_param 	= '$row';
			$func_callable_name 	= create_function( $reduce_function_param,$reduce_function_content );

			JWFunction::Set($func_key_name, $func_callable_name);
		}
	
		// 装换rows, 返回 id 的 array
		$ids = array_map(	 $func_callable_name
							,$rows
						);

		return $ids;
	}


	/**
	 *	2007-07-10
	 *	将一个数组 array ，按照 map array 映射为 values 的另外一个 array
	 *	如： 	$items 	= array ( 1,2,3 )
	 *			$map	= array ( 1=>10, 2=>40, 3=>90 )
	 *	调用 GetMappedArray($items, $map) 则会得到 array ( 10,40,90 )
	 *	注意：要严格保证顺序
	 */
	public static function GetMappedArray($items, $map)
	{
		$result_items = array();

		foreach ( $items as $item )
		{
			$result_items[] = $map[$item];
		}

		return $result_items;
/*
 * 没有找到能够一次性解决这个问题的 php 函数
 * 只好是用 foreach 了
		$func_key_name 		= "JWFunction::GetMappedArray";
		$func_callable_name	= JWFunction::Get($func_key_name);

		if ( empty($func_callable_name) )
		{
			$reduce_function_content = 'return $map[$val];';
			$reduce_function_param 	= '$val, $key, $map';
			$func_callable_name 	= create_function( $reduce_function_param,$reduce_function_content );

			JWFunction::Set($func_key_name, $func_callable_name);
		}
	
		// 装换rows, 返回 id 的 array
		array_walk(	 $items
					,$func_callable_name, $map
				);

		return $items;
*/
	}

}
?>
