<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de AutoNudge Class
 */
class JWAutoNudge {
	/**
	 * Instance of this singleton
	 *
	 * @var JWAutoNudge
	 */
	static private $msInstance;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWAutoNudge
	 */
	static public function &Instance()
	{
		if (!isset(self::$msInstance)) {
			$class = __CLASS__;
			self::$msInstance = new $class;
		}
		return self::$msInstance;
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
	}

	static private function GetNudgeInfo($key)
	{
		$sql = <<<_SQL_
SELECT	*
FROM	AutoNudge
LIMIT	1
_SQL_;
		$nudge_info = JWDB::GetQueryResult($sql);

		return intval($nudge_info[$key]);
	}

	static private function SetNudgeInfo($changeSet)
	{
		// TODO check param to make sure it's llegal
		JWDB::UpdateTableRow('AutoNudge', 1, $changeSet);
	}

	static public function GetIdStatusLastDayProcessed()
	{
		return self::GetNudgeInfo('idStatusLastDay');
	}

	static public function SetIdStatusLastDayProcessed($idStatusLastDay)
	{
		$current_id = self::GetIdStatusLastDayProcessed();
		if ( $current_id > $idStatusLastDay )
			throw new JWException('new id less then old id?!');

		return self::SetNudgeInfo( array('idStatusLastDay'=>$idStatusLastDay) );
	}

	static public function GetIdStatusLastWeekProcesse()
	{
	}

	static public function SetIdStatusLastWeekProcessed()
	{
	}

	static public function GetIdStatusLastMonthProcessed()
	{
	}
	static public function SetIdStatusLastMonthProcessed()
	{
	}


	/*
	 *	获取需要进行 24 小时未更新提醒的idUser列表
 	 *	注意：获取列表后，系统就认为已经提醒过，再次调用本函数不很返回已经返回过的idUser.
	 */
	static public function GetIdUserNudgeDay()
	{
		$id_status_last_day = self::GetIdStatusLastDayProcessed();

		$id_status_max		= JWStatus::GetMaxId();

		/*
			选出idUser，条件为：
				1、24小时前更新过，并且更新没有检查过 auto nudge (意味着idStatus>idStatusLastDay)的
				2、24小时内未更新过的
 		*/

		$now_before_24h	= time() - 24 * 60 * 60;
		//$now_before_24h	= time() - 60*15;

		// 获取最接近24小时前的最大 idStatus
		$id_status_before_24h	= JWStatus::GetMaxIdStatusBeforeTime($now_before_24h);


		/*
		 * 在 最后处理到的  idStatus 和 24小时前的 idStatus 之间扫描用户
		 */
		$id_status_last_day_processed = self::GetIdStatusLastDayProcessed();

/*
		$sql = <<<_SQL_
SELECT	DISTINCT idUser
FROM	Status AS last
WHERE	id BETWEEN $id_status_last_day_processed AND $id_status_before_24h
		AND NOT EXISTS
		(
			SELECT 	1 from Status as new
			WHERE	id BETWEEN $id_status_before_24h+1 AND $id_status_max
					AND last.idUser=new.idUser
		)
_SQL_;

		$result_array = JWDB::GetQueryResult($sql, true);

		$id_users_need_nudge = array();

		if ( empty($result_array) ) 
			return array();

		foreach ( $result_array as $result ) {
			array_push($id_users_need_nudge, $result['idUser']);
		}
*/

/* New CODE shwdai@gmail.com 2007-10-30 */
		$sql_one = <<<_SQL_
SELECT DISTINCT(idUser) 
	FROM Status
	WHERE 
		( id BETWEEN $id_status_last_day_processed AND $id_status_before_24h )
		AND
		idUser NOT IN 
			( 
				SELECT distinct(idUser) 
				FROM Status 
				WHERE id BETWEEN $id_status_before_24h+1 AND $id_status_max
			)
_SQL_;
		$r_one = JWDB::GetQueryResult($sql_one, true);

		if( empty($r_one) )
			return array();

		$id_users_need_nudge = array();
		foreach( $r_one as $r ) {
			array_push( $id_users_need_nudge, $r['idUser'] );
		}
/* End New CODE */

		JWLog::Instance()->Log(LOG_INFO,"JWAutoNudge::GetIdUserNudgeDay found " . count($id_users_need_nudge) . " user(s) no update in near 24H");

		/*
		 *	查看用户设置是否 AutoNudge, 只挠Web用户；
		 */
		$condition_in = JWDB::GetInConditionFromArray($id_users_need_nudge);
		$sql = <<<_SQL_
SELECT	id as idUser
FROM	User
WHERE	id IN ( $condition_in )
		AND noticeAutoNudge='Y'
		AND isWebUser='Y'
_SQL_;

		$result_array = JWDB::GetQueryResult($sql, true);

		if ( empty($result_array) )
			return array();

		$id_users_need_nudge = array();
		foreach ( $result_array as $result ) {
			array_push($id_users_need_nudge, $result['idUser']);
		}


		// 将已经处理过的 idStatus 记录到 Nudge 表的 idStatusLastDay中，
		// 下次处理之处理 > idStatusLastDay 的
		self::SetIdStatusLastDayProcessed($id_status_before_24h);

		JWLog::Instance()->Log(LOG_INFO,"JWAutoNudge::GetIdUserNudgeDay found " 
									. count($id_users_need_nudge) . " user(s) need nudge");
		return $id_users_need_nudge;
	}

}
?>
