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
class JWStatusNotifyQueue {
	/**
	 * Instance of this singleton
	 *
	 * @var JWStatusNotifyQueue
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
	 * @return JWStatusNotifyQueue
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

	static public function Create($idUser, $idStatus, $time=null, $extraInfo=array()){
		$idUser = JWDB::CheckInt( $idUser );
		$idStatus = JWDB::CheckInt( $idStatus );

		$time = ( null == $time ) ? time() : $time;
		$timeCreate = date("Y-m-d H:i:s", $time);

		$metaInfo = json_encode( $extraInfo );

		JWDB::SaveTableRow( 'StatusNotifyQueue' , array(
					'idUser' => $idUser,
					'idStatus' => $idStatus,
					'timeCreate' => $timeCreate,
					'metaInfo' => $metaInfo,
					));
	}

	static public function GetUndealStatus( $num = 100 ){
		$num = JWDB::CheckInt( $num );

		$sql = <<<SQL
SELECT * FROM StatusNotifyQueue
	WHERE dealStatus='NONE'
	ORDER BY id ASC
	LIMIT $num
SQL;
		$result = JWDB::GetQueryResult( $sql , true);
		return $result;
	}

	static public function SetStatusDealStatus( $id, $dealStatus = self::DEAL_DEALED ){
		settype( $id, 'array');
		if( empty( $id ) )
			return;
		$idCondition = implode( ',', $id );
		$dealStatusString = self::GetDealStatusString( $dealStatus );
		
		$sql = <<<SQL
UPDATE StatusNotifyQueue 
	SET dealStatus='$dealStatusString'
	WHERE id IN ($idCondition)
SQL;
		return JWDB::Execute( $sql );
	}

	static public function GetDealStatusString( $dealStatus = self::DEAL_NONE ){
		switch( $dealStatus ) {
			case self::DEAL_NONE:
				return 'NONE';
			case self::DEAL_DEALED:
				return 'DEALED';
			case self::DEAL_QUARANTINED:
				return 'QUARANTINED';
			default:
				throw new JWException("Unsupport dealStatus");
		}
	}


	static public function Run () {

		echo "Notify队列处理机器人已经进入主循环，可以处理通知数据了...\n";

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
	
		$queueStatuses = self::GetUndealStatus( 100 );	

		if ( empty( $queueStatuses ) ){
			self::IdleCircle();
		}else{
			# It's busy now:
			self::$mSleepUsec = 0;

			foreach( $queueStatuses as $notify ){
				$id = $notify['id'];
				$idUser = $notify['idUser'];

				$metaInfo = @json_decode($notify['metaInfo']);

				if( $metaInfo ){

					//var_dump( $metaInfo );
					if( is_object( $metaInfo ) ){	
						$status = $metaInfo->status;
						$idUserReplyTo = $metaInfo->idUserReplyTo;
						$smssuffix = $metaInfo->smssuffix;
					}else if( is_array( $metaInfo ) ){
						$status = @$metaInfo['status'];
						$idUserReplyTo = @$metaInfo['idUserReplyTo'];
						$smssuffix = @$metaInfo['smssuffix'];
					}else{
						self::SetStatusDealStatus( $id, self::DEAL_QUARANTINED );
						continue;
					}

					/**
					 * notify to other
					 */
					echo "idUser: $idUser, idUserReplyTo: $idUserReplyTo, status: $status, smssuffix: $smssuffix\n";
					JWSns::NotifyFollower( $idUser, $idUserReplyTo, $status, $smssuffix );

					/**
					 * Set Status 'DEALED'
					 */
					self::SetStatusDealStatus( $id, self::DEAL_DEALED );

					continue;
				}

				/**
				 * Set Status 'QUARANTINED'
				 */
				//var_dump( "Come Here");
				self::SetStatusDealStatus( $id, self::DEAL_QUARANTINED );
			}
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
