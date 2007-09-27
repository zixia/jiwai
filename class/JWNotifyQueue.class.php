<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	seek@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de NotifyQueue Class
 */
class JWNotifyQueue {
	/**
	 * Instance of this singleton
	 *
	 * @var JWNotifyQueue
	 */
	static private $instance__;

	/**
	 * dealStatus
	 */
	const DEAL_NONE = 1;
	const DEAL_DEALED = 2;
	const DEAL_QUARANTINED = 3;


	/**
	 * notifyQueue type
	 */
	const T_STATUS = 'STATUS';
	const T_MMS = 'MMS';
	const T_NUDGE = 'NUDGE';
	const T_INVITE = 'INVITE';
	const T_CONFERENCE = 'CONFERENCE';
	const T_WEBNUDGE = 'WEBNUDGE';
	const T_UNKNOWN = 'UNKNOWN';

	/**
	 * idle 
	 *
	 * @var mSleepUsec
	 */
	static private $mSleepUsec	= 0;
	static private $mSleepUsecMax	= 300000; // 0.3s

	/**
	 * Instance of this singleton class
	 *
	 * @return JWNotifyQueue
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

	static public function Create( $idUserFrom=null, $idUserTo=null, $type=self::T_STATUS, $extraInfo=array() ){
        
		$idUserFrom = ( $idUserFrom ) ? JWDB::CheckInt( $idUserFrom ) : null;
		$idUserTo = ( $idUserTo ) ? JWDB::CheckInt( $idUserTo ) : null;

		$metaInfo = self::EncodeBase64Serialize( $extraInfo );

		return JWDB::SaveTableRow( 'NotifyQueue' , array(
					'idUserFrom' => $idUserFrom,
					'idUserTo' => $idUserTo,
					'type' => $type,
					'metaInfo' => $metaInfo,
				));
	}

	static public function GetUnDealStatus( $num = 100 ){
		$num = JWDB::CheckInt( $num );

		$sql = <<<SQL
SELECT * FROM NotifyQueue
	WHERE dealStatus='NONE'
	ORDER BY id ASC
	LIMIT $num
SQL;

		$rtn = array();

		$result = JWDB::GetQueryResult( $sql , true);
		if( is_array( $result ) ) {
			foreach( $result as $k=>$one ) {
				$one['metaInfo'] = self::DecodeBase64Serialize( $one['metaInfo'] );
				$rtn[ $k ] = $one;
			}
		}

		return $rtn;
	}

	static public function SetDealStatus( $id, $dealStatus = self::DEAL_DEALED ){
		settype( $id, 'array');
		if( empty( $id ) )
			return;
		$idCondition = implode( ',', $id );
		
		$sql = <<<SQL
UPDATE NotifyQueue 
	SET dealStatus='$dealStatus'
	WHERE id IN ($idCondition)
SQL;

		/*
		 * 2007-08-08
		 */
		if( $dealStatus == self::DEAL_DEALED ){
			$sql = <<<SQL
DELETE FROM NotifyQueue
	WHERE id IN ($idCondition)
SQL;
		}

		return JWDB::Execute( $sql );
	}

	/**
	 * 总控
	 */
	static public function Run () {

		echo "NotifyQueue Robot enter in the main loop...\n";

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
	
		$notifyQueue = self::GetUnDealStatus( 100 );	

		if ( empty( $notifyQueue ) ){
			self::IdleCircle();
		}else{
			# It's busy now:
			self::$mSleepUsec = 0;

			foreach( $notifyQueue as $queue ){

				switch( $queue['type'] ) {
					case self::T_CONFERENCE:
					case self::T_MMS:
					case self::T_STATUS:
					{
						JWNotify::NotifyStatus( $queue );
					}
					break;
					case self::T_INVITE:
					{
						JWNotify::NotifyInvite( $queue );
					}
					break;
					case self::T_WEBNUDGE:
					{
						JWNotify::NotifyWebNudge( $queue );
					}
					break;
				}
				self::SetDealStatus( $queue['id'], self::DEAL_DEALED );
			}

		}
	}

	/**
	 * IdleCircle
	 */
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

	/**
	 * Encode metaInfo
	 */
	static private function EncodeBase64Serialize( $metaInfo = array()){
		return Base64_Encode( serialize( $metaInfo ) );
	}

	/**
	 * Decode metaInfo 
	 */
	static private function DecodeBase64Serialize( $metaString ) {
		return @unserialize( Base64_Decode( $metaString ) );
	}
}
?>
