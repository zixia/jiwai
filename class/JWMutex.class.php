<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @date		07/08/2007
 */

/**
 * JiWai.de Mutex Class
 *
 *	XXX：需要调整高系统设置的 sem 上限：最后一个数字改为 70000
	 zixia bbs 的数值： kernel.sem = 16 1120000 128 70000
 *
 */
class JWMutex {
	/**
	 * sem resource
	 *
	 * @var
	 */
	private $mMutexHandle;
	private	$mMutexKey;

	private $mIsAcquired;

	private	$mSyslog;

	private $mBackend;

	/**
	 *	支持两种方式：
			1、sem ipc
			2、flock
	 *
	 */

	const	SEM		= 1;
	const	FLOCK	= 2;


	/**
	 *	所有的 mutex 会共享 MAX_SEM_NUM 个 sem，为了提高性能，我们允许等待一些和自己无关的 mutex release.
	 *	设置为 65535 个，可以基本上认为不会有冲突
	 */
	// 正式运营数据大一些
	//const	MAX_SEM_NUM		= 0x0000FFFF;

	// debug 时小一点
	const	MAX_SEM_NUM		= 0x000000FF;
	const	SEM_KEY_PREFIX	= 0xFFFF0000;

	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct($key, $backend=self::FLOCK)
	{
		if ( empty($key) )
			throw new JWException('need key!');

		$this->mBackend	= $backend;

		//$this->mSyslog = JWLog::Instance('Mutex');

		// 转换为正整数
		if ( is_int($key) )
			$this->mMutexKey = abs($key);
		else if ( is_object($key) || is_array($key) )
			$this->mMutexKey = sprintf( '%u', crc32(md5(serialize($key))) );
		else
			$this->mMutexKey = sprintf( '%u', crc32($key) );


		switch ( $this->mBackend )
		{
			case self::SEM:
				$this->mMutexKey %= self::MAX_SEM_NUM;
				$this->mMutexKey |= self::SEM_KEY_PREFIX;

				$sem_resource = sem_get( $this->mMutexKey, 1 );

				if ( empty($sem_resource) )
					throw new JWException('sem resource limit exceed!');

				$this->mMutexHandle = $sem_resource;

				break;

			default:
				//fall to FILE
			case self::FLOCK:
				$config 			= JWConfig::Instance();
				$mutex_file_root 	= $config->directory->mutex;

				$mutex_file			= $mutex_file_root . $this->mMutexKey;

				$fp = @fopen($mutex_file, "r");
			
				if ( empty($fp) )
				{
					$fp = fopen($mutex_file, "w");

					if ( empty($fp) )
						throw new JWException("mutex file open failed! [$mutex_file_root]");
				}

				$this->mMutexHandle = $fp;

				break;
		}
	
			
		$this->mIsAcquired = false;
	}

    /**
    * Destructing method, write everything left
    *
    */
    function __destruct()
    {
		//$this->mSyslog->LogMsg('Removing key ' . $this->mMutexKey);	

/*
		有了 MAX_SEM_NUM ，我们就不 remove 了
		if ( !empty($this->mMutexHandle) )
        	sem_remove($this->mMutexHandle);
*/
    }

	public function Acquire()
	{
return true;
		//$this->mSyslog->LogMsg('Acquiring key ' . $this->mMutexKey);	

		switch ( $this->mBackend )
		{
			case self::SEM:
				if ( ! sem_acquire($this->mMutexHandle) )
					return false;
				break;

			default:
				// fall to FILE
			case self::FLOCK:
				if ( ! flock($this->mMutexHandle, LOCK_EX) )
					return false;
				break;
		}
		$this->mIsAcquired = true;

		//$this->mSyslog->LogMsg('Acquired key ' . $this->mMutexKey);	

		return true;
	}

	public function Release()
	{
		//$this->mSyslog->LogMsg('Releasing key ' . $this->mMutexKey);	

		if ( ! $this->mIsAcquired )
			return true;

		switch ( $this->mBackend )
		{
			case self::SEM:
				if ( ! sem_release($this->mMutexHandle) )
					return false;
				break;

			default:
				// fall to file
			case self::FLOCK:
				if ( ! flock($this->mMutexHandle, LOCK_UN) )
					return false;
				break;
		}

		return true;
	}
}
?>
