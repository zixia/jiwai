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
class JWMmsMail {
	/**
	 * Instance of this singleton
	 *
	 * @var JWMmsMail
	 */
	static private $instance__;

	/**
	 * idle 
	 *
	 * @var mSleepUsec
	 */
	static private $mSleepUsec		= 0;
	static private $mSleepUsecMax	= 300000; // 0.3s


	static private $mMailUnDealDir = null;
	static private $mMailDealedDir = null;
	static private $mMailQuarantinedDir = null;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWMmsMail
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
	}

	static public function SetMailDir( $rootDir ) {
		$rootDir = rtrim( $rootDir );
		self::$mMailDealedDir = $rootDir .'/d';
		self::$mMailUnDealDir = $rootDir .'/m';
		self::$mMailQuarantinedDir = $rootDir .'/f';

		@mkdir( self::$mMailUnDealDir, 777, true );
		@mkdir( self::$mMailDealedDir, 777, true );
		@mkdir( self::$mMailQuarantinedDir, 777, true );
	}

	static public function GetUndealMail(){
		$dirs = scandir( self::$mMailUnDealDir );
		$rtn = array();
		foreach( $dirs as $n ) {
			if( 0 !== strpos($n, '.') && is_dir( self::$mMailUnDealDir.'/'.$n ) ) {
				array_push( $rtn, $n );
			}
		}
		return $rtn;
	}

	static public function Run () {

		echo "MMS-Mail 机器人已经进入主循环，可以处理数据了...\n";

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
	
		$queueMails = self::GetUndealMail();	

		if ( empty( $queueMails ) ){
			self::IdleCircle();
		}else{
			# It's busy now:
			self::$mSleepUsec = 0;

			foreach( $queueMails as $dirname ){

				$undealDirname = self::$mMailUnDealDir . '/' . $dirname ;
				$dealedDirname = self::$mMailDealedDir .'/' . $dirname ;
				$quarantinedDirname = self::$mMailQuarantinedDir .'/' . $dirname ;

				if( false == preg_match( '/(1[\d]{10})$/', $dirname, $matches ) ) {
					echo "Not come from a mobile\n";
					@rename($undealDirname, $quarantinedDirname);
					continue;
				}

				$phone = $matches[1];

				$deviceRow = JWDevice::GetDeviceDbRowByAddress($phone, 'sms');
				if( empty( $deviceRow ) ) {
					@rename($undealDirname, $quarantinedDirname);
					continue;
				}

				$mmsArray = array();
				$mmsArray['address'] = $phone;
				$mmsArray['idUser'] = $deviceRow['idUser'];
				
				$files = scandir( $undealDirname );

				foreach( $files as $f ) {

					$realfile = $undealDirname .'/'. $f ;
					if( is_file( $realfile ) ) {

						$pathInfo = pathInfo( $realfile );
						if( in_array( $pathInfo['extension'] , array('html','smi','smil') ) )
							continue;

						$contentType = mime_content_type( $realfile );
						$filetype = $suffix = null;
						@list($filetype, $suffix) = explode( '/', $contentType );
						
						//Fetch image and text
						if( $filetype == 'image' ) {

							$ufilename = tempnam( "/tmp", 'Mms' ) . '.' . $suffix;
							@copy( $realfile, $ufilename );

							$idPicture = JWPicture::SaveUserIcon($mmsArray['idUser'],$ufilename, 'MMS');
							if( $idPicture ) {
								$mmsArray['idPicture'] = $idPicture;
							}else{
								@rename($undealDirname, $quarantinedDirname);
								break;
							}	

						}else if($filetype =='text') {
							$mmsArray['status'] = file_get_contents( $realfile );
						}
					}
				}

				if( self::SaveToStatus($mmsArray) ){
					echo "[sms://${phone}] Update MMS Status Successed\n";
					@rename($undealDirname, $dealedDirname);
				}else{
					echo "[sms://${phone}] Update MMS Status Failed\n";
					@rename($undealDirname, $quarantinedDirname);
				}
			}
		}
	}

	static function SaveToStatus($mmsArray = array()){

		$userInfo = JWUser::GetUserInfo( $mmsArray['idUser'] );
		$device = 'sms';
		$serverAddress = 'm@jiwai.de';
		$options = array(
				'idPicture' => $mmsArray['idPicture'],	
			);

		if( false == isset($mmsArray['status']) || empty($mmsArray['status']) ) {
			$mmsArray['status'] = "$userInfo[nameFull]上传了一条彩信。";
		}

		$serverAddress = 'm@jiwai.de';
		$isSignature = 'N';
		$options = array(
				'idPicture' => $mmsArray['idPicture'],	
			);


		return JWSns::UpdateStatus( $mmsArray['idUser'], 
						$mmsArray['status'], 
						$device, 
						null, 
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
