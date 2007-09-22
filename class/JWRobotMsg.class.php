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
	private $mServerAddress	= null;
	private $mLinkId	= null;
	private $mMsgtype	= null;
	private $mBody		= null;
	private $mFile		= null;
	private $mCreateTime	= null;
	
	/**
	  * idUserConference; not need check
	  */
	private $mIdUserConference = null;

	/**
	 * A hash array used to store Message Head Tag from File 
	 */
	private $headTags = array();

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

		if ( false===$raw_msg_content )
		{
			JWLog::Instance()->Log(LOG_ERR, "[$fileName] read failed");
			return false;
		}

		$body = null;
		$lines = explode("\n", $raw_msg_content );
		$contentBegin = false;

		foreach( $lines as $line ){
			if( true==$contentBegin ){
				$body .= "$line\n";
				continue;
			}
			if( ! $line ) {
				$contentBegin = true ;
			}else{
				$this->_SetHeadTagByLine($line);
			}
		}

		$this->mBody		= trim( $body ); // 去掉末尾行的\n
		$this->mFile		= $fileName;
		$this->mCreateTime	= filemtime($fileName);
		if( !$this->_SetPropertiesByTagHeads() ){
			JWRobot::QuarantineMsg($this);
			//throw new JWException('Essential properties[address/device] not given');
		}

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
	public function GetLinkId()
	{
		return $this->mLinkId;
	}
	public function SetLinkId($linkId)
	{
		$this->mLinkId = $linkId;
		$this->mIsValid = null;
	}
	public function GetMsgtype()
	{
		return $this->mMsgtype;
	}
	public function SetMsgtype($msgtype)
	{
		$this->mMsgtype = $msgtype;
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
	public function GetServerAddress()
	{
		return $this->mServerAddress;
	}
	public function SetServerAddress($serverAddress)
	{
		if ( $this->mReadOnly )
			throw new JWException('cant modify readonly msg');

		$this->mServerAddress = $serverAddress;
		$this->mIsValid = null;
	}
	public function GetBody()
	{
		return $this->mBody;
	}
	public function SetBody($body)
	{
		/**if ( $this->mReadOnly )
			throw new JWException('cant modify readonly msg');
		*/

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

	public function GetIdUserConference()
	{
		return $this->mIdUserConference;
	}
	public function SetIdUserConference( $idUserConference )
	{
		$this->mIdUserConference = $idUserConference ;
	}



	public function Set($address, $type, $body, $serverAddress=null, $linkId=null, $file=null)
	{
		if ( $this->mReadOnly )
			throw new JWException('cant modify readonly msg');

		$this->mAddress	= $address;
		$this->mType	= $type;
		$this->mBody	= $body;
		$this->mServerAddress	= $serverAddress;
		$this->mLinkId	= $linkId;
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

		$file_contents =  "ADDRESS: " . $this->mType . "://" . $this->mAddress . "\n";
		if( $this->mServerAddress != null ) {
			$file_contents .= "SERVERADDRESS: " . $this->mServerAddress . "\n";
		}
		if( $this->mMsgtype != null ) {
			$file_contents .= "MSGTYPE: " . $this->mMsgtype . "\n";
		}
		if( $this->mLinkId != null ) {
			$file_contents .= "LINKID: " . $this->mLinkId . "\n";
		}

		$file_contents .= "\n";
		$file_contents .= $this->mBody ;

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

	private function _SetHeadTagByPair($tagName=null, $value=null){
		if( null == $tagName )
			return;
		$this->headTags[ strtoupper( $tagName ) ] = $value;
	}

	private function _SetHeadTagByLine($lineString=null){
		if( null == $lineString )
			return;
		if( preg_match( '/^(\w+):\s+(.+)$/', $lineString, $matches ) ){
			$this->headTags[ strtoupper(trim($matches[1]))] = trim($matches[2]);
		}	
	}

	private function _SetPropertiesByTagHeads(){
		//Device and Address
		$deviceAndAddress = $this->_GetHeadTag('Address');
		if( null == $deviceAndAddress )
			return false;
		@list( $device, $address ) = explode('://', $deviceAndAddress );
		if( ! $device || ! $address )
			return false;

		//MSGTYPE
		$msgtype = $this->_GetHeadTag('MsgType');
		$serverAddress = $this->_GetHeadTag('ServerAddress');
		$linkId = $this->_GetHeadTag('LinkId');
		
		//Set properties
		$this->mAddress = $address ;
		$this->mType	= $device ;
		$this->mMsgtype = $msgtype ;
		$this->mServerAddress = $serverAddress ;
		$this->mLinkId = $linkId;

		return true;
	}

	private function _GetHeadTag($tagName=null){
		if ( $tagName == null )
			return null;
		$tagName = strtoupper( $tagName );
		if( isset( $this->headTags[$tagName] ) ){
			return $this->headTags[ $tagName ];
		}
		return null;
	}
}
?>
