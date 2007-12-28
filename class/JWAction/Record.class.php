<?php
/**
 * @package JiWai.de
 * @copyright AKA Inc.
 * @author wqsemc@jiwai.com
 * @version $Id$
 */

/**
 * JiWai.de JWAction_Record Class
 */
class JWAction_Record{

	
	const SECOND_POOL_SIZE = 60;
	const MINUTE_POOL_SIZE = 60;

	public $second_array = array();
	public $minute_array = array();

	public function Init()
	{
		$second = time();
		$minute = intval(time()/60);

		$second_min = $second - self::SECOND_POOL_SIZE;
		$minute_min = $minute - self::MINUTE_POOL_SIZE;

		$second_key = array_keys($this->second_array);
		$minute_key = array_keys($this->minute_array);
		while ( $k = array_pop($second_key) )
		{
			if ( $k <= $second_min )
			{
				unset( $this->second_array[$k] );
			}
			else
				break;
		}
		while ( $k = array_pop($minute_key) )
		{
			if ( $k <= $minute_min )
			{
				unset( $this->minute_array[$k] );
			}
			else
				break;
		}
	}

	public function GetCountSecondInteval($step=5)
	{
		if ( $step <= 0 )
			return 0;

		if ( $step > 60 )
			return self::GetCountMinuteInteval( ceil($step/60) );

		$second = time();
		$count = 0;
		for ( $i=0; $i<$step; $i++ )
		{
			$second_point = $second-$i;
			if (isset($this->second_array[$second_point]))
				$count += $this->second_array[$second_point];
		}
		return $count;
	}

	public function GetCountMinuteInteval($step=10)
	{
		if ( $step <= 0 )
			return 0;
		
		$minute = intval(time()/60);
		$count = 0;
		for ( $i=0; $i<$step; $i++ )
		{
			$minute_point = $minute-$i;
			if (isset($this->minute_array[$minute_point]))
				$count += $this->minute_array[$minute_point];
		}
		return $count;
	}

	public function Record()
	{
		$second = time();
		$minute = intval(time()/60);

		$this->second_array[$second] = isset($this->second_array[$second]) ? 
			($this->second_array[$second]+1) : 1;

		$this->minute_array[$minute] = isset($this->minute_array[$minute]) ? 
			($this->minute_array[$minute]+1) : 1;
	}
}
?>
