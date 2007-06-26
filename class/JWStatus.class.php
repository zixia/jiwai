<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Status Class
 */
class JWStatus {
	/**
	 * Instance of this singleton
	 *
	 * @var JWStatus
	 */
	static private $msInstance = null;

	const	DEFAULT_STATUS_NUM	= 20;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWStatus
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

	/*
	 *	根据 status 的 @zixia 打头内容，获取 zixia 最新的一条 status 的 id，
	 */
	static public function GetReplyInfo($status)
	{
		if ( empty($status) )
			return null;

		if ( ! preg_match('/^@\s*([\w\.\-\_]+)/',$status, $matches) )
			return null;

		$reply_to_user = $matches[1];

		$user_db_row	= JWUser::GetUserInfo($reply_to_user);


		if ( empty($user_db_row) )
			return null;

		$reply_to_status_id = JWStatus::GetMaxIdStatusByUserId($user_db_row['idUser']);

		if ( empty($reply_to_status_id) )
			return null;

		return array ( 	 'user_id'		=> $user_db_row['idUser']
						,'status_id'	=> $reply_to_status_id
					);
	}


	/*
	 *	@param	int	$time	unixtime
	 */
	static public function Create( $idUser, $status, $device='web',$time=null,$isSignature='N')
	{
		$db = JWDB::Instance()->GetDb();

		// 去掉回车，替换为空格 , shwdai moved here from below..
		$status = preg_replace('[\r\n]',' ',$status);

		//Sinature logic
		if( $isSignature == 'Y' && in_array($device,array('gtalk','msn')) ){
			$device_data = GetDeviceRowByUserId( $idUser );
			if( !empty( $device_data ) 
					&& $device_data['isSignatureRecord'] == 'Y' 
					&& strncasecmp($device_data['signature'],$status,140)
			  ){
			}else{
				return true;
			}
		}else{
			$isSignature = 'N';
		}

		$time = intval($time);

		if ( 0>=$time )
			$time = time();

		$reply_info 		= JWStatus::GetReplyInfo($status);

		if ( empty($reply_info) )
		{ 
			$reply_status_id	= null;
			$reply_user_id		= null;
		}
		else
		{
			$reply_status_id	= $reply_info['status_id'];
			$reply_user_id		= $reply_info['user_id'];
		}

		$user_db_row = JWUser::GetUserDbRowById($idUser);

		$picture_id = $user_db_row['idPicture'];

		if ( $stmt = $db->prepare( "INSERT INTO Status (idUser,status,device,timeCreate,idStatusReplyTo,idUserReplyTo,idPicture,isSignature) "
								. " values (?,?,?,FROM_UNIXTIME(?),?,?,?,?)" ) ){
			if ( $result = $stmt->bind_param("isssiiis"
											, $idUser
											, $status
											, $device
											, $time
											, $reply_status_id
											, $reply_user_id
											, $picture_id
											, $isSignature
								) ){
				if ( $stmt->execute() ){
					$stmt->close();
					return true;
				}else{
					JWLog::Instance()->Log(LOG_ERR, $db->error );
				}
			}
		}else{
			JWLog::Instance()->Log(LOG_ERR, $db->error );
		}
		return false;
	}


	/*
	 *	获取用户的 idStatus 
	 *	@param	int		$idUser	用户的id
	 *	@return	array	array ( 'status_ids'=>array(), 'user_ids'=>array() )
	 */
	static public function GetStatusIdsFromUser($idUser, $num=JWStatus::DEFAULT_STATUS_NUM, $start=0)
	{
		$idUser	= intval($idUser);
		$num	= intval($num);
		$start	= intval($start);

		if ( !is_int($idUser) || !is_int($num) || !is_int($start) )
			throw new JWException('must int');

		$sql = <<<_SQL_
SELECT		Status.id	as idStatus
FROM		Status, User
WHERE		Status.idUser=User.id
			AND User.id=$idUser
ORDER BY 	Status.timeCreate desc
LIMIT 		$start,$num
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);

		if ( !empty($rows) )
		{
		// 装换rows, 返回 id 的 array
			$status_ids = array_map(	create_function(
												'$row'
												, 'return $row["idStatus"];'
											)
										, $rows
									);
		}
		else
		{
			$status_ids = array();
		}

		return array (	'status_ids'	=> $status_ids
						,'user_ids'		=> array($idUser)
					);
	}


	/*
	 *	获取回复用户的 idStatus 
	 *	@param	int		$idUser	用户的id
	 *	@return	array	array ( 'status_ids'=>array(), 'user_ids'=>array() )
	 */
	static public function GetStatusIdsFromSelfNReplies($idUser, $num=JWStatus::DEFAULT_STATUS_NUM, $start=0)
	{
		$idUser	= JWDB::CheckInt($idUser);
		$num	= JWDB::CheckInt($num);

		$sql = <<<_SQL_
SELECT		 Status.id	as idStatus
			,Status.idUser	as idUser
FROM		Status
WHERE		(
			Status.idUserReplyTo=$idUser
			OR Status.idUser=$idUser
			)
ORDER BY 	Status.timeCreate desc
LIMIT 		$start,$num
_SQL_;

		//AND Status.idUser<>1927 -- XXX block youyouwan

		$rows = JWDB::GetQueryResult($sql,true);

		if ( empty($rows) )
			return array();


		$status_ids = JWFunction::GetColArrayFromRows($rows, 'idStatus');
		$user_ids 	= JWFunction::GetColArrayFromRows($rows, 'idUser');

		array_push($user_ids, $idUser);

		return array (	'status_ids'	=> $status_ids
						,'user_ids'		=> $user_ids
					);
	}



	/*
	 *	获取回复用户的 idStatus 
	 *	@param	int		$idUser	用户的id
	 *	@return	array	array ( 'status_ids'=>array(), 'user_ids'=>array() )
	 */
	static public function GetStatusIdsFromReplies($idUser, $num=JWStatus::DEFAULT_STATUS_NUM, $start=0)
	{
		$idUser	= JWDB::CheckInt($idUser);
		$num	= JWDB::CheckInt($num);

		$sql = <<<_SQL_
SELECT		 id				as idStatus
			,idUser
FROM		Status
WHERE		idUserReplyTo=$idUser
ORDER BY 	timeCreate desc
LIMIT 		$start,$num
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);

		if ( empty($rows) )
			return array();


		$status_ids = JWFunction::GetColArrayFromRows($rows, 'idStatus');
		$user_ids 	= JWFunction::GetColArrayFromRows($rows, 'idUser');

		array_push($user_ids, $idUser);

		return array (	'status_ids'	=> $status_ids
						,'user_ids'		=> $user_ids
					);
	}



	/*
	 *	获取用户和好友的 idStatus，并返回相关的 idUser 以供后期组合
	 *	@param	int		$idUser	用户的id
	 *	@return	array	array ( 'status_ids'=>array(), 'user_ids'=>array() )
	 */
	static public function GetStatusIdsFromFriends($idUser, $num=JWStatus::DEFAULT_STATUS_NUM, $start=0)
	{
		$idUser	= intval($idUser);
		$num	= intval($num);
		$start	= intval($start);

		if ( 0>=$idUser || 0>=$num )
			throw new JWException('must int');

		$friend_ids = JWFriend::GetFriendIds($idUser);
		
		array_push($friend_ids, $idUser);

		$condition_in = JWDB::GetInConditionFromArray($friend_ids);

		$sql = <<<_SQL_
SELECT
		 Status.id	as idStatus
		,Status.idUser as idUser
FROM	
		Status
WHERE	
		Status.idUser IN ($condition_in)
		AND Status.timeCreate > (NOW()-INTERVAL 1 WEEK)
ORDER BY
		Status.timeCreate desc
LIMIT 
		$start,$num
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);


		$status_ids = array();
		$user_ids = array();
		if ( !empty($rows) )
		{
			$status_ids = JWFunction::GetColArrayFromRows($rows, 'idStatus');
			$user_ids 	= JWFunction::GetColArrayFromRows($rows, 'idUser');
		}

		return array ( 	 'status_ids'	=> $status_ids
						,'user_ids'		=> $user_ids
					);
	}


	/*
	 *	获取 public_timeline 的 idStatus 
	 *	@return	array	array ( 'status_ids'=>array(), 'user_ids'=>array() )
	 */
	static public function GetStatusIdsFromPublic($num=JWStatus::DEFAULT_STATUS_NUM, $start=0)
	{
		$num	= intval($num);
		$start	= intval($start);

		if ( !is_int($num) || !is_int($start) )
			throw new JWException('must int');

		$sql = <<<_SQL_
SELECT		
			Status.id		as idStatus
			,Status.idUser	as idUser
FROM		
			Status, User
WHERE		
			Status.idUser=User.id
			AND Status.idUserReplyTo IS NULL
			AND User.idPicture IS NOT NULL
			AND User.protected<>'Y'
ORDER BY 	
			Status.timeCreate desc
LIMIT 		$start,$num
_SQL_;
			//AND User.id<>1927 -- XXX block youyouwan

		$rows = JWDB::GetQueryResult($sql,true);

		$status_ids = JWFunction::GetColArrayFromRows($rows, 'idStatus');
		$user_ids 	= JWFunction::GetColArrayFromRows($rows, 'idUser');

		return array ( 	 'status_ids'	=> $status_ids
						,'user_ids'		=> $user_ids
					);
	}


	/*
	 *	根据 idStatus 获取 Row 的详细信息
	 *	@param	array	idStatuses
	 * 	@return	array	以 idStatus 为 key 的 status row
	 * 
	 */
	static public function GetStatusDbRowsByIds ($idStatuses)
	{
		if ( empty($idStatuses) )
			return array();

		if ( !is_array($idStatuses) )
			throw new JWException('must array');

		$idStatuses = array_unique($idStatuses);

		$condition_in = JWDB::GetInConditionFromArray($idStatuses);

		$sql = <<<_SQL_
SELECT
		id as idStatus
		, idUser
		, status
		, UNIX_TIMESTAMP(Status.timeCreate) AS timeCreate
		, device
		, idStatusReplyTo
		, idPicture
		, isSignature
FROM	Status
WHERE	Status.id IN ($condition_in)
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);


		if ( empty($rows) ){
			$status_map = array();
		} else {
			foreach ( $rows as $row ) {
				$status_map[$row['idStatus']] = $row;
			}
		}

		return $status_map;
	}

	static public function GetStatusDbRowById ($idStatus)
	{
		$status_db_rows = JWStatus::GetStatusDbRowsByIds(array($idStatus));

		if ( empty($status_db_rows) )
			return array();

		return $status_db_rows[$idStatus];
	}

	/*
	 *	@param	bool	$forceDate	是否强制显示日期时间
	 */
	static public function GetTimeDesc ($unixtime, $forceDate=false)
	{

		$duration = time() - $unixtime;
		if ( $forceDate || $duration > 2*86400 ){
			return strftime("%Y-%m-%d 周%a %H:%M",$unixtime);
		}else if ( $duration > 86400 ){
			return strftime("%Y-%m-%d %H:%M",$unixtime);
			//return "1 天前";
		}else if ( $duration > 3600 ){ // > 1 hour
			$duration = intval($duration/3600);
			return "$duration 小时前";
		}else if ( $duration > 60 ){ // > 1 min
			$duration = intval($duration/60);
			return "$duration 分钟前";
		}else{ // < 1 min
			if ( $duration > 30 ){
				return "半分钟前";
			}else if ( $duration > 20 ){
				return "20 秒前";
			}else if ( $duration > 10 ){
				return "10 秒前";
			}else if ( $duration > 5 ){
				return "5 秒前";
			}else{
				return "就在刚才";
			}
		}
	}


	/*
	 * @param	int
	 * @return	bool
	 */
	static public function Destroy ($idStatus)
	{
		if ( !is_numeric($idStatus) ){
			throw new JWException("must be numeric! [$idStatus]");
		}

		return JWDB::DelTableRow('Status', array (	'id'	=> intval($idStatus) ));
	}



	/*
	 * @param	int		status pk
	 * @param	int		user pk
	 * @return	bool	if user own status
	 */
	static public function IsUserOwnStatus ($idUser, $idStatus)
	{
		$idUser 	= intval($idUser);
		$idStatus	= intval($idStatus);

		if ( 0>=$idStatus || 0>=$idUser )
			throw new JWException("must be int! [$idStatus] [$idUser]");

		return JWDB::ExistTableRow('Status', array (	'id'		=> intval($idStatus)
														,'idUser'	=> intval($idUser)
											) );
	}


	/*
	 *	@param	string	status
	 *	@param	bool	jsLink	使用 js 做链接，如果是 flase 则使用 html link
	 *
	 *	@return	array	formated status & other info
	 *					array ( 'status' => ..., 'replyto' => ... );
	 */
	static public function FormatStatus ($status, $jsLink=true)
	{
		$replyto	= null;

		if ( preg_match('/^@\s*([\w\._\-]+)/',$status,$matches) )
			$replyto 	= $matches[1];


		/* 
		 * if status contains URL, we split status str to 4 parts: url_before, url_domain, url_path, url_after.
		 *
		 * URL is ascii. UTF8 ascii is 1 byte so we use 0x00-0xff to match ascii.
		 * 		":" is 0x3a
		 *		"/" is 0x2F
		 *		' ' is 0x20
		 *
		 */

		$skip_url_regex = 	 '#'
							.'(komoo.cn)'
							.'|(fanfou.com)'
							.'#'
						;
		$status = preg_replace('/[\n\r]/' ,' ', $status);

		if ( !preg_match($skip_url_regex,$status)
				&& preg_match(	'#'
									// head_str
									. '^(.*?)'
									. 'http://'
										// url_domain
										. '([' . '\x00-\x1F' ./*' '*/ '\x21-\x2B' ./*','*/ '\x2D-\x2E' ./*'/'*/ '\x30-\x39' ./*':'*/ '\x3B-\x7F' . ']+)'
										// url_path
										. '([' . '\x00-\x09' ./*\x0a(\n)*/ '\x0B-\x0C' ./*\x0d(\r)*/ '\x0E-\x1F' ./*' '*/ '\x21-\x7F' . ']*)'
									// tail_str
									. '(.*)$#is'
							, $status
							, $matches 
						) )
		{
			//die(var_dump($matches));
			$head_str		= htmlspecialchars($matches[1]);
			$url_domain		= htmlspecialchars($matches[2]);
			$url_path		= htmlspecialchars($matches[3]);
			$tail_str		= htmlspecialchars($matches[4]);


			/*
			 *	检查 url path 是否为真正的 url path
	 	 	 */
			if (!empty($url_path) && preg_match('#[^/:]#', $url_path[0]) )
			{
				$tail_str = $url_path . $tail_str;
				$url_path = '';
			}


			if ( $jsLink )
			{
				$url_str		= <<<_HTML_
<a href="#" target="_blank" onclick="urchinTracker('/wo/outlink/$url_domain$url_path'); 
						this.href='http://$url_domain$url_path';">http://$url_domain/...</a>
_HTML_;
			}
			else
			{
				$url_str		= <<<_HTML_
<a href="http://$url_domain$url_path" 
		target="_blank" onclick="urchinTracker('/wo/outlink/$url_domain$url_path');"
		>http://$url_domain/...</a>
_HTML_;

			}

			$status 		= $head_str . $url_str . $tail_str;

//$status = htmlspecialchars($status);
		}
		else
		{
			$status = htmlspecialchars($status);
		}

		if ( ! empty($replyto) )
			$status		= preg_replace('/^@\s*([\w\._\-]+)/',"@<a href='/$1/'>$1</a> ", $status);

		return array ( 'status'		=> $status
						, 'replyto'	=> $replyto
					);
	}
	

	/*
	 *	@param	int		$idUser
	 *	@return	int		$statusNum for $idUser
	 */
	static public function GetStatusNum($idUser)
	{
		$idUser = intval($idUser);

		if ( !is_int($idUser) )
			throw new JWException('must be int');

		$sql = <<<_SQL_
SELECT	COUNT(*) as num
FROM	Status
WHERE	idUser=$idUser
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}


	/*
	 *	@param	int		$idUser
	 *	@return	int		$statusNum for replies to $idUser
	 */
	static public function GetStatusNumFromReplies($idUser)
	{
		$idUser = JWDB::CheckInt($idUser);

		$sql = <<<_SQL_
SELECT	COUNT(*) as num
FROM	Status
WHERE	idUserReplyTo=$idUser
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}


	/*
	 *	@param	int		$idUser
	 *	@return	int		$statusNum for replies to $idUser
	 */
	static public function GetStatusNumFromSelfNReplies($idUser)
	{
		$idUser = JWDB::CheckInt($idUser);

		$sql = <<<_SQL_
SELECT	COUNT(*) as num
FROM	Status
WHERE	idUserReplyTo=$idUser
		OR idUser=$idUser
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}



	/*
	 *	24小时以内的 idUser 和好友们的更新

	 *	@param	int		$idUser
	 *	@return	int		$statusNum for $idUser's friends
	 */
	static public function GetStatusNumFromFriends($idUser)
	{
		$idUser = intval($idUser);

		if ( !is_int($idUser) )
			throw new JWException('must be int');

		$friend_ids = JWFriend::GetFriendIds($idUser);

		array_push($friend_ids, $idUser);

		$condition_in = JWDB::GetInConditionFromArray($friend_ids);

		$sql = <<<_SQL_
SELECT      
        COUNT(1) as num
FROM
        Status
WHERE
        Status.timeCreate > (NOW()-INTERVAL 24 HOUR)
        AND Status.idUser IN ($condition_in)
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}


	/*
	 *	返回当前最大的Status的ID号
 	 */
	static public function GetMaxId()
	{
		$sql = <<<_SQL_
SELECT	MAX(id) as idStatus
FROM	Status
_SQL_;

		$result = JWDB::GetQueryResult($sql);
		return $result['idStatus'];
	}


	/*
	 *	获取在某一时刻之前的最大 idStatus
	 */
	static public function GetMaxIdStatusBeforeTime($unixtime)
	{
		$sql = <<<_SQL_
SELECT	MAX(id) as idMax
FROM	Status
WHERE	timeCreate < FROM_UNIXTIME($unixtime)
_SQL_;
		$row = JWDB::GetQueryResult($sql);
		return $row['idMax'];
	}

	/*
	 *	获取用户的最大 idStatus
	 */
	static public function GetMaxIdStatusByUserId($idUser)
	{
		$idUser = JWDB::CheckInt($idUser);

		$sql = <<<_SQL_
SELECT	MAX(id) as idMax
FROM	Status
WHERE	idUser=$idUser
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		if ( empty($row) )
			return 0;

		return $row['idMax'];
	}


}
?>
