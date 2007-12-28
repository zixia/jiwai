<?php
/**
 * @package JiWai.de
 * @copyright AKA Inc.
 * @author wqsemc@jiwai.com
 * @version $Id$
 */

/**
 * JiWai.de JWActionRule lass
 */
class JWActionRule{
	
	/* Constraint type */
	const POLY_FORBID = 'FORBID';
	const POLY_COUNT = 'COUNT';

	const ACTION_ANY = 'ANY';

	/* Object */
	const IP = 'IP'; // limit single ip or c class ip;
	const ID = 'ID'; // limit single user

	/* rule_array */
	static private $rule_array = array();

	static public function Init()
	{
		if (empty(self::$rule_array))
		{
			if ( false==self::LoadFromCache() )
			{
				self::LoadFromDB();
				self::SaveToCache();
			}
		}
	}
	
	/* gen cache key */
	static public function GetCacheKey()
	{
		return JWDB_Cache::GetCacheKeyByFunction( array('JWActionRule','Load') );
	}

	static public function GetRecordCacheKey($action, $value, $is_id=true)
	{
		if ($is_id) 
		{
			$func = array('JWActionRule', 'CanDoID');
		}
		else
		{
			$func = array('JWActionRule', 'CanDoIP');
		}
		return JWDB_Cache::GetCacheKeyByFunction($func, array($action, $value));
	}

	/* load from cache */
	static public function LoadFromCache()
	{
		$memcache = JWMemcache::Instance();
		return self::$rule_array = $memcache->Get(self::GetCacheKey());
	}

	/* save to cache */
	static public function SaveToCache()
	{
		$memcache = JWMemcache::Instance();
		$memcache->Set(self::GetCacheKey(), self::$rule_array);
	}

	static public function InNet($ip, $net=0, $mask=0)
	{
		if ($net==0) 
			return true;

		$ip = ip2long($ip);
		$net = ip2long($net);

		return ($ip>>(32-$mask))==($net>>(32-$mask));
	}

	/* load from DB */
	static public function LoadFromDB()
	{
		$sql = 'SELECT * FROM ActionRule';
		$rows = JWDB::GetQueryResult($sql, true);

		$ip_rule = array();
		$id_rule = array();

		foreach ( $rows as $one )
		{
			if ( empty($one['action'] ) )
				continue;

			$action = strtoupper($one['action']);

			if ( $one['credit'] == null ) //ip rule
			{
				@list($net,$mask) = explode('/',$one['ip']);
				$net = null==$net ? '0.0.0.0' : $net;
				$mask = intval($mask);

				if ( false==isset($ip_rule[$action]) )
					$ip_rule[$action] = array();

				array_push( $ip_rule[$action], array(
					'net' => $net,
					'mask' => $mask,
					'poly' => $one['poly'],
					'step' => $one['step'],
					'unit' => $one['unit'],
					'count' => $one['count'],
				));
			}
			else //id rule
			{
				$credit = $one['credit'];

				if ( false==isset($id_rule[$action]) )
					$id_rule[$action] = array();

				if ( false==isset($id_rule[$action]['credit']) )
					$id_rule[$action][$credit] = array();

				array_push( $id_rule[$action][$credit], array(
					'poly' => $one['poly'],
					'step' => $one['step'],
					'unit' => $one['unit'],
					'count' => $one['count'],
				));
			}
		}

		self::$rule_array = array(
			self::ID => $id_rule,
			self::IP => $ip_rule,
		);
	}

	/**
	 * Create
	 */
	static public function Create()
	{
	}

	static public function GetRecord($key)
	{
		$record = JWMemcache::Instance()->Get($key);
		if ( false==$record )
			$record = new JWAction_Record();
		else
			$record->Init();

		return $record;
	}

	static public function SaveRecord($key, $record, $done=false)
	{
		if ( $done && $record )
		{
			$record->Record();
		}

		if ( $record )
		{
			JWMemcache::Instance()->Set($key, $record);
		}
	}

	static public function CanDoByOneRule($rule, $record)
	{
		if ( null==$rule || null==$record )
			return true;

		if ( $rule['poly'] == self::POLY_FORBID )
			return false;

		$count = 0;
		if ($rule['unit']=='MINUTE')
		{
			$count = $record->GetCountMinuteInteval($rule['step']);
		}
		else if ($one['unit']=='SECOND')
		{
			$count = $record->GetCountSecondInteval($rule['step']);
		}
		else
			return true; // fix me;

		if ( $count >= $rule['count'] )
			return false;

		return true;
	}
	/**
	 * Check User Can do
	 */
	static public function CanDo($action, $user_info=null, $options=array())
	{
		self::Init();
		
		$action = strtoupper($action);
		$device = isset($options['device']) ? $options['device'] : null;
		$ignore_any = isset($options['ignore_any']) ? $options['ignore_any'] : false;
		$ip = JWRequest::GetIpRegister($device);

		/* Step.1:  IP check */
		$ip_rule = self::$rule_array[self::IP];
		$ip_action_key = self::GetRecordCacheKey($action, $ip, false);
		$ip_action_record = null;
		
		/**
		 * exist $ip_rule[$action] or $ip_rule['ANY'] and false==$ignore_any
		 */
		if ( (isset($ip_rule[$action]) 
				&& $action_row=$ip_rule[$action]) 
			|| ( false==$ignore_any 
				&& isset($ip_rule[self::ACTION_ANY]) 
				&& $action_row=self::$ip_rule[self::ACTION_ANY]) )
		{
			$ip_action_record = self::GetRecord($ip_action_key);
			foreach ($action_row AS $one)
			{
				if (self::InNet($ip, $one['net'], $one['mask']))
				{
					if ( false===self::CanDoByOneRule($one, $ip_action_record) )
						return false;
				}
			}
		}


		/* Step.2:  ID check */
		$id_action_key = $id_action_record = null;
		$id_rule = self::$rule_array[self::ID];
		if ( false==empty($user_info) )
		{
			if ( 'ANONYMOUS' == $user_info['srcRegister'] ) 
			{
				$user_credit == JWCredit::CREDIT_ANONYMOUS;
			}
			else
			{
				$credit_row = JWCredit::GetDbRowByIdUser($user_info['id']);
				$user_credit = $credit_row['credit'];
			}

			$id_action_key = self::GetRecordCacheKey($action, $ip, true);
			$id_action_record = self::GetRecord($id_action_key);

			/**
			 * exist $ip_rule[$action] or $ip_rule['ANY'] and false==$ignore_any
			 */
			if ( (isset($id_rule[$action]) 
					&& $action_row=$id_rule[$action]) 
				|| ( false==$ignore_any 
					&& isset($id_rule[self::ACTION_ANY]) 
					&& $action_row=self::$id_rule[self::ACTION_ANY]) )
			{
				foreach ( $action_row AS $rule_credit=>$credit_action_row )
				{
					if ( JWCredit::Compare( $rule_credit, $user_credit ) >= 0 )
					{
						foreach ( $credit_action_row AS $one )
						{
							if ( false===self::CanDoByOneRule($one, $id_action_record) )
								return false;
						}
					}
				}
			}
		}

		/* save action record */
		self::SaveRecord($ip_action_key, $ip_action_record, true);
		self::SaveRecord($id_action_key, $id_action_record, true);

		return true;
	}
}
?>
