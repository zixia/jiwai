<?php
/**
 * @package JiWai.de
 * @copyright AKA Inc.
 * @author seek@jiwai.com
 * @version $Id$
 */

/**
 * JiWai.de JWNano.class.php
 */
class JWNanoVote{
	
	static private $default_expire = 86400;
	static private $default_device_allow = 'sms,im,web';
	static private $default_notify = 'N';
	static private $default_limit = '20_1';

	const FAIL_EXPIRE = -10;
	const FAIL_DEVICE = -20;
	const FAIL_EXCEED = -30;
	const FAIL_CHOICE = -40;
	const FAIL_NOVOTE = -50;

	/** 
	 * Record a vote;
	 */
	static public function DoVote($user_id, $id, $choice=1, $device='web')
	{
		/**
		 * id check
		 */
		$vote_row = self::GetDbRowByNumber($id);
		if ( empty( $vote_row ) )
			return self::FAIL_NOVOTE;

		$status_id = $vote_row['idStatus'];
		$device = strtolower($device);


		$r = self::DoVoteInfo( $status_id );
		$item_count = count($r);
		if ( empty($r) )
		{
			$status_row = JWDB_Cache_Status::GetDbRowById( $vote_row['idStatus'] );
			$vote_items = JWSns::ParseVoteItem( $status_row['raw_status'] );
			$item_count = count($vote_items);
			$r = array();
			for( $i=1; $i<=$item_count; $i++)
			{
				$r[$i] = array('total'=>0,);
			}
		}

		if ( is_numeric($choice) )
			$choice = abs(intval( $choice ));
		else
			$choice = abs(strpos('0ABCDEFGHIJKLMNOPQRSTUVWXYZ', strtoupper($choice)));


		/**
		 * availabe check	
		 */
		if ( $choice > $item_count || 0==$choice )
			return self::FAIL_CHOICE;

		if ( 0 >= ($flag = self::IsAvailable($vote_row, $user_id, $device)) )
			return $flag;

		/**
		 * recorde choice
		 */
		if ( false==isset($r[$choice][$device] ) )
			$r[$choice][$device] = 0;

		$r[$choice][$device] += 1;
		$r[$choice]['total'] += 1;

		return self::DoVoteInfo( $status_id, $r );
	}

	static public function DoVoteInfo($status_id, $r=null)
	{
		$runtime_key = 'JWNANO_VOTE_' . $status_id;

		if ( $r==null )
		{
			return JWRuntimeInfo::Get($runtime_key);
		}

		return JWRuntimeInfo::Set($runtime_key, $r);
	}

	static public function IsAvailable($vote_row, $user_id, $device='gtalk')
	{
		$now = date('Y-m-d H:i:s');
		if ( $now > $vote_row['timeExpire'] )
			return self::FAIL_EXPIRE;

		$device_cat = JWDevice::GetDeviceCategory( $device );
		if ( false == in_array($device_cat, explode(',', $vote_row['deviceAllow']) ) )
			return self::FAIL_DEVICE;

		$status_id = $vote_row['idStatus'];
		list($minute, $count) = explode('_', $vote_row['limit']);
		$expire = $minute * 60;
		$v = self::GetLimitCount( $status_id, $user_id );
		if ( $v >= $count )
			return self::FAIL_EXCEED;

		self::SetLimitCount( $status_id, $user_id, $expire, 1 );
		return true;
	}

	static public function GetLimitCount( $status_id, $user_id )
	{
		$mc_key = "NANO_VOTE_${status_id}_${user_id}";
		$memcache = JWMemcache::Instance();
		$v = $memcache->Get( $mc_key ) || $v = 0;
		return $v;
	}

	static public function SetLimitCount( $status_id, $user_id, $expire=1200, $plus=1 )
	{
		$mc_key = "NANO_VOTE_${status_id}_${user_id}";
		$memcache = JWMemcache::Instance();
		$v = $memcache->Get( $mc_key ) || $v = 0;
		$v += $plus;
		$memcache->Set( $mc_key, $v, 0, $expire );
		return true;
	}

	static private function IsVoteNumber($number)
	{
		if ( strlen($number) > 4 ) // less then 9999;
			return false;

		if ( false == preg_match('/^\d+$/', $number) ) // all number
			return false;

		return true;
	}

	static public function Create($status_id, $number, $options=array())
	{
		$status_id = JWDB::CheckInt( $status_id );

		if ( false == self::IsVoteNumber($number) )
			return false;

		$timeCreate = date('Y-m-d H:i:s');
		$timeExpire = isset($options['expire']) ? $options['expire'] : self::$default_expire;
		$timeExpire = date('Y-m-d H:i:s', strtotime($timeCreate) + $timeExpire);
		
		$deviceAllow = isset($options['deviceAllow']) ? $options['deviceAllow'] : self::$default_device_allow;
		$limit = isset($options['limit']) ? $options['limit'] : self::$default_limit;
		$notify = isset($options['notify']) ? $options['notify'] : self::$default_notify;

		return JWDB::SaveTableRow( 'Vote', array(
			'idStatus' => $status_id,
			'number' => $number,
			'deviceAllow' => $deviceAllow,
			'timeCreate' => $timeCreate,
			'timeExpire' => $timeExpire,
			'notify' => $notify,
			'limit' => $limit,
		));
	}

	static public function GetDbRowByNumber($id)
	{
		$field = self::IsVoteNumber($id) ? 'number' : 'idStatus';
		$row = JWDB::GetTableRow( 'Vote', array($field=>$id), 1 );

		if ( empty($row) && 'idStatus' == $field )
		{
			$status_row = JWDB_Cache_Status::GetDbRowById( $id );

			if ( 'VOTE' != strtoupper($status_row['statusType']) )
				return array();

			$timeExpire = date('Y-m-d H:i:s', strtotime($status_row['timeCreate']) + self::$default_expire);
			$row = array(
				'idStatus' => $id,
				'notify' => self::$default_notify,
				'timeCreate' => $status_row['timeCreate'],
				'timeExpire' => $timeExpire,
				'limit' => self::$default_limit,
				'deviceAllow' => self::$default_device_allow,
				'notify' => self::$default_notify,
				'number' => null,
			);
		}

		return $row;
	}
}
?>
