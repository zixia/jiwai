<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Robot Msg Class
 */
class JWRobotMsg {
	/**
	 * Constructing method, save initial state
	 *
	 */
	private $mAddress	= null;
	private $mType		= null;
	private $mBody		= null;
	private $mFile		= null;
	private $mCreateTime	= null;

	/**
	 * if this instance can be modifyed.
	 */
	private $mReadOnly	= false;

	/**
	 * mIsValid: null(need check), true,false
	 *
	 */
	private $mIsValid	= null;

	function __construct ($fileName=null)
	{
		if ( empty($fileName) )
			return;

		$this->Load($fileName);
	}


	function Load($fileName=null)
	{
		if ( null==$fileName )
		{
			if ( empty($this->mFile) )
				throw new JWException('Load without filename');
		}
		else
		{
			// set file name first, so if any err, 
			// we can quarantine this file.
			$this->mFile		= $fileName;
		}

		$raw_msg_content = file_get_contents( $fileName );

		if ( false===file_get_contents($fileName) )
		{
			JWLog::Instance()->Log(LOG_ERR, "[$fileName] read failed");
			return false;
		}

		if ( ! preg_match('/^(.+?)\n\n(.+)$/s', $raw_msg_content, $matches) )
		{
			
			JWLog::Instance()->Log(LOG_ERR, "parse_msg($raw_msg_content) parse format failed");
			return false;
		}

		$head = $matches[1];
		$body = $matches[2];

		if ( ! preg_match('/^ADDRESS: ([^\/]+):\/\/(.+)$/',$head,$matches) ){
			// ie: msn://zixia@zixia.net
			throw new JWException("parse_msg($head) parse ADDRESS: failed");
		}

		$this->mType		= $matches[1];
		$this->mAddress		= $matches[2];

		$this->mBody		= $body;
		$this->mFile		= $fileName;
		$this->mCreateTime	= filemtime($fileName);

		// we prevent modify a RoboMsg which load from the file.
		// when we need to create a RobotMsg, we must create a new one
		// instead of modify the one that load from file,
		// this is helpful to prevent make mistake that developer may save a new msg 
		// to a existing file.
		$this->mReadOnly	= true;

		return $this->IsValid(true);
	}


	public function IsValid($forceCheck=false)
	{
		if ( null===$this->mIsValid || $forceCheck )
		{
			# first set true, then check unvalid values.
			$this->mIsValid = true;

			if ( empty($this->mAddress) || empty($this->mType) || empty($this->mBody) )
				$this->mIsValid = false;

			if ( ! JWDevice::IsValid($this->mAddress,$this->mType) )
				$this->mIsValid = false;
		}

		return $this->mIsValid;
	}


	public function GetAddress()
	{
		return $this->mAddress;
	}
	public function SetAddress($address)
	{
		if ( $this->mReadOnly )
			throw new JWException('cant modify readonly msg');

		$this->mAddress = $address;
		$this->mIsValid = null;
	}
	public function GetType()
	{
		return $this->mType;
	}
	public function SetType($type)
	{
		if ( $this->mReadOnly )
			throw new JWException('cant modify readonly msg');

		$this->mType = $type;
		$this->mIsValid = null;
	}
	public function GetBody()
	{
		return $this->mBody;
	}
	public function SetBody($body)
	{
		if ( $this->mReadOnly )
			throw new JWException('cant modify readonly msg');

		$this->mBody = $body;
		$this->mIsValid = null;
	}
	public function GetFile()
	{
		return $this->mFile;
	}
	public function SetFile($file)
	{
		if ( $this->mReadOnly )
			throw new JWException('cant modify readonly msg');

		$this->mFile = $file;
	}
	public function GetCreateTime()
	{
		return $this->mCreateTime;
	}



	public function Set($address, $type, $body, $file=null)
	{
		if ( $this->mReadOnly )
			throw new JWException('cant modify readonly msg');

		$this->mAddress	= $address;
		$this->mType	= $type;
		$this->mBody	= $body;
		$this->mFile	= $file;

		$this->mIsValid	= $this->IsValid(true);
	}

	public function Save($fileName=null, $fileNameTmp=null)
	{
		if ( $this->mReadOnly )
			throw new JWException('cant modify readonly msg');

		if ( ! empty($fileName))
			$this->mFile = $fileName;

		if ( empty($this->mFile) || ! $this->IsValid() )
			throw new JWException("can't save msg");


		$file_contents = 	 "ADDRESS: " . $this->mType . "://" . $this->mAddress . "\n"
							."\n"
							.$this->mBody
						;

		$ret = true;

		if ( empty($fileNameTmp) )
		{
			$ret = $ret && file_put_contents($this->mFile, $file_contents);
		}
		else
		{
			/*
		 	 *	保证文件出现的原子性：inode和文件内容同时出现
			 */
			$ret = $ret && file_put_contents($fileNameTmp, $file_contents);
			$ret = $ret && link($fileNameTmp, $this->mFile);
			$ret = $ret && unlink($fileNameTmp);
		}
		
		return $ret;
	}

	
	public function Destroy()
	{
		if ( isset($this->mFile) )
			$ret = unlink($this->mFile);
		else
			$ret = true;

		$this->mAddress		= null;
		$this->mType		= null;
		$this->mBody		= null;
		$this->mIsValid		= null;
		$this->mReadOnly	= null;;

		return $ret;
	}

	function GenFileName($address=null, $type=null)
	{
		list($usec, $sec) = explode(" ", microtime());
		$usec *= 1000000;

		if ( empty($address) || empty($type) )
		{
			if ( ! $this->IsValid() )
				throw new JWException('msg not valid');
			
			return $this->mType . '__' 
							. $this->mAddress
							. '__'
							. "${sec}_$usec"
							;
		}
		else
		{
			return "${type}__${address}__${sec}_$usec";
		}
	}


}
?>
