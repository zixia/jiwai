<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @date		07/08/2007
 */

/**
 * JiWai.de Mutex Class
 */
class JWMutex {
	/**
	 * sem resource
	 *
	 * @var
	 */
	private $msSemResource;

	private $msIsAcquired;


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct($key)
	{
		if ( empty($key) )
			throw new JWException('need key!');

		// 转换为正整数
		if ( is_int($key) )
			$uint_key = abs($key);
		else if ( is_object($key) || is_array($key) )
			$uint_key = sprintf( '%u', crc32(md5(serialize($key))) );
		else
			$uint_key = sprintf( '%u', crc32($key) );

		$this->msSemResource = sem_get( $uint_key, 1 );
	
		if ( empty($this->msSemResource) )
			throw new JWException('sem resource limit exceed!');
			
		$this->msIsAcquired = false;
	}

    /**
    * Destructing method, write everything left
    *
    */
    function __destruct()
    {
		if ( !empty($this->msSemResource) )
        	sem_remove($this->msSemResource);
    }

	public function Acquire()
	{
		if ( ! sem_acquire($this->msSemResource) )
			return false;

		$this->msIsAcquired = true;
		return true;
	}

	public function Release()
	{
		if ( ! $this->msIsAcquired )
			return true;

		if ( sem_release($this->msSemResource) )
			return true;

		return false;
	}
}
?>
