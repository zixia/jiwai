<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de StatusNotityQueue Class
 */
class JWMmsLogic {
	/**
	 * Instance of this singleton
	 *
	 * @var JWMmsLogic
	 */
	static private $instance__;

	/**
	 * idle 
	 *
	 * @var mSleepUsec
	 */
	static private $mSleepUsec	= 0;
	static private $mSleepUsecMax	= 300000; // 0.3s


	static private $mMoWaitingDir = null;
	static private $mMoUnDealDir = null;
	static private $mMoDealedDir = null;
	static private $mMoQuarantinedDir = null;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWMmsLogic
	 */
	static public function &instance()
	{
		if (!isset(self::$instance__)) {
			$class = __CLASS__;
			self::$instance__ = new $class;
		}
		return self::$instance__;
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
		self::SetMoDir( MMS_STORAGE_ROOT );        
	}

	static public function SetMoDir( $rootDir ) {

		$rootDir = rtrim( $rootDir );
		self::$mMoDealedDir = $rootDir .'/d';
		self::$mMoWaitingDir = $rootDir .'/w';
		self::$mMoUnDealDir = $rootDir .'/m';
		self::$mMoQuarantinedDir = $rootDir .'/f';

		@mkdir( self::$mMoUnDealDir, 777, true );
		@mkdir( self::$mMoDealedDir, 777, true );
		@mkdir( self::$mMoQuarantinedDir, 777, true );
	}

	static public function GetUnDealDir(){
		self::Instance();
		return self::$mMoUnDealDir;
	}

	static public function GetWaitingDir(){
		self::Instance();
		return self::$mMoWaitingDir;
	}

	static public function GetUndealMo(){
		$dirs = scandir( self::$mMoUnDealDir );
		$rtn = array();
		foreach( $dirs as $n ) {
			if( 0 !== strpos($n, '.') && is_dir( self::$mMoUnDealDir.'/'.$n ) ) {
				array_push( $rtn, $n );
			}
		}
		return $rtn;
	}

	static public function Run () {

		echo "MMS MO Queue Robot enters the mainLoop.\n";  
		self::Instance();

		while ( true ){
			try{
				self::MainLoop();
			}catch(Exception $e){
 				JWLog::Instance()->Log(LOG_ERR, 'notifyqueue main_loop exception' );
				echo "Exception: " .  $e->getMessage() . $e->getTraceAsString() . "\n";
				sleep(1);
			}
		}
	}

	static public function MainLoop(){
	
		$queueMos = self::GetUndealMo();	

		if ( empty( $queueMos ) ){
			self::IdleCircle();
		}else{
			# It's busy now:
			self::$mSleepUsec = 0;

			foreach( $queueMos as $dirname ){

				$undealDirname = self::$mMoUnDealDir . '/' . $dirname ;
				$dealedDirname = self::$mMoDealedDir .'/' . $dirname ;
				$quarantinedDirname = self::$mMoQuarantinedDir .'/' . $dirname ;

				if( false == preg_match( '/(1[\d]{10})$/', $dirname, $matches ) ) {
					echo "Not come from a mobile\n";
					@rename($undealDirname, $quarantinedDirname);
					continue;
				}

				$phone = $matches[1];
				$try = 0;
				$deviceRow = array();
				do{ 
					$deviceRow = JWDevice::GetDeviceDbRowByAddress($phone, 'sms'); 
					if( false == empty( $deviceRow ) )
						break;
				} while( ++$try < 3 );

				if( empty( $deviceRow ) ) {
					echo "$phone is not register in JiWai yet.\n";
					@rename($undealDirname, $quarantinedDirname);
					continue;
				}

				$mmsArray = array();
				$mmsArray['address'] = $phone;
				$mmsArray['idUser'] = $deviceRow['idUser'];
				$mmsArray['subject'] = uniqid('/tmp/Mms');
				$mmsArray['timeCreate'] = 0;
				
				$files = scandir( $undealDirname );

				$readok = true;

				foreach( $files as $f ) {

					$realfile = $undealDirname .'/'. $f ;
					if( is_file( $realfile ) ) {
						
						//parse subject
						if( $f == 'subject.sub' ) {
							$content = file_get_contents( $realfile );
							if( preg_match('/^=\?\w+\?(\w)\?(.*)\?=$/', $content, $matches ) ){
								switch( strtoupper( $matches[1] ) ){
									case 'B':
									$content = base64_Decode($matches[2]);
									break;
									case 'Q':
									$content = quoted_printable_decode($matches[2]);
									break;
								}
							}
							$content = mb_convert_encoding($content,'UTF-8','GB2312,UTF-8');
							$mmsArray['subject'] = $content;
							continue;
						}

						if( $f == 'mail.tim' ) {
							$content = file_get_contents( $realfile );
							$contentTime = strtotime( $content );
							if( $contentTime > 0 ) {
								$mmsArray['timeCreate'] = strtotime($content);
							}
							continue;
						}

						$pathInfo = pathInfo( $realfile );
						if( in_array( $pathInfo['extension'] , array('html','smi','smil') ) )
							continue;

						$contentType = mime_content_type( $realfile );
						$filetype = $suffix = null;
						@list($filetype, $suffix) = explode( '/', $contentType );
						
						//Fetch image and text
						if( $filetype == 'image' ) 
						{
							$mmsArray['imageFile'] = $realfile;
							$mmsArray['imageSuffix'] = $suffix;
							if( $mmsArray['timeCreate'] <= 0 ) {
								$mmsArray['timeCreate'] = fileCTime($realfile);
							}
						}
						else if( $filetype=='text' && $suffix=='plain') 
						{
							$text = file_get_contents( $realfile );
							$status = mb_convert_encoding( $text, 'UTF-8', 'GB2312, UTF-8');
							$mmsArray['status'] = $status;
						}
					}
				}
				
				if( isset( $mmsArray['imageFile'] ) ){
					$ufilename = uniqid('/tmp/MMS') . '.' . $mmsArray['imageSuffix'];
					@copy( $mmsArray['imageFile'], $ufilename );

					$options = array(
							'thumbs' => array( 'origin', 'thumb48', 'thumb96', 'picture' ),
							'filename' => $mmsArray['subject'],
						);
					$idPicture = JWPicture::SaveUserIcon($mmsArray['idUser'],$ufilename,'MMS',$options);

					if( $idPicture ) 
					{
						$mmsArray['idPicture'] = $idPicture;
						$readok = true;
					}
					else
					{
						$readok = false;
					}	
				}

				if( $readok && self::SaveToStatus($mmsArray) ){
					echo "[sms://${phone}] Update MMS Status Successed\n";
					if( false == file_exists( $dealedDirname ) ) {
						@rename($undealDirname, $dealedDirname);
					}else{
						do{
							$dealedDirname .= '-'.time();
						}while( false == file_exists( $dealedDirname ) );
						@rename($undealDirname, $dealedDirname);
					}
				}else{
					echo "[sms://${phone}] Update MMS Status Failed\n";
					if( false == file_exists( $quarantinedDirname ) ) {
						@rename($undealDirname, $quarantinedDirname);
					}else{
						do{
							$quarantinedDirname .= '-'.time();
						}while( false == file_exists( $quarantinedDirname ) );
						@rename($undealDirname, $quarantinedDirname);
					}
				}
			}
		}
	}

	static function SaveToStatus($mmsArray = array()){

		$device = 'sms';
		$serverAddress = 'm@jiwai.de';
		$timeCreate = $mmsArray['timeCreate'];

		if( false == isset($mmsArray['status']) || empty($mmsArray['status']) ) {
			$userInfo = JWUser::GetUserInfo( $mmsArray['idUser'] );
			$mmsArray['status'] = "$userInfo[nameFull]上传了一条彩信。";
		}

		$serverAddress = 'm@jiwai.de';
		$isSignature = 'N';
		$options = isset( $mmsArray['idPicture'] ) ? array(
					'idPicture' => $mmsArray['idPicture'],	
					'isMms' => 'Y',
					'nofilter' => true,
				) : array();

		return JWSns::UpdateStatus( $mmsArray['idUser'],
						$mmsArray['status'],
						$device,
						$timeCreate,
						$isSignature,
						$serverAddress,
					       	$options );	
	}

	static function IdleCircle()
	{
		//print ".";
		if (self::$mSleepUsec)
		{
			usleep (self::$mSleepUsec);
			self::$mSleepUsec *= 2;

			if ( self::$mSleepUsec > self::$mSleepUsecMax )
			{
				self::$mSleepUsec = self::$mSleepUsecMax;
			}

		}
		else
		{
			self::$mSleepUsec = 1;
		}
	}
}
?>
