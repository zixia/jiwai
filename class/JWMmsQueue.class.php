<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de MmsQueue Class
 */
class JWMmsQueue {
	/**
	 * Instance of this singleton
	 *
	 * @var JWMmsQueue
	 */
	static private $instance__;

	/**
	 * dealStatus
	 */
	const DEAL_NONE = 1;
	const DEAL_DEALED = 2;
	const DEAL_QUARANTINED = 3;

	/**
	 * idle 
	 *
	 * @var mSleepUsec
	 */
	static private $mSleepUsec		= 0;
	static private $mSleepUsecMax	= 300000; // 0.3s

	/**
	 * Instance of this singleton class
	 *
	 * @return JWMmsQueue
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

	static public function Create($mobileNo, $idStatus){
        
		$idStatus = JWDB::CheckInt( $idStatus );

		JWDB::SaveTableRow( 'MmsQueue' , array(
			'idStatus' => $idStatus,
			'mobileNo' => $mobileNo,
		));
	}

	static public function GetMtQueue( $num = 100 ){
		$num = JWDB::CheckInt( $num );

		$sql = <<<SQL
SELECT * FROM MmsQueue
	ORDER BY id ASC
	LIMIT $num
SQL;
		$result = JWDB::GetQueryResult( $sql , true);
		return $result;
	}

	static public function SetDealedQueue( $id ) {
		settype( $id, 'array');
		if( empty( $id ) )
			return;
		$idCondition = implode( ',', $id );

			$sql = <<<SQL
DELETE FROM MmsQueue
	WHERE id IN ($idCondition)
	AND idStatus IS NOT NULL
SQL;
		return JWDB::Execute( $sql );
	}

	static public function Run () {

		echo "MMS MT Queue Robot enters the mainLoop.\n";

		while ( true ){
			try{
				self::MainLoop();
			}catch(Exception $e){
 				JWLog::Instance()->Log(LOG_ERR, 'Mms mt main_loop exception' );
				echo "Exception: " .  $e->getMessage() . $e->getTraceAsString() . "\n";
				sleep(1);
			}
		}
	}

	static public function MainLoop(){
	
		$queueMt = self::GetMtQueue( 100 );	

		if ( empty( $queueMt ) ){
			self::IdleCircle();
		}else{
			# It's busy now:
			self::$mSleepUsec = 0;

			foreach( $queueMt as $mt ){

				$id = $mt['id'];
				$mobileNo = $mt['mobileNo'];
				$idStatus = $mt['idStatus'];

				JWMms::SendStatusMMSMt( $mobileNo, $idStatus );
			}

			self::SetDealedQueue( $id );
		}
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
