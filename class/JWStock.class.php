<?php
/**
 * @package	 JiWai.de
 * @copyright   AKA Inc.
 * @author	  wqsemc@jiwai.com
 * @version	 $Id$
 */

/**
 * JWStock
 */

class JWStock
{
	static public function GetClassRows()
	{
		$file_path = FRAGMENT_ROOT . 'page/stock.txt';
		$file = file_get_contents( $file_path );
		$class_rows = split(',', $file);
		$class_rows = array_slice($class_rows, 1);
		return $class_rows;
	}

	static public function GetCacheKeyTotal($type)
	{
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array( 'JWStock', 'GetCacheKeyTotal' ), array($type));
		return $mc_key;
	}

	static public function Query($class_array, $type)
	{
		$url_array = array_slice($class_array, 1, count($class_array)-2);
		$url_array = JWFunction::RandomArray($url_array, 10);
		$memcache = JWMemcache::Instance();
		$mc_key = self::GetCacheKeyTotal($type);
		$memcache->Set($mc_key, $url_array);

		return $url_array;
	}

	static public function QueryAll()
	{
		$class_rows = JWStock::GetClassRows();
		$i=0;
		foreach($class_rows as $class_row)
		{
			$i++;
			$class_array = preg_split('/[\n\r]/', $class_row);
			JWStock::Query($class_array, $i);
		}
	}

	static public function Total($class_array, $type)
	{
		$memcache = JWMemcache::Instance();
		$mc_key = self::GetCacheKeyTotal($type);
		$v = $memcache->Get($mc_key);
		if (!$v)
		{
			$v = self::Query($class_array, $type);
		}

		return $v;
			
	}
}

