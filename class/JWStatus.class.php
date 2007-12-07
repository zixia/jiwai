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
	 *  	options may contains idTag, idThread
	 */
	static public function GetReplyInfo( $status, $options = array() )
	{

		$is_extends_tag = true;

		$tag_id = isset( $options['idTag'] ) ? $options['idTag'] : null;
		$thread_id = isset( $options['idThread'] ) ? $options['idThread'] : null;
		$user_id = isset( $options['idUserReplyTo'] ) ? $options['idUserReplyTo'] : null;
		$status_id = isset( $options['idStatusReplyTo'] ) ? $options['idStatusReplyTo'] : null;

		// not extends tag_id || if extends 
		if( $is_extends_tag == false ) $tag_id = null;

		$rtn_array = array(
			'user_id' => $user_id,
			'status_id' => $status_id,
			'tag_id' => $tag_id,
			'thread_id' => $thread_id,
			'status' => $status,
		);

		if( empty( $status ) )
		{
			return $rtn_array;
		}

		if ( preg_match( "/^(\s*#\s*)([^\s<>@]{3,20})(\b|\s)(.+)/", $status, $matches ) ) 
		{
			$tag_name = $matches[2];
			$tag_row = JWTag::GetDbRowByName( $tag_name );
			$tag_id = empty( $tag_row ) ? JWTag::Create( $tag_name ) : $tag_row['id'];

			$status = preg_replace( '/^'.$matches[1].$matches[2].'/', '', $status );

			$rtn_array['status'] = $status;
			$rtn_array['tag_id'] = $tag_id;
		}
		
		/**
		 * if not set idUserReplyTo in options;
		 */
		if ( $user_id == null ) 
		{
			if ( false == preg_match( "/^(\s*@\s*)([^\s<>@]{3,20})(\b|\s)(.+)/", $status, $matches ) ) 
			{
				return $rtn_array;
			}else
			{
				$status = preg_replace( '/^'.$matches[1].$matches[2].'/', '', $status );
				$rtn_array['status'] = $status;
			}
			$reply_to_user = $matches[2];
			$user_db_row = JWUser::GetUserInfo($reply_to_user);

			if( empty( $user_db_row ) )
			{
				return $rtn_array;
			}

			$user_id = $user_db_row['id'];
		}

		/**
		 * if not set idStatusReplyTo
		 */
		if( $status_id == null ) {
			$status_id = JWStatus::GetMaxIdStatusByUserId( $user_id, $options );
		}

		if ( $status_id 
			&& $reply_to_status_row = self::GetDbRowById( $status_id ) ) 
		{
			if( $thread_id == null ) 
			{
				$thread_id = ( $reply_to_status_row['idThread'] ) ? 
					$reply_to_status_row['idThread'] : $status_id;
			}
			if( $tag_id == null && $is_extends_tag == true ) 
			{
				$tag_id = $reply_to_status_row['idTag'] ;
			}
		}

		$rtn_array['tag_id'] = $tag_id;
		$rtn_array['thread_id'] = $thread_id;
		$rtn_array['user_id'] = $user_id;
		$rtn_array['status_id'] = $status_id;

		return $rtn_array;
	}


	/*
	 *	@param	int	$time	unixtime
	 */
	static public function Create( $idUser, $status=null, $device='web', $timeCreate=null, $isSignature='N', $options=array() )
	{
		/** signature html encode filter */
		if( $isSignature == 'Y' ) {
			$status = htmlSpecialChars_Decode( $status );
		}
		
		/** timeCreate **/
		$timeCreate = isset($options['timeCreate']) ? 
			$options['timeCreate'] : ($timeCreate ? $timeCreate : time());
		$isMms = ( isset($options['isMms']) && $options['isMms'] == 'Y' ) ? 'Y' : 'N';

		//user info
		$userInfo  = JWUser::GetDbRowById( $idUser );
		$isProtected = $userInfo['protected'];

		/** choose idPicture | idConference */
		$idPicture = isset( $options['idPicture'] ) ? $options['idPicture'] : $userInfo['idPicture'];
		$idConference = ( isset( $options['idConference'] ) && $options['idConference'] ) ? 
			$options['idConference'] : $userInfo['idConference'];

		//options about thread tag
		$idThread = isset( $options['idThread'] ) ? $options['idThread'] : null;
		$idTag = isset( $options['idTag'] ) ? $options['idTag'] : null;

		/** ReplyInfo */
		$idUserReplyTo = null;
		$idStatusReplyTo = null;
		$idConference = null;

		if( isset( $options['idUserReplyTo'] ) ) {
			$idUserReplyTo = $options['idUserReplyTo'];
			$idStatusReplyTo = $options['idStatusReplyTo'];
		}else{
			$statusPost = JWRobotLingoBase::ConvertCorner($status);
			$reply_info = JWStatus::GetReplyInfo($statusPost, $options);
			if( false == empty( $reply_info ) ){
				$idUserReplyTo = $reply_info['user_id'];
				$idStatusReplyTo = $reply_info['status_id'];
				$idThread = $reply_info['thread_id'];
				$idTag = $reply_info['tag_id'];
				$status = $reply_info['status'];
			}
		}

		/** parter */
		$idPartner = null;
		if( isset( $options['idPartner'] ) && intval($options['idPartner']) ){
			$partner = JWPartner::GetDbRowById( intval($options['idPartner']) );
			if( false == empty( $partner ) ){
				$idPartner = $partner['id'];
			}
		}

		$is_succ= JWDB_Cache::SaveTableRow('Status', array( 
			'idUser' => $idUser,
			'status' => $status,
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
			'idThread' => $idThread,
			'idTag' => $idTag,
		));

		if( $is_succ && $idThread )
		{
			JWDB_Cache_Status::GetCountReply( $idThread, true );
		}
		if( $is_succ && $idTag )
		{
			JWDB_Cache_Status::GetCountPostByIdTag( $idTag, true );
		}
		if( $is_succ && $idTag && empty( $idThread ) )
		{
			JWDB_Cache_Status::GetCountTopicByIdTag( $idTag, true );
		}

		return $is_succ;
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
	
	static public function SetIdThread( $idStatus, $idThread = null ) 
	{
		$idStatus = JWDB::CheckInt( $idStatus );

		$is_succ = JWDB_Cache::UpdateTableRow( 'Status', $idStatus, array(
			'idThread' => $idThread,
		));

		if( $is_succ && $idThread ) 
		{
			JWDB_Cache_Status::GetCountReply($idThread, true);
		}

		return $is_succ;
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

		$friend_ids = JWFollower::GetFollowingIds($idUser);
		
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

		$friend_ids = JWFollower::GetFollowingIds($idUser);
		
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
				,'user_ids'	=> $user_ids
		);
	}

	static public function GetDbRowById ($idStatus)
	{
		$rows = self::GetDbRowsByIds( array( $idStatus ) );
		return empty( $rows ) ? array() : $rows[ $idStatus ];
	}

	/*
	 *	根据 idStatus 获取 Row 的详细信息
	 *	@param	array	idStatuses
	 * 	@return	array	以 idStatus 为 key 的 status row
	 * 
	 */
	static public function GetDbRowsByIds ($idStatuses)
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

		if( count( $idStatuses ) > 1 ) {
			$rows = JWDB_Cache::GetQueryResult($sql,true);
		}else{
			$rows = JWDB::GetQueryResult($sql,true);
		}

		if ( empty($rows) ){
			$status_map = array();
		} else {
			foreach ( $rows as $row ) {
				$row['status'] = self::SimpleFormat( $row );	
				$status_map[$row['idStatus']] = $row;
			}
		}

		return $status_map;
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
			return "$duration 秒前";
/*			if ( $duration > 30 ){
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
*/		}
	}


	/*
	 * @param	int
	 * @return	bool
	 */
	static public function Destroy ($idStatus)
	{
		$idStatus = JWDB_Cache::CheckInt($idStatus);
		$statusRow = JWDB_Cache::GetTableRow('Status', array('id'=>$idStatus), 1);

		$is_succ = JWDB_Cache::DelTableRow('Status', array (	'id'	=> $idStatus ));

		if( $is_succ ) 
		{
			if( $statusRow['idThread'] )
			{
				JWDB_Cache_Status::GetCountReply($statusRow['idThread'], true);
			}else{
				JWDB_Cache_Status::GetCountReply($statusRow['id'], true);
			}

			if( $statusRow['idTag'] ) 
			{
				JWDB_Cache_Status::GetCountTopicByIdTag($statusRow['idTag'], true);
				JWDB_Cache_Status::GetCountPostByIdTag($statusRow['idTag'], true);
			}
		}

		return $is_succ;
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

		$db_row = self::GetDbRowById($idStatus);

		if ( empty($db_row) )
			return false;

		return $db_row['idUser']==$idUser;
	}
		
	
	/**
		这个方法必须给出说明，
			status_row 可以是String，这时 id_user_reply_to 是回复用户 id 
			status_row 本应为 一条记录;
	*/
	static public function SimpleFormat( $status_row, $id_user_reply_to=null ) 
	{

		if( is_string( $status_row ) ){
			$status_row = array(
				'status' => $status_row,
				'idUserReplyTo' => $id_user_reply_to,
			);
		}

		$idUserReplyTo = $status_row['idUserReplyTo'];
		$status = $status_row['status'];

		if( $idUserReplyTo ) 
		{
			$user = JWUser::GetUserInfo( $idUserReplyTo ) ;

			if ( preg_match( "/^@\s*([^\s<>@]{3,20})(\b|\s)(.+)/", $status, $matches ) ) 
			{
				$reply_to = $matches[1];
				$reply_user = JWUser::GetUserInfo( $reply_to ) ;

				if( $reply_user['id'] == $user['id'] ) 
					$status = preg_replace( "/^@\s*(".$user['nameScreen'].")/i", '', $status );
			}

			$status = "@$user[nameScreen] $status";
		}

		return $status;
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

		$reply_to_user_id = $reply_to_status_id = $tag_id = $thread_id = $device = null;
		if( is_array( $status ) ){
			$reply_to_user_id = $status['idUserReplyTo'];
			$reply_to_status_id = $status['idStatusReplyTo'];
			$tag_id = $status['idTag'];
			$device = $status['device'];

			$status = $status['status'];
		}

		$replyto = null;

		if ( preg_match( "/^@\s*([^\s<>@]{3,20})(\b|\s)/", $status, $matches ) ) 
			$replyto = $matches[1];

		$skip_url_regex = '#'.'(komoo.cn)'
				// .'|(fanfou.com)'
				.'#';

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
			$head_str = htmlspecialchars($matches[1]);
			$url_domain = htmlspecialchars($matches[2]);
			$url_path = htmlspecialchars($matches[3]);
			$tail_str = htmlspecialchars($matches[4]);


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
				$url_str = <<<_HTML_
					<a class="extlink" title="指向其它网站的链接" href="#" onclick="JiWai.OpenLink('$url_domain$url_path');return false;">http://$url_domain/...</a>
_HTML_;
			}
			else
			{
				if( $urchin ) {
					$url_str = <<<_HTML_
						<a class="extlink" title="指向其它网站的链接" href="http://$url_domain$url_path" target="_blank" onclick="urchinTracker('/wo/outlink/$url_domain$url_path');">http://$url_domain/...</a>
_HTML_;
				}else{
					$url_str = <<<_HTML_
						<a class="extlink" title="指向其它网站的链接" href="http://$url_domain$url_path" target="_blank">http://$url_domain/...</a>
_HTML_;
				}
			}

			$status = $head_str . $url_str . $tail_str;

		}
		else
		{
			$status = htmlspecialchars($status);
		}

		if( $reply_to_user_id ) 
		{
			$reply_to_user = JWUser::GetUserInfo( $reply_to_user_id );
			if ( preg_match( "/^@\s*([^\s<>@]{3,20})(\b|\s)(.+)/", $status, $matches ) ) 
			{    
				$u = JWUser::GetUserInfo( $matches[1] );
				if( $u['id'] == $reply_to_user_id ) 
				{
					$status = preg_replace( '/@\s*('.$matches[1].')/i', '', $status );
				}
			}

			$reply_to_user_name_url = $reply_to_user['nameUrl'];
			$reply_to_user_name_screen = $reply_to_user['nameScreen'];
			$status = "@<a href='/$reply_to_user_name_url/'>$reply_to_user_name_screen</a> $status";
		}else
		{
			$reply_to_user_name_url = null;
			$reply_to_user_name_screen = null;
		} 

		if( $tag_id && null == $reply_to_user_id ) 
		{
			$tag_row = JWTag::GetDbRowById( $tag_id );
			if( false == empty( $tag_row ) )
			{
				$status = "#<a href='/t/$tag_row[name]/'>$tag_row[name]</a> $status";
			}
		}

		// Add @ Link For other User
		$status = preg_replace(	 "/@\s*([^\s<>@]{3,20})(\b|\s|$)/" ,"@<a href='/\\1/'>\\1</a>\\2" ,$status );

		return array ( 
			'status' => $status, 
			'replyto' => $reply_to_user_name_url,
			'replytoname' => $reply_to_user_name_screen,
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
	 *	@param	int		$idUser
	 *	@return	int		$statusNum
	 */
	static public function GetStatusNumFromConference($idConference)
	{
		$idConference = JWDB::CheckInt($idConference);

		$sql = <<<_SQL_
SELECT      
        COUNT(1) as num
FROM
        Status
WHERE
        idConference = $idConference
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

		$friend_ids = JWFollower::GetFollowingIds($idUser);

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

		$friend_ids = JWFollower::GetFollowingIds($idUser);

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
	static public function GetMaxIdStatusByUserId( $idUser, $options=array() )
	{
		$idUser = JWDB_Cache::CheckInt($idUser);

		$threadCond = null;
		if( isset( $options['idThread'] ) )
		{
			$threadCond = " AND idThread=$options[idThread]";
		}


		$sql = <<<_SQL_
SELECT	MAX(id) as idMax
FROM	Status
WHERE	idUser=$idUser
	$threadCond
_SQL_;
		$row = JWDB_Cache::GetQueryResult($sql);

		if ( empty($row) )
			return 0;

		return $row['idMax'];
	}

	static public function SetIdConference($idStatus, $idConference=null) {

		$idStatus = JWDB::CheckInt( $idStatus );

		$uArray = array(
				'idConference' => $idConference,
			);

		if( JWDB::ExistTableRow( 'Status', array( 'id' => $idStatus ) ) ) {
			return JWDB::UpdateTableRow( 'Status', $idStatus, $uArray );
		}

		return false;
	}

	static public function GetCountReply( $idStatus ) {

			$idStatus = JWDB::CheckInt( $idStatus );

			$sql = <<<_SQL_
SELECT COUNT(1) AS num
		FROM 
				Status
		WHERE 
				idThread = $idStatus
_SQL_;

				$row = JWDB::GetQueryResult( $sql );

				return $row['num'];
	}

	/*
	 *	获取 指定idStatus 的所有回复用户
	 *	@return	rows	
	 */
	static public function GetStatusReplyFromStatus($idStatusReplyTo, $num=self::DEFAULT_STATUS_NUM, $start=0, $idSince=null, $timeSince=null)
	{
		$idStatusReplyTo = JWDB::CheckInt( $idStatusReplyTo );
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
			* 
FROM		
			Status
WHERE		
			Status.idStatusReplyTo = $idStatusReplyTo 
			$condition_other
ORDER BY 	
			Status.timeCreate asc
LIMIT 		$start,$num
_SQL_;

		$rows = JWDB_Cache::GetQueryResult($sql,true);

		if (empty($rows))
			return array();

		return $rows;
	}
    
	/*
	 *	获取 指定idStatus 的所有回复用户
	 *	@return rows	
	 */
	static public function GetStatusReplyAllFromStatus($idStatusReplyTo, $start=0, $idSince=null, $timeSince=null)
	{
		$statusRow = self::GetDbRowById($idStatusReplyTo);
		$countReply=$statusRow['countReply'];

		if(0==$countReply)
			return array();

		return self::GetStatusReplyFromStatus($idStatusReplyTo, $countReply, $start, $idSince, $timeSince); 
	}

	/*
	 *	获取 指定idStatus 的所有回复用户
	 *	@return	rows	
	 */
	static public function GetDbRowsByThread($idThread, $num=self::DEFAULT_STATUS_NUM, $start=0, $idSince=null, $timeSince=null)
	{
		$idThread = JWDB::CheckInt( $idThread );
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
			* 
FROM		
			Status
WHERE		
			Status.idThread = $idThread
			$condition_other
ORDER BY 	
			Status.timeCreate asc
LIMIT 		$start,$num
_SQL_;

		$rows = JWDB_Cache::GetQueryResult($sql,true);

		if (empty($rows))
			return array();

		return $rows;
	}

	/*
	 *	获取 指定idStatus 的所有回复
	 *	@return rows	
	 */
	static public function GetAllDbRowsByThread($idThread, $start=0, $idSince=null, $timeSince=null)
	{
		$countReply = JWDB_Cache_Status::GetCountReply( $idThread );
		if(0==$countReply)
			return array();

		return self::GetDbRowsByThread($idThread, $countReply, $start, $idSince, $timeSince); 
	}

	/*
	 * @param	int		status pk
	 * @param	int		user pk
	 * @return	bool	if user own status
	 */
	static public function IsUserCanDelStatus ($idUser, $idStatus)
	{
		if ( JWUser::IsAdmin($idUser) )
			return true;

		$idUser 	= JWDB_Cache::CheckInt($idUser);
		$idStatus	= JWDB_Cache::CheckInt($idStatus);

		$db_row = self::GetDbRowById($idStatus);

		if ( empty($db_row) )
			return false;

		return ($db_row['idUser']==$idUser) ;//|| ($db_row['idUserReplyTo']==$idUser);//楼主可删除本楼的任何帖子
	}

	/*
	 *	获取用户和好友的 idStatus，并返回相关的 idUser 以供后期组合
	 *	@param	int		$idUser	用户的id
	 *	@return	array	array ( 'status_ids'=>array(), 'user_ids'=>array() )
	 */
	static public function GetStatusIdsFromFriendsConfrence($idUser, $num=JWStatus::DEFAULT_STATUS_NUM, $start=0, $idSince=null, $timeSince=null)
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

		$friend_ids = JWFollower::GetFollowingIds($idUser);

		array_push($friend_ids, $idUser);
		$condition_in = JWDB::GetInConditionFromArray($friend_ids);

		$friend_confrence_rows = JWUser::GetDbRowsByIds($friend_ids);
		$friend_confrence_ids = JWFunction::GetColArrayFromRows($friend_confrence_rows, 'idConference');
		$friend_confrence_ids = array_unique($friend_confrence_ids);
		$friend_confrence_in = JWDB::GetInConditionFromArray($friend_confrence_ids);

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
        OR ( 
       		idConference IS NOT NULL
        	AND idConference IN ($friend_confrence_in)
        )
	AND timeCreate > (NOW()-INTERVAL 1 WEEK)
	$condition_other
ORDER BY
	timeCreate desc
LIMIT 
	$start,$num
_SQL_;

		$rows = JWDB_Cache::GetQueryResult($sql,true);

		if ( empty($rows) )
			return array();


		$status_ids = JWFunction::GetColArrayFromRows($rows, 'idStatus');
		$user_ids = array_unique(JWFunction::GetColArrayFromRows($rows, 'idUser'));

		array_push($user_ids, $idUser);

		return array (
			'status_ids' => $status_ids,
			'user_ids' => $user_ids,
		);
	}

	/*
	 *	@param	int		$idUser
	 *	@return	int		$statusNum
	 */
	static public function GetStatusNumFromFriendsConference($idUser)
	{
		$idUser = JWDB::CheckInt($idUser);
		$user_info = JWUser::GetUserInfo($idUser);

		$nums = self::GetStatusNumFromFriends($idUser); 

		$friend_ids = JWFollower::GetFollowingIds($idUser);
		array_push($friend_ids, $idUser);

		foreach($friend_ids as $friend_id)
		{
			$user_info = JWUser::GetUserInfo($friend_id);
			if($user_info['idConference'])
			{
				$nums += self::GetStatusNumFromConference($user_info['idConference']);
			}
		}

		return $nums;
	}

	/**
	 * Get count of idTag and idUser
	 */
	static public function GetCountPostByIdTagAndIdUser( $idTag, $idUser )
	{  
		$idTag = JWDB::CheckInt( $idTag );
		$idUser = JWDB::CheckInt( $idUser );

		$sql = <<<_SQL_
SELECT COUNT(1) AS num
	FROM
		Status
	WHERE
		idTag=$idTag
		AND idUser=$idUser
_SQL_;
		$row = JWDB::GetQueryResult( $sql );

		return $row['num'];
	}

	/**
	 * Get count of idTag and idUser
	 */
	static public function GetCountTopicByIdTagAndIdUser( $idTag, $idUser )
	{  
		$idTag = JWDB::CheckInt( $idTag );
		$idUser = JWDB::CheckInt( $idUser );

		$sql = <<<_SQL_
SELECT COUNT(1) AS num
	FROM
		Status
	WHERE
		idTag=$idTag
		AND idUser=$idUser
        AND idUserReplyTo IS NULL
_SQL_;
		$row = JWDB::GetQueryResult( $sql );

		return $row['num'];
	}

	/**
	 * Get status_ids from idTag
	 */
	static public function GetStatusIdsTopicByIdTag($idTag, $num=JWStatus::DEFAULT_STATUS_NUM, $start=0, $idSince=null, $timeSince=null)
	{
		$idTag	= JWDB::CheckInt($idTag);
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
		
		$sql = <<<_SQL_
SELECT
	id, idUser
FROM
	Status
WHERE 
	idTag=$idTag
	AND idUserReplyTo IS NULL
	$condition_other
ORDER BY id DESC
LIMIT $start, $num
_SQL_;

		$rows = JWDB::GetQueryResult( $sql, true );
		if( empty( $rows ) )
			return array();

		$status_ids = JWFunction::GetColArrayFromRows($rows, 'id');
		$user_ids = array_unique(JWFunction::GetColArrayFromRows($rows, 'idUser'));

		return array(
			'status_ids' => $status_ids,
			'user_ids' => $user_ids,
		);

		return $rows;
	}

	/**
	 * Get status_ids from idTag
	 */
	static public function GetStatusIdsPostByIdTag($idTag, $num=JWStatus::DEFAULT_STATUS_NUM, $start=0, $idSince=null, $timeSince=null)
	{
		$idTag	= JWDB::CheckInt($idTag);
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
		
		$sql = <<<_SQL_
SELECT
	id, idUser
FROM
	Status
WHERE 
	idTag=$idTag
	$condition_other
ORDER BY id DESC
LIMIT $start, $num
_SQL_;

		$rows = JWDB::GetQueryResult( $sql, true );
		if( empty( $rows ) )
			return array();

		$status_ids = JWFunction::GetColArrayFromRows($rows, 'id');
		$user_ids = array_unique(JWFunction::GetColArrayFromRows($rows, 'idUser'));

		return array(
			'status_ids' => $status_ids,
			'user_ids' => $user_ids,
		);

		return $rows;
	}

	/**
	 * Get status_ids from idTag and idUser
	 */
	static public function GetStatusIdsPostByIdTagAndIdUser( $idTag, $idUser, $limit=self::DEFAULT_STATUS_NUM, $offset=0 ) 
	{
		$idTag = JWDB::CheckInt( $idTag );
		$idUser = JWDB::CheckInt( $idUser );
		
		$sql = <<<_SQL_
SELECT
	id, idUser
FROM
	Status
WHERE 
	idTag=$idTag
	AND idUser=$idUser
ORDER BY id DESC
LIMIT $offset, $limit
_SQL_;
		$rows = JWDB::GetQueryResult( $sql, true );
		if( empty( $rows ) )
			return array();

		$status_ids = JWFunction::GetColArrayFromRows($rows, 'id');
		$user_ids = array_unique(JWFunction::GetColArrayFromRows($rows, 'idUser'));

		return array(
			'status_ids' => $status_ids,
			'user_ids' => $user_ids,
		);
	}

	/**
	 * Get status_ids from idTag and idUser
	 */
	static public function GetStatusIdsTopicByIdTagAndIdUser( $idTag, $idUser, $limit=self::DEFAULT_STATUS_NUM, $offset=0 ) 
	{
		$idTag = JWDB::CheckInt( $idTag );
		$idUser = JWDB::CheckInt( $idUser );
		
		$sql = <<<_SQL_
SELECT
	id, idUser
FROM
	Status
WHERE 
	idTag=$idTag
	AND idUser=$idUser
    AND idUserReplyTo IS NULL
ORDER BY id DESC
LIMIT $offset, $limit
_SQL_;
		$rows = JWDB::GetQueryResult( $sql, true );
		if( empty( $rows ) )
			return array();

		$status_ids = JWFunction::GetColArrayFromRows($rows, 'id');
		$user_ids = array_unique(JWFunction::GetColArrayFromRows($rows, 'idUser'));

		return array(
			'status_ids' => $status_ids,
			'user_ids' => $user_ids,
		);
	}
	
	/**
	 * Get Last ReplyInfo of a status;
	 */
	static public function GetLastReplyInfo( $idStatus ) 
	{
		$idStatus = JWDB::CheckInt( $idStatus );

		$sql = <<<_SQL_
SELECT 
	id, idUser, timeCreate
FROM
	Status
WHERE
	idThread = $idStatus
ORDER BY id DESC
LIMIT 0,1
_SQL_;
		$row = JWDB_Cache::GetQueryResult( $sql );
		if( empty( $row ) ) 
			return $row;

		return $row;
	}

	/**
	 * Get idTag from Status by IdUser
	 */
	static public function GetTagIdsPostByIdUser( $user_id ) 
	{
		$user_id = JWDB::CheckInt( $user_id );

		$sql = <<<_SQL_
SELECT
	idTag, COUNT(1) AS count
FROM
	Status
WHERE
	idUser=$user_id
	AND idTag IS NOT NULL
GROUP BY idTag
ORDER BY count DESC
_SQL_;

		$rows = JWDB_Cache::GetQueryResult( $sql, true );
		if( empty($rows) )
			return array();
		
		$rtn = array();
		foreach ( $rows as $one ) 
		{
			$rtn[ $one['idTag'] ] = $one['count'] ;
		}

		return $rtn;
	}

	/**
	 * Get idTag from Status by IdUser
	 */
	static public function GetTagIdsTopicByIdUser( $user_id ) 
	{
		$user_id = JWDB::CheckInt( $user_id );

		$sql = <<<_SQL_
SELECT
	idTag, COUNT(1) AS count
FROM
	Status
WHERE
	idUser=$user_id
	AND idTag IS NOT NULL
    AND idUserReplyTo IS NULL
GROUP BY idTag
ORDER BY count DESC
_SQL_;

		$rows = JWDB_Cache::GetQueryResult( $sql, true );
		if( empty($rows) )
			return array();
		
		$rtn = array();
		foreach ( $rows as $one ) 
		{
			$rtn[ $one['idTag'] ] = $one['count'] ;
		}

		return $rtn;
	}

	/**
	 * Get Count of idTag [ only post ]
	 */
	static public function GetCountPostAll() 
	{
		$sql = <<<_SQL_
SELECT COUNT(1) AS num
	FROM
		Status
	WHERE
		idTag IS NOT NULL
_SQL_;
	$row = JWDB::GetQueryResult( $sql );

		return $row['num'];
	}

	/**
	 * Get Count of idTag [ only topic ]
	 */
	static public function GetCountTopicAll() 
	{
		$sql = <<<_SQL_
SELECT COUNT(1) AS num
	FROM
		Status
	WHERE
		idTag IS NOT NULL
		AND idUserReplyTo IS NULL
_SQL_;
		$row = JWDB::GetQueryResult( $sql );
		return $row['num'];
	}

	/**
	 * Get Count of idTag [ only post ]
	 */
	static public function GetCountPostByIdTag( $idTag ) 
	{
		$idTag = JWDB::CheckInt( $idTag );

		$sql = <<<_SQL_
SELECT COUNT(1) AS num
	FROM
		Status
	WHERE
		idTag=$idTag
_SQL_;
		$row = JWDB::GetQueryResult( $sql );
		return $row['num'];
	}

	/**
	 * Get Count of idTag [ only topic ]
	 */
	static public function GetCountTopicByIdTag( $idTag ) 
	{
		$idTag = JWDB::CheckInt( $idTag );

		$sql = <<<_SQL_
SELECT COUNT(1) AS num
	FROM
		Status
	WHERE
		idTag=$idTag
		AND idUserReplyTo IS NULL
_SQL_;
		$row = JWDB::GetQueryResult( $sql);
         
		return $row['num'];
	}

	/**
	 * Get status_ids from idTag
	 */
	static public function GetStatusIdsTopic($num=JWStatus::DEFAULT_STATUS_NUM, $start=0, $idSince=null, $timeSince=null)
	{
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
		
		$sql = <<<_SQL_
SELECT
	id, idUser
FROM
	Status
WHERE 
	idTag IS NOT NULL
	AND idUserReplyTo IS NULL
	$condition_other
ORDER BY id DESC
LIMIT $start, $num
_SQL_;

		$rows = JWDB_Cache::GetQueryResult( $sql, true );
		if( empty( $rows ) )
			return array();

		$status_ids = JWFunction::GetColArrayFromRows($rows, 'id');
		$user_ids = array_unique(JWFunction::GetColArrayFromRows($rows, 'idUser'));

		return array(
			'status_ids' => $status_ids,
			'user_ids' => $user_ids,
		);

		return $rows;
	}

    /**
     *
     */
    static public function GetStatusByIdTagAndIdStatus( $idTag, $idStatus, $start=0, $limit=20 )
    {
        $idTag = JWDB::CheckInt( $idTag );
        $idStatus = JWDB::CheckInt( $idStatus );

        $sql="SELECT * FROM Status WHERE idTag='$idTag' AND idUserReplyTo IS NULL AND id!='$idStatus' ORDER BY id DESC LIMIT    $start,$limit";
        $row = JWDB_Cache::GetQueryResult( $sql, true );

        if( empty($row) )
            return array();
        return $row;
    }
    
}
?>
