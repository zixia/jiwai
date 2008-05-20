<?php
require('Mail/mimeDecode.php');

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

	private $mFunction	= null;
	private $mResource	= null;

	private $mAttachments = array();
	private $mBoundary = null;

	private $mHeader = array();
	
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

	/**
	 * mIsInterceptable: null(willing to be intercepted), true,false
	 *
	 */
	private $mIsInterceptable = true;

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
			$base_name = basename( $fileName );
			if ( preg_match( '/^(\S+)__(\S+)__(\d+)_(\d+)/U', $base_name, $matches ) )
			{
				$this->mType = $matches[1];
				$this->mAddress= $matches[2];
			}
		}

		$raw_msg_content = file_get_contents( $fileName );

		if ( false===$raw_msg_content )
		{
			JWLog::Instance()->Log(LOG_ERR, "[$fileName] read failed");
			return false;
		}

		$this->mFile		= $fileName;
		$this->mCreateTime	= filemtime($fileName);
		$this->decodeMessage( $raw_msg_content );

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

	public function decodeMessage($content=null)
	{
		$params = array(
			'include_bodies' => true,
			'decode_bodies' => true,
			'decode_headers' => true,
			'input' => $content,
		);

		$output = Mail_mimeDecode::decode($params); 
		$headers = $output->headers;
		
		//Set head tag;
		foreach( $headers AS $name => $value )
		{
			$this->_SetHeadTagByPair($name, $value);
		}

		//parse body
		if ( false==isset($output->parts) )
		{
			// encoded body
			if ( isset($output->ctype_parameters) )
			{
				$charset = isset($output->ctype_parameters['charset']) 
					? strtoupper($output->ctype_parameters['charset']) : null;
				if ( $charset && 'UTF-8' != $charset )
					$body = mb_convert_encoding($output->body, 'UTF-8', $charset);

				$this->mBody = $this->_StripBody( $body );
			}
			//Maybe normal robot_msg -- Comptiable with OLD RobotMsg DATA
			else
			{   
				$charset = mb_detect_encoding($output->body);
				if ( 'UTF-8' == $charset )
					$body = $output->body;
				else
					$body = mb_convert_encoding($output->body, 'UTF-8', $charset);
				$this->mBody = $this->_StripBody( $body );
			}   
		}
		else
		{
			$parts = $output->parts;
			$this->mBoundary = $output->ctype_parameters['boundary'];

			foreach ( $parts AS $part )
			{   
				//Alternative Body
				if ( $part->ctype_secondary == 'alternative' )
				{   
					$part = $part->parts[0]; 
				}

				//Attachment
				if( isset($part->disposition) && 'attachment' == $part->disposition )
				{
					$file_name = $part->ctype_parameters['name'];
					$file_content = $part->body;
					$file_type = $part->ctype_primary.'/'.$part->ctype_secondary;
					$disposition = $part->disposition;

					$this->mAttachments[ $file_name ] = array(
						'content_type' => $file_type,
						'file_content' => $file_content,
					);
				}

				//Text-body
				else
				{
					if ( $part->ctype_secondary == "plain" )
					{
						$body = mb_convert_encoding($part->body, 'UTF-8', 'GB2312,UTF-8');
						$this->mBody = $this->_StripBody( $body );
					}
					if ( $part->ctype_secondary == 'html' )
					{
						$body = mb_convert_encoding($part->body, 'UTF-8', 'GB2312,UTF-8');
						$this->mBody = $this->_StripBody( $body );
					}
				}
			}
		}

		if ( null==$this->mBody )
		{
			$this->mBody = mb_convert_encoding( $this->_GetHeadTag('Subject'), 'UTF-8', 'GB2312,UTF-8' );
		}
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

	public function GetFunction()
	{
		return $this->mFunction;
	}
	public function SetFunction($function)
	{
		$this->mFunction= $function;
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
		/**if ( $this->mReadOnly )
			throw new JWException('cant modify readonly msg');
		*/

		$this->mBody = $this->_StripBody( $body );
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

	public function GetIsInterceptable()
	{
		return (false === $this->mIsInterceptable) ? false : true;
	}
	public function SetIsInterceptable( $isInterceptable)
	{
		$this->mIsInterceptable = (false === $isInterceptable) ? false : true;
	}



	public function Set($address, $type, $body, $file=null)
	{
		if ( $this->mReadOnly )
			throw new JWException('cant modify readonly msg');

		$this->mAddress	= $address;
		$this->mType	= $type;
		$this->mBody	= self::_StripBody( $body );
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

		foreach( $this->mHeader AS $key=>$value )
		{
			$file_contents .= "$key: $value\n";
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

		$this->mAddress = $address;
		$this->mType = $device;

		unset( $this->headTags[ "ADDRESS" ] );

		foreach( $this->headTags AS $key=>$value )
		{
			$this->SetHeader( $key, $value );	
		}

		return true;
	}

	public function SetHeader($name=null, $value=null)
	{
		if ( null==$name )
			return;

		$name = strtoupper($name);
		$this->mHeader[ $name ] = $value;

		if ( null===$value )
		{
			unset( $this->mHeader[ $name ] );
		}
	}

	public function GetHeader($name=null)
	{
		$name = strtoupper($name);
		if ( isset($this->mHeader[ $name ]) )
			return $this->mHeader[ $name ];

		return null;
	}

	public function GetHeaders()
	{
		return $this->mHeader;
	}

	public function SetHeaders($headers)
	{
		foreach( $headers AS $key=>$value )
		{
			$this->SetHeader( $key, $value );
		}
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

	private function _StripBody($body=null){
		$body = JWTextFormat::PreFormatRobotMsg( $body );
		$constant_name = 'JW_HARDLEN_' . strtoupper( $this->mType );
		if ( defined( $constant_name ) )
			$hardlength = constant( $constant_name );
		else
			$hardlength = 420;
		return mb_substr( trim($body), 0, $hardlength, 'UTF-8' );
	}
}
?>
