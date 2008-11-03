<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	glinus@gmail.com
 * @version		$Id$
 */

/**
 * JiWai.de PushMail Class
 */
class JWPushMail {
	/**
	 * Instance of this singleton
	 *
	 * @var JWPushMailLogic
	 */
	static private $instance__;

	/**
	 * idle 
	 *
	 * @var mSleepUsec
	 */
	static private $mSleepUsec	= 0;
	static private $mSleepUsecMax	= 300000; // 0.3s

	static private $msPushMailPath = 'pushmail/';
	static private $mMoWaitingDir = null;
	static private $mMoUnDealDir = null;
	static private $mMoDealedDir = null;
	static private $mMoQuarantinedDir = null;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWPushMailLogic
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
		self::SetMoDir( JWFile::GetStorageAbsRoot() . self::$msPushMailPath );
	}

	static public function SetMoDir( $rootDir ) {

		$rootDir = rtrim( $rootDir );
		self::$mMoDealedDir = $rootDir .'/d';
		self::$mMoWaitingDir = $rootDir .'/w';
		self::$mMoUnDealDir = $rootDir .'/m';
		self::$mMoQuarantinedDir = $rootDir .'/f';

		@mkdir( self::$mMoUnDealDir, 0777, true );
		@mkdir( self::$mMoDealedDir, 0777, true );
		@mkdir( self::$mMoQuarantinedDir, 0777, true );
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

				if( false == preg_match( '/-([^@]+@[^@]+)-([^@]+@[^@]+)$/', $dirname, $matches ) ) {
					echo "Malformed Email Address: $dirname\n";
					@rename($undealDirname, $quarantinedDirname);
					continue;
				}

				$mailFrom = $matches[1];
				$mailTo = $matches[2];
				$userRow = JWUser::GetUserInfo($mailTo, null, 'email');
				if( empty( $userRow ) ) {
					#echo "$email is not register in JiWai yet.\n";
					#@rename($undealDirname, $quarantinedDirname);
					#continue;
					$userRow = array('id' => 2802);
				}

				$pushMailArray = array();
				$pushMailArray['mailFrom'] = $mailFrom;
				$pushMailArray['mailTo'] = $mailTo;
				$pushMailArray['idUser'] = $userRow['id'];
				$pushMailArray['subject'] = uniqid('/tmp/PushMail');
				$pushMailArray['timeCreate'] = 0;
				
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
							$pushMailArray['subject'] = $content;
							continue;
						}

						if( $f == 'mail.tim' ) {
							$content = file_get_contents( $realfile );
							$contentTime = strtotime( $content );
							if( $contentTime > 0 ) {
								$pushMailArray['timeCreate'] = strtotime($content);
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
							$pushMailArray['imageFile'] = $realfile;
							$pushMailArray['imageSuffix'] = $suffix;
							if( $pushMailArray['timeCreate'] <= 0 ) {
								$pushMailArray['timeCreate'] = fileCTime($realfile);
							}
						}
						else if( $filetype=='text' && $suffix=='plain') 
						{
							$text = file_get_contents( $realfile );
							$status = mb_convert_encoding( $text, 'UTF-8', 'GB2312, UTF-8');
							$pushMailArray['status'] = $status;
						}
					}
				}
				
				if( isset( $pushMailArray['imageFile'] ) ){
					$ufilename = uniqid('/tmp/MMS') . '.' . $pushMailArray['imageSuffix'];
					@copy( $pushMailArray['imageFile'], $ufilename );

					$options = array(
							'thumbs' => array( 'origin', 'picture' ),
							'filename' => $pushMailArray['subject'],
						);
					$idPicture = JWPicture::SaveUserIcon($pushMailArray['idUser'],$ufilename,'MMS',$options);

					if( $idPicture ) 
					{
						$pushMailArray['idPicture'] = $idPicture;
						$readok = true;
					}
					else
					{
						$readok = false;
					}	
				}

				if( $readok && self::SaveToDirectMessage($pushMailArray) ){
					echo "[pushmail://${mailTo}] Push Mail Successed\n";
					if( false == file_exists( $dealedDirname ) ) {
						@rename($undealDirname, $dealedDirname);
					}else{
						do{
							$dealedDirname .= '-'.time();
						}while( false == file_exists( $dealedDirname ) );
						@rename($undealDirname, $dealedDirname);
					}
				}else{
					echo "[pushmail://${mailTo}] Push Mail Failed\n";
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

	static function SaveToDirectMessage($pushMailArray = array()){
		if( false == isset($pushMailArray['status']) || empty($pushMailArray['status']) ) {
			return false;
		}

		$mailFrom = $mailTo = $subject = $status = null;
		extract($pushMailArray, EXTR_IF_EXISTS);

		$message = <<<__MSG__
From: $mailFrom
To: $mailTo
Subject: $subject

$status
__MSG__;

		JWNudge::NudgeToUsers( $pushMailArray['idUser'], $message );
		return true;
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
