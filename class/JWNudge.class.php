<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Nudge Class
 */
class JWNudge {
	/**
	 * Instance of this singleton
	 *
	 * @var JWNudge
	 */
	static private $msInstance;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWNudge
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
FROM	Nudge
LIMIT	1
_SQL_;
		$nudge_info = JWDB::GetQueryResult($sql);

		return intval($nudge_info[$key]);
	}

	static private function SetNudgeInfo($changeSet)
	{
		// TODO check param to make sure it's llegal
		JWDB::UpdateTableRow('Nudge', 1, $changeSet);
	}

	static public function GetIdStatusLastDay()
	{
		return self::GetNudgeInfo('idStatusLastDay');
	}

	static public function SetIdStatusLastDay($idStatusLastDay)
	{
		$current_id = self::GetIdStatusLastDay();
		if ( $current_id > $idStatusLastDay )
			throw new JWException('new id less then old id?!');

		return self::SetNudgeInfo( array('idStatusLastDay'=>$idStatusLastDay) );
	}

	static public function GetIdStatusLastWeek()
	{
	}

	static public function SetIdStatusLastWeek()
	{
	}

	static public function GetIdStatusLastMonth()
	{
	}
	static public function SetIdStatusLastMonth()
	{
	}


	/*
	 *	获取需要进行 24 小时未更新提醒的idUser列表
 	 *	注意：获取列表后，系统就认为已经提醒过，再次调用本函数不很返回已经返回过的idUser.
	 */
	static public function GetIdUserNudgeDay()
	{
		$id_status_last_day = self::GetIdStatusLastDay();

		$id_status_max		= JWStatus::GetMaxId();

		/*
			选出idUser，条件为：
				1、24小时前更新过，并且更新没有检查过 auto nudge (意味着idStatus>idStatusLastDay)的
				2、24小时内未更新过的
 		*/

		$now_before_24h	= time() - 24 * 60 * 60;
		//$now_before_24h	= time() - 60*15;

		$id_status_last_day = self::GetIdStatusLastDay();

		$sql = <<<_SQL_
SELECT	distinct idUser
FROM	Status
WHERE	timestamp < FROM_UNIXTIME($now_before_24h)
			AND id>$id_status_last_day
			AND idUser NOT IN
			(
				SELECT 	idUser from Status
				WHERE	timestamp > FROM_UNIXTIME($now_before_24h)
						AND id<=$id_status_max
			 )
_SQL_;

//die($sql);
		$result_array = JWDB::GetQueryResult($sql, true);

		$id_user_need_nudge = array();

		if ( !empty($result_array) ) {
			foreach ( $result_array as $result ) {
				array_push($id_user_need_nudge, $result['idUser']);
			}
		}

		// 将已经处理过的 idStatus 记录到 Nudge 表的 idStatusLastDay中，
		// 下次处理之处理 > idStatusLastDay 的
		$sql = <<<_SQL_
SELECT	MAX(id) idStatusLastDay
FROM	Status
WHERE	timestamp < FROM_UNIXTIME($now_before_24h)
_SQL_;

		$result_array = JWDB::GetQueryResult($sql);
		
		self::SetIdStatusLastDay($result_array['idStatusLastDay']);

		JWLog::Instance()->Log(LOG_INFO,"JWNudge::GetIdUserNudgeDay found " 
									. count($id_user_need_nudge) . " user(s) need nudge");
		return $id_user_need_nudge;
	}
}
?>
