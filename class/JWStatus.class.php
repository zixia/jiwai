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

		if ( ! preg_match('/^@\s*([\w\.\-\_]+)/',$status, $matches) ) {
            if ( ! preg_match('/^@\s*([^\s]+)\s+(.+)/',$status, $matches) ) 
                return null;
        }

		$reply_to_user = $matches[1];

		$user_db_row	= JWUser::GetUserInfo($reply_to_user);

		if ( empty($user_db_row) )
			return null;

		$reply_to_status_id = JWStatus::GetMaxIdStatusByUserId($user_db_row['idUser']);

		if ( empty($reply_to_status_id) )
			return null;
		
		return array ( 	 
				'user_id' 	=> $user_db_row['idUser'], 
				'status_id'	=> $reply_to_status_id,
			);
	}


	/*
	 *	@param	int	$time	unixtime
	 *	//fix me, the first parameter $idUser, may be an array which comes from table StatusQuarantine. We can use this array to create a new status directly.
	 */
	static public function Create( $idUser, $status=null, $device='web', $timeCreate=null, $isSignature='N', $options=array() )
	{
		//strip status
		$status = preg_replace('[\r\n]',' ',$status);
		
		//time set
		if( isset( $options['timeCreate'] ) ) {
			$timeCreate = $options['timeCreate'];
		}else{
			$timeCreate = ( intval($timeCreate) > 0 ) ? intval($timeCreate) : time();
		}

		$userInfo  = JWUser::GetUserInfo( $idUser );
		$idPicture = $userInfo['idPicture'];
		$isProtected = $userInfo['protected'];

		$idUserReplyTo = $idStatusReplyTo = null;
		$idConference = null;

		if( isset( $options['idPicture'] ) ) {
			$idPicture = $options['idPicture'] ;
		}

		if( isset( $options['idUserReplyTo'] ) ) {
			$idUserReplyTo = $options['idUserReplyTo'];
			$idStatusReplyTo = $options['idStatusReplyTo'];
		}else{
			$statusPost = JWRobotLingoBase::ConvertCorner($status);
			$reply_info = JWStatus::GetReplyInfo($statusPost);
			if( false == empty( $reply_info ) ){
				$idUserReplyTo = $reply_info['user_id'];
				$idStatusReplyTo = $reply_info['status_id'];
			}
		}

		if( isset( $options['idConference'] ) && $options['idConference'] ) {
			$idConference = $options['idConference'];
		}else{
			$idConference = $userInfo['idConference'];
		}
        
		$idPartner = null;
		if( isset( $options['idPartner'] ) && intval($options['idPartner']) ){
			$partner = JWPartner::GetDbRowById( intval($options['idPartner']) );
			if( false == empty( $partner ) ){
				$idPartner = $partner['id'];
			}
		}

		$isMms = ( isset($options['isMms']) && $options['isMms'] == 'Y' ) ? 'Y' : 'N';

		return JWDB_Cache::SaveTableRow('Status', array( 
								'idUser' => $idUser,
								'status' => preg_replace('/\xE2\x80\xAE/U', '', $status),
								'device' => $device,
								'timeCreate' => Date('Y-m-d H:i:s', $timeCreate),
								'idUserReplyTo'	=> $idUserReplyTo, 
								'idStatusReplyTo' => $idStatusReplyTo,
								'idPicture' => $idPicture,
								'idConference' => $idConference,
								'isProtected' => $isProtected,
								'idPartner' => $idPartner,
								'isSignature' => $isSignature,
								'isMms' => $isMms,
						));
	}


	/*
	 *	获取用户的 idStatus 
	 *	@param	int		$idUser	用户的id
	 *	@return	array	array ( 'status_ids'=>array(), 'user_ids'=>array() )
	 */
	static public function GetStatusIdsFromUser($idUser, $num=JWStatus::DEFAULT_STATUS_NUM, $start=0, $idSince=null, $timeSince=null)
	{
		$idUser	= JWDB::CheckInt($idUser);
		$num	= JWDB::CheckInt($num);
		$start	= intval($start);

		//$idSince 	= JWDB::CheckInt($idSince);
		//$timeSince	= JWDB::CheckInt($timeSince);

		$condition_other = null;
		if( $idSince > 0 ){
			$condition_other .= " AND id > $idSince";
		}
		if( $timeSince ) {
			$condition_other .= " AND timeCreate > '$timeSince'";
		}

		/*
		 *	每个结果集中，必须保留 id，为了 memcache 统一处理主键
		 */
		$sql = <<<_SQL_
SELECT		 id
			,id as idStatus
FROM		Status
WHERE		idUser=$idUser
		$condition_other
ORDER BY 	timeCreate desc
LIMIT 		$start,$num
_SQL_;

		$rows = JWDB_Cache::GetQueryResult($sql,true);


		if ( !empty($rows) )
		{
			// 装换rows, 返回 id 的 array
			$status_ids = JWFunction::GetColArrayFromRows($rows, 'idStatus');
		}
		else
		{
			$status_ids = array();
		}

		return array (	'status_ids'	=> $status_ids
						,'user_ids'		=> array($idUser)
					);
	}

	static public function GetStatusIdsFromUserMms($idUser, $num=JWStatus::DEFAULT_STATUS_NUM, $start=0 )
	{
		$idUser	= JWDB::CheckInt($idUser);
		$num	= JWDB::CheckInt($num);
		$start	= intval($start);

		$condition_other = null;
		/*
		 *	每个结果集中，必须保留 id，为了 memcache 统一处理主键
		 */
		$sql = <<<_SQL_
SELECT		 id
			,id as idStatus
FROM		Status
WHERE		idUser=$idUser
		AND isMms = 'Y'
		$condition_other
ORDER BY 	timeCreate desc
LIMIT 		$start,$num
_SQL_;

		$rows = JWDB_Cache::GetQueryResult($sql,true);


		if ( !empty($rows) )
		{
			// 装换rows, 返回 id 的 array
			$status_ids = JWFunction::GetColArrayFromRows($rows, 'idStatus');
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
		$idUser	= JWDB_Cache::CheckInt($idUser);
		$num	= JWDB_Cache::CheckInt($num);

		/*
		 *	每个结果集中，必须保留 id，为了 memcache 统一处理主键
		 */
		$sql = <<<_SQL_
SELECT		 id
			,id	as idStatus
			,idUser
FROM		Status
WHERE		(
			idUserReplyTo=$idUser
			OR idUser=$idUser
			)
ORDER BY 	timeCreate desc
LIMIT 		$start,$num
_SQL_;

		//AND Status.idUser<>1927 -- XXX block youyouwan

		$rows = JWDB_Cache::GetQueryResult($sql,true);

		if ( empty($rows) )
			return array();


		$status_ids = JWFunction::GetColArrayFromRows($rows, 'idStatus');
		$user_ids 	= array_unique(JWFunction::GetColArrayFromRows($rows, 'idUser'));

		array_push($user_ids, $idUser);

		return array (	'status_ids'	=> $status_ids
						,'user_ids'		=> $user_ids
					);
	}

	/*
	 *	获取用户的会议模式下的Status
	 *	@param	int		$idUser	用户的id
	 *	@return	array	array ( 'status_ids'=>array(), 'user_ids'=>array() )
	 */
	static public function GetStatusIdsFromConferenceUser($idUser, $num=JWStatus::DEFAULT_STATUS_NUM, $start=0)
	{
		$idUser	= JWDB_Cache::CheckInt($idUser);
		$num	= JWDB_Cache::CheckInt($num);

		$userInfo = JWUser::GetUserInfo( $idUser );

		if( empty( $userInfo ) || null == $userInfo['idConference'] ){
			return array();
		}

		$idConference = JWDB_Cache::CheckInt( $userInfo['idConference'] );

		/*
		 *	每个结果集中，必须保留 id，为了 memcache 统一处理主键
		 */
		$sql = <<<_SQL_
SELECT		 id
		,id	as idStatus
		,idUser
FROM		Status
WHERE		idConference = $idConference
ORDER BY 	timeCreate desc
LIMIT 		$start,$num
_SQL_;

		//AND Status.idUser<>1927 -- XXX block youyouwan

		$rows = JWDB_Cache::GetQueryResult($sql,true);

		if ( empty($rows) )
			return array();


		$status_ids = JWFunction::GetColArrayFromRows($rows, 'idStatus');
		$user_ids 	= array_unique(JWFunction::GetColArrayFromRows($rows, 'idUser'));

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
		$idUser	= JWDB_Cache::CheckInt($idUser);
		$num	= JWDB_Cache::CheckInt($num);

		/*
		 *	每个结果集中，必须保留 id，为了 memcache 统一处理主键
		 */
		$sql = <<<_SQL_
SELECT		 id
			,id	as idStatus
			,idUser
FROM		Status
WHERE		idUserReplyTo=$idUser
ORDER BY 	timeCreate desc
LIMIT 		$start,$num
_SQL_;

		$rows = JWDB_Cache::GetQueryResult($sql,true);

		if ( empty($rows) )
			return array();


		$status_ids = JWFunction::GetColArrayFromRows($rows, 'idStatus');
		$user_ids 	= array_unique(JWFunction::GetColArrayFromRows($rows, 'idUser'));

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
	static public function GetStatusIdsFromFriends($idUser, $num=JWStatus::DEFAULT_STATUS_NUM, $start=0, $idSince=null, $timeSince=null)
	{
		$idUser	= intval($idUser);
		$num	= intval($num);
		$start	= intval($start);
		
		$condition_other = null;
		if( $idSince > 0 ){
			$condition_other .= " AND id > $idSince";
		}
		if( $timeSince ) {
			$condition_other .= " AND timeCreate > '$timeSince'";
		}

		if ( 0>=$idUser || 0>=$num )
			throw new JWException('must int');

		$friend_ids = JWFriend::GetFriendIds($idUser);
		
		array_push($friend_ids, $idUser);

		$condition_in = JWDB_Cache::GetInConditionFromArray($friend_ids);

		/*
		 *	每个结果集中，必须保留 id，为了 memcache 统一处理主键
		 */
		$sql = <<<_SQL_
SELECT
		 id
		,id	as idStatus
		,idUser as idUser
FROM	
		Status
WHERE	
		idUser IN ($condition_in)
		AND timeCreate > (NOW()-INTERVAL 1 WEEK)
		$condition_other
ORDER BY
		timeCreate desc
LIMIT 
		$start,$num
_SQL_;

		$rows = JWDB_Cache::GetQueryResult($sql,true);


		$status_ids = array();
		$user_ids = array();
		if ( !empty($rows) )
		{
			$status_ids = JWFunction::GetColArrayFromRows($rows, 'idStatus');
			$user_ids 	= array_unique(JWFunction::GetColArrayFromRows($rows, 'idUser'));
		}

		return array ( 	 'status_ids'	=> $status_ids
						,'user_ids'		=> $user_ids
					);
	}

	static public function GetStatusIdsFromFriendsMms($idUser, $num=JWStatus::DEFAULT_STATUS_NUM, $start=0)
	{
		$idUser	= intval($idUser);
		$num	= intval($num);
		$start	= intval($start);
		
		$condition_other = null;

		if ( 0>=$idUser || 0>=$num )
			throw new JWException('must int');

		$friend_ids = JWFriend::GetFriendIds($idUser);
		
		array_push($friend_ids, $idUser);

		$condition_in = JWDB_Cache::GetInConditionFromArray($friend_ids);

		/*
		 *	每个结果集中，必须保留 id，为了 memcache 统一处理主键
		 */
		$sql = <<<_SQL_
SELECT
		 id
		,id	as idStatus
		,idUser as idUser
FROM	
		Status
WHERE	
		idUser IN ($condition_in)
		AND isMms = 'Y'
		$condition_other
ORDER BY
		timeCreate desc
LIMIT 
		$start,$num
_SQL_;

		$rows = JWDB_Cache::GetQueryResult($sql,true);


		$status_ids = array();
		$user_ids = array();
		if ( !empty($rows) )
		{
			$status_ids = JWFunction::GetColArrayFromRows($rows, 'idStatus');
			$user_ids 	= array_unique(JWFunction::GetColArrayFromRows($rows, 'idUser'));
		}

		return array ( 	 'status_ids'	=> $status_ids
						,'user_ids'		=> $user_ids
					);
	}


	/*
	 *	获取 public_timeline 的 idStatus 
	 *	@return	array	array ( 'status_ids'=>array(), 'user_ids'=>array() )
	 */
	static public function GetStatusIdsFromPublic($num=self::DEFAULT_STATUS_NUM, $start=0, $idSince=null, $timeSince=null)
	{
		$num	= intval($num);
		$start	= intval($start);

		if ( !is_int($num) || !is_int($start) )
			throw new JWException('must int');

		$condition_other = null;
		if( $idSince > 0 ){
			$condition_other .= " AND Status.id > $idSince";
		}
		if( $timeSince ) {
			$condition_other .= " AND Status.timeCreate > '$timeSince'";
		}

		$sql = <<<_SQL_
SELECT		
			Status.id	as idStatus
			,Status.idUser	as idUser
FROM		
			Status force index(IDX__Status__timeCreate)
WHERE		
			Status.idUserReplyTo IS NULL
			AND Status.idPicture IS NOT NULL
			AND Status.isProtected = 'N'
			$condition_other
ORDER BY 	
			Status.timeCreate desc
LIMIT 		$start,$num
_SQL_;
			//AND User.id<>1927 -- XXX block youyouwan

		$rows = JWDB_Cache::GetQueryResult($sql,true);

		$status_ids = JWFunction::GetColArrayFromRows($rows, 'idStatus');
		$user_ids 	= array_unique(JWFunction::GetColArrayFromRows($rows, 'idUser'));

		return array ( 	 'status_ids'	=> $status_ids
						,'user_ids'		=> $user_ids
					);
	}


	/*
	 *	规范命名方式，以后都应该是 GetDbRowsByIds 或者 GetDbRowById，不用在函数名称中加数据库表名
	 */
	static public function GetDbRowsByIds ($idStatuses)
	{
		return self::GetStatusDbRowsByIds($idStatuses);
	}

	static public function GetDbRowById ($idStatus)
	{
		$db_rows = self::GetDbRowsByIds(array($idStatus));
		return empty($db_rows) ? array() : $db_rows[$idStatus];
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

		$condition_in = JWDB_Cache::GetInConditionFromArray($idStatuses);

		/*
		 *	每个结果集中，必须保留 id，为了 memcache 统一处理主键
		 */
		$sql = <<<_SQL_
SELECT	 *
		,id as idStatus
FROM	Status
WHERE	id IN ($condition_in)
_SQL_;

		$rows = JWDB_Cache::GetQueryResult($sql,true);


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
		/*
		 *	传入的 unixtime 可能是数据库的 datatime 格式
	 	 */
		if ( ! is_numeric($unixtime) )
			$unixtime = strtotime($unixtime);

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
		$idStatus = JWDB_Cache::CheckInt($idStatus);

		return JWDB_Cache::DelTableRow('Status', array (	'id'	=> $idStatus ));
	}



	/*
	 * @param	int		status pk
	 * @param	int		user pk
	 * @return	bool	if user own status
	 */
	static public function IsUserOwnStatus ($idUser, $idStatus)
	{
		$idUser 	= JWDB_Cache::CheckInt($idUser);
		$idStatus	= JWDB_Cache::CheckInt($idStatus);

		$db_row = self::GetStatusDbRowById($idStatus);

		if ( empty($db_row) )
			return false;

		return $db_row['idUser']==$idUser;
	}


	/*
	 *	@param	string	status
	 *	@param	bool	jsLink	使用 js 做链接，如果是 flase 则使用 html link
	 *
	 *	@return	array	formated status & other info
	 *					array ( 'status' => ..., 'replyto' => ... );
	 */
	static public function FormatStatus ($status, $jsLink=true, $urchin=false)
	{

        $idUserReplyTo = $idStatusReplyTo = null;
        if( is_array( $status ) ){
            $idUserReplyTo = $status['idUserReplyTo'];
            $idStatusReplyTo = $status['idStatusReplyTo'];
            $status = $status['status'];
        }

		$replyto	= null;

		if ( preg_match('/^@\s*([\w\._\-]+)/',$status,$matches) )
			$replyto 	= $matches[1];
        else if ( preg_match('/^@\s*([^\s]+)\s+(.+)/',$status,$matches) )
            $replyto    = $matches[1];


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
			#				.'|(fanfou.com)'
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
<a class="extlink" title="指向其它网站的链接" href="#" onclick="JiWai.OpenLink('$url_domain$url_path');return false;">http://$url_domain/...</a>
_HTML_;
			}
			else
			{
                if( $urchin ) {
                    $url_str		= <<<_HTML_
<a class="extlink" title="指向其它网站的链接" href="http://$url_domain$url_path" target="_blank" onclick="urchinTracker('/wo/outlink/$url_domain$url_path');">http://$url_domain/...</a>
_HTML_;
                }else{
                    $url_str		= <<<_HTML_
<a class="extlink" title="指向其它网站的链接" href="http://$url_domain$url_path" target="_blank">http://$url_domain/...</a>
_HTML_;
                }
			}

			$status 		= $head_str . $url_str . $tail_str;

//$status = htmlspecialchars($status);
		}
		else
		{
			$status = htmlspecialchars($status);
		}

		if ( ! empty($replyto) ) {
            if( $idUserReplyTo ) {
                $userReply = JWUser::GetUserInfo( $idUserReplyTo );
                if( false == empty( $userReply ) ) {
                    $replyto = $userReply['nameScreen'];
                    $status		= preg_replace('/^@\s*([\w\._\-]+|[^\s]+)/',"@<a href='/$userReply[nameScreen]/'>$userReply[nameScreen]</a> ", $status);
                }
            }else{
                $replyto = null;
            //    $status		= preg_replace('/^@\s*([\w\._\-]+)/',"@<a href='/$1/'>$1</a> ", $status);
            }
        }

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
		$row = JWDB_Cache::GetQueryResult($sql);

		return $row['num'];
	}

	static public function GetStatusMmsNum($idUser)
	{
		$idUser = intval($idUser);

		if ( !is_int($idUser) )
			throw new JWException('must be int');

		$sql = <<<_SQL_
SELECT	COUNT(*) as num
FROM	Status
WHERE	idUser=$idUser
	AND isMms = 'Y'
_SQL_;
		$row = JWDB_Cache::GetQueryResult($sql);

		return $row['num'];
	}


	/*
	 *	@param	int		$idUser
	 *	@return	int		$statusNum for replies to $idUser
	 */
	static public function GetStatusNumFromReplies($idUser)
	{
		$idUser = JWDB_Cache::CheckInt($idUser);

		$sql = <<<_SQL_
SELECT	COUNT(*) as num
FROM	Status
WHERE	idUserReplyTo=$idUser
_SQL_;
		$row = JWDB_Cache::GetQueryResult($sql);

		return $row['num'];
	}


	/*
	 *	@param	int		$idUser
	 *	@return	int		$statusNum for replies to $idUser
	 */
	static public function GetStatusNumFromSelfNReplies($idUser)
	{
		$idUser = JWDB_Cache::CheckInt($idUser);

		$sql = <<<_SQL_
SELECT	COUNT(*) as num
FROM	Status
WHERE	idUserReplyTo=$idUser
		OR idUser=$idUser
_SQL_;
		$row = JWDB_Cache::GetQueryResult($sql);

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

		$condition_in = JWDB_Cache::GetInConditionFromArray($friend_ids);

		$sql = <<<_SQL_
SELECT      
        COUNT(1) as num
FROM
        Status
WHERE
        Status.timeCreate > (NOW()-INTERVAL 24 HOUR)
        AND Status.idUser IN ($condition_in)
_SQL_;
		$row = JWDB_Cache::GetQueryResult($sql);

		return $row['num'];
	}

	static public function GetStatusMmsNumFromFriends($idUser)
	{
		$idUser = intval($idUser);

		if ( !is_int($idUser) )
			throw new JWException('must be int');

		$friend_ids = JWFriend::GetFriendIds($idUser);

		array_push($friend_ids, $idUser);

		$condition_in = JWDB_Cache::GetInConditionFromArray($friend_ids);

		$sql = <<<_SQL_
SELECT      
        COUNT(1) as num
FROM
        Status
WHERE
        Status.idUser IN ($condition_in)
	AND isMms = 'Y'
_SQL_;
		$row = JWDB_Cache::GetQueryResult($sql);

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

		$result = JWDB_Cache::GetQueryResult($sql);
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
		$row = JWDB_Cache::GetQueryResult($sql);
		return $row['idMax'];
	}

	/*
	 *	获取用户的最大 idStatus
	 */
	static public function GetMaxIdStatusByUserId($idUser)
	{
		$idUser = JWDB_Cache::CheckInt($idUser);

		$sql = <<<_SQL_
SELECT	MAX(id) as idMax
FROM	Status
WHERE	idUser=$idUser
_SQL_;
		$row = JWDB_Cache::GetQueryResult($sql);

		if ( empty($row) )
			return 0;

		return $row['idMax'];
	}


}
?>
