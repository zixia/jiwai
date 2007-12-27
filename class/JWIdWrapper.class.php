<?php
/**
 * @package	JiWai.de
 * @copyright AKA Inc.
 * @author	seek@jiwai.com
 * @version	$Id$
 */

/**
 * JiWai.de IdWrapper Class
 */
class JWIdWrapper {

	static public $string_pool = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	static public $string_pre = 'ILOVEJIWAIANDWORKSFORJIWAI';
	static public $pool_len = 36;
	static public $pre_len = 26;

	static public function IsWrappedId( $id )
	{
		if ( false===strpos(self::$string_pre, substr($id, 0, 1)) )
			return false;

		if ( strlen($id)==strspn($id, self::$string_pool) )
			return true;

		return false;
	}

	static public function EncodeId($decoded_id)
	{
		$id = intval( $decoded_id );

		if ( $id < 0 )
			return false;

		$pre = self::$string_pre[intval($id%self::$pre_len)];
		$encoded_id = null;
		
		do
		{
			$m = $id % self::$pool_len;
			$encoded_id = self::$string_pool[$m] . $encoded_id;
			$id = intval( $id / self::$pool_len );
		}
		while ($id>0);

		return $pre . $encoded_id;
	}

	static public function DecodeId($encoded_id)	
	{
		$id = strtoupper($encoded_id);
		if ( false==self::IsWrappedId($id) )
			return false; 

		$pre = substr($id, 0, 1);
		$id = strrev(substr($id, 1));
		$base = 0;
		
		$decoded_id = 0;
		for ($i=0;$i<strlen($id);$i++)
		{
			$pos = strpos(self::$string_pool, $id[$i]);
			if ( $base == 0 )
			{
				$decoded_id += $pos;
			}
			else
			{
				$decoded_id += $pos*$base;
			}

			$base = ($base==0) ? self::$pool_len : ($base*self::$pool_len);
		}

		$pre_verify = self::$string_pre[intval($decoded_id%self::$pre_len)];

		return $pre==$pre_verify ? false : $encoded_id;
	}
}
?>
