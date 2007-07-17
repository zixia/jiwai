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

	private	$msSemKey;

	private	$msSyslog;
	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct($key)
	{
		if ( empty($key) )
			throw new JWException('need key!');

		$this->msSyslog = JWLog::Instance('Mutex');

		// 转换为正整数
		if ( is_int($key) )
			$this->msSemKey = abs($key);
		else if ( is_object($key) || is_array($key) )
			$this->msSemKey = sprintf( '%u', crc32(md5(serialize($key))) );
		else
			$this->msSemKey = sprintf( '%u', crc32($key) );

		$this->msSemResource = sem_get( $this->msSemKey, 1 );
	
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
		$this->msSyslog->LogMsg('Removing key ' . $this->msSemKey);	

		if ( !empty($this->msSemResource) )
        	sem_remove($this->msSemResource);
    }

	public function Acquire()
	{
		//$this->msSyslog->LogMsg('Acquiring key ' . $this->msSemKey);	

		if ( ! sem_acquire($this->msSemResource) )
			return false;

		$this->msIsAcquired = true;

		$this->msSyslog->LogMsg('Acquired key ' . $this->msSemKey);	

		return true;
	}

	public function Release()
	{
		$this->msSyslog->LogMsg('Releasing key ' . $this->msSemKey);	

		if ( ! $this->msIsAcquired )
			return true;

		if ( sem_release($this->msSemResource) )
			return true;

		return false;
	}
}
?>
