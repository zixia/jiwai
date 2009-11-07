<?php
/**
 * @package		JiWai.de
 * @copyright		AKA Inc.
 * @author	 	shwdai@gmail.com 
 *
 */
class JWElement {
	/* Single instance */
	static private $mInstance = null;

	/* get Intance */
	static public function Instance(){
		return self::$mInstance==null
			? self::$mInstance = new self()
			: self::$mInstance;

	}

	/* for common elememt call */
	function __call($func,$args=array())
	{
		JWRender::Display($func, @$args[0]);
	}

	function block_statuses($args=array())
	{
		$status_ids = $args['status_ids'];
		$status_rows = JWDB_Cache_Status::GetDbRowsByIds($status_ids);
		$user_ids = JWFunction::GetColArrayFromRows($status_rows, 'idUser');
		$user_rows = JWDB_Cache_User::GetDbRowsByIds($user_ids);

		/* display */
		$args['user_rows'] = $user_rows;
		$args['status_rows'] = $status_rows;
		JWRender::Display('block_statuses_user', $args);
	}

	function block_statuses_muser($args=array()) {
		$user_ids = @$args['user_ids'];
		if (empty($user_ids)) return;

		$status_rows = JWFarrago::GetHeadStatusRows($user_ids);
		$user_rows = JWDB_Cache_User::GetDbRowsByIds($user_ids);

		/* display */
		$args['user_rows'] = $user_rows;
		$args['status_rows'] = $status_rows;
		JWRender::Display('block_statuses_user', $args);
	}

	function block_statuses_one($args=array()) 
	{
		$status = @$args['status'];
		if (empty($status)) return;
		$user = JWUser::GetUserInfo($status['idUser']);
		$status_rows = array($status['id']=>$status);
		$user_rows = array($status['idUser']=>$user);

		/* display */
		$args['user_rows'] = $user_rows;
		$args['status_rows'] = $status_rows;
		JWRender::Display('block_statuses_user', $args);
	}

	function _block_statuses_conference($args=array())
	{
		global $g_page_user_id;
		$user = JWUser::GetUserInfo($g_page_user_id);
		$total = JWStatus::GetStatusNumFromConference($user['idConference']);
		$pager = new JWPager(array('rowCount'=>$total-1));
		$status_data = JWStatus::GetStatusIdsFromConferenceUser( $g_page_user_id, $pager->pageSize, $pager->offset+1);

		/* display */
		$this->block_statuses($status_data);
		$this->block_pager( array('pager'=>$pager) );
	}

	function block_statuses_nore($args=array())
	{
		global $g_page_user_id;
		$user = JWUser::GetUserInfo($g_page_user_id);
		$total = JWDB_Cache_Status::GetStatusNumFromUserNoRe($g_page_user_id);
		$pager = new JWPager(array('rowCount'=>$total-1));
		$status_data = JWDB_Cache_Status::GetStatusIdsFromUserNoRe($g_page_user_id, $pager->pageSize, $pager->offset);

		/* display */
		$this->block_statuses($status_data);
		$this->block_pager( array('pager'=>$pager) );
	}

	function block_statuses_with_friends($args=array())
	{
		global $g_page_user_id;
		$user = JWUser::GetUserInfo($g_page_user_id);
		$total = JWStatus::GetStatusNumFromFriends($g_page_user_id);
		$pager = new JWPager(array('rowCount'=>$total-1));
		$status_data = JWDB_Cache_Status::GetStatusIdsFromFriends( (string)$g_page_user_id, $pager->pageSize, $pager->offset+1);

		/* display */
		$this->block_statuses($status_data);
		$this->block_pager( array('pager'=>$pager) );
	}

	function block_statuses_with_friendsnew($args=array())
	{
		global $g_page_user_id;
		$host = '10.1.40.10';
		$port = 4002;
		$status_data = JWRemote::GetFriendStatus($g_page_user_id);
		$total = count($status_data['status_ids']);
		$pager = new JWPager(array('rowCount'=>$total));
		$offset = $pager->offset;
		$pagesize = $pager->pageSize;
		$status_data = JWUtility::SliceStatusData($status_data, $pagesize, $offset);

		/* display */
		$this->block_statuses($status_data);
		$this->block_pager( array('pager'=>$pager) );
	}

	function block_statuses_user($args=array())
	{
		global $g_page_user_id, $g_current_user_id;
		$user = JWUser::GetUserInfo($g_page_user_id);
		if ( $user['idConference'] )
		{
			return $this->_block_statuses_conference($args);
		}

		if (JWSns::IsProtected($user, $g_current_user_id))
			return;

		$total = JWStatus::GetStatusNum($g_page_user_id);
		$pager = new JWPager(array('rowCount'=>$total-1));
		$status_data = JWDB_Cache_Status::GetStatusIdsFromUser( (string)$g_page_user_id, $pager->pageSize, $pager->offset+1);

		/* display */
		$this->block_statuses($status_data);
		$this->block_pager( array('pager'=>$pager) );
	}

	/* wo related statuses fetch */
	function block_statuses_wo_archive()
	{
		$user_id = JWLogin::GetCurrentUserId();
		$user = JWUser::GetUserInfo($user_id);
		$total = JWDB_Cache_Status::GetStatusNum($user_id);
		$pager = new JWPager(array('rowCount'=>$total));
		$status_data = JWDB_Cache_Status::GetStatusIdsFromUser((string)$user_id, $pager->pageSize, $pager->offset);

		/* display */
		$this->block_statuses($status_data);
		$this->block_pager( array('pager'=>$pager) );
	}

	function block_statuses_wo_replies()
	{
		$user_id = JWLogin::GetCurrentUserId();
		$user = JWUser::GetUserInfo($user_id);

		$total = JWDB_Cache_Status::GetStatusNumFromReplies($user_id);
		$pager = new JWPager(array('rowCount'=>$total));
		$status_data = JWDB_Cache_Status::GetStatusIdsFromReplies((string)$user_id, $pager->pageSize, $pager->offset);

		/* display */
		$this->block_statuses($status_data);
		$this->block_pager( array('pager'=>$pager) );
	}

	function block_statuses_thread($args=array())
	{
		$status_id = @$args['thread_id'];
		$status_data = JWDB_Cache_Status::GetStatusIdsByIdThread( $status_id, 9999);

		/* display */
		$this->block_statuses($status_data);
	}

	function block_statuses_favourites($args=array())
	{
		global $g_page_user_id, $g_current_user_id;
		($user_id = $g_page_user_id) 
			|| ($user_id=JWLogin::GetCurrentUserId());
		$user = JWUser::GetUserInfo($user_id);

		if (JWSns::IsProtected($user, $g_current_user_id))
			return;

		$total = JWFavourite::GetFavouriteNum($user_id);
		$pager = new JWPager(array('rowCount'=>$total));
		$status_data = JWFavourite::GetFavouriteData($user_id, $pager->pageSize, $pager->offset );

		/* display */
		$this->block_statuses($status_data);
		$this->block_pager( array('pager'=>$pager) );
	}

	function block_statuses_wo_with_friends()
	{
		$user_id = JWLogin::GetCurrentUserId();
		$user = JWUser::GetUserInfo($user_id);

		$total = JWStatus::GetStatusNumFromFriends($user_id);
		$pager = new JWPager(array('rowCount'=>$total));
		$status_data = JWDB_Cache_Status::GetStatusIdsFromFriends( (string)$user_id, $pager->pageSize, $pager->offset);

		/* display */
		$this->block_statuses($status_data);
		$this->block_pager( array('pager'=>$pager) );
	}

	function block_statuses_wo_with_friendsnew()
	{
		$user_id = JWLogin::GetCurrentUserId();
		$host = '10.1.40.10';
		$port = 4002;
		$status_data = JWRemote::GetFriendStatus($user_id);
		$total = count($status_data['status_ids']);
		$pager = new JWPager(array('rowCount'=>$total));
		$offset = $pager->offset;
		$pagesize = $pager->pageSize;
		$status_data = JWUtility::SliceStatusData($status_data, $pagesize, $offset);

		/* display */
		$this->block_statuses($status_data);
		$this->block_pager( array('pager'=>$pager) );
	}

	function block_statuses_search($args=array())
	{
		$page_size = 20;
		$extra = isset($args['extra']) ? $args['extra'] : array();
		$value = isset($args['value']) ? $args['value'] : array();
		$result = isset($args['result']) ? $args['result'] : array();

		if ( empty($result) ) {
			$q = isset($_GET['q']) ? $_GET['q'] : null;
			if ( !$q ) return;
			$_GET['q'] = $q = preg_replace('/\\\\/', '', $q);
			$page = isset($_GET['page']) ? abs(intval($_GET['page'])) : 1;
			$result = JWSearch::SearchStatus($q, $page, 20, $extra);
		}

		$total = $result['count'];
		$status_ids = $result['list'];

		$status_data = array(
				'status_ids' => $status_ids,
				'is_skip' => true,
				);
		$page_options = array( 
			'rowCount' => $total,
			'pageSize' => $page_size,
			);
		if( false==empty($value) ) {
			$page_options['valueArray'] = $value;
		}
		$pager = new JWPager( $page_options );

		/* display */
		$this->block_statuses($status_data);
		$this->block_pager( array('pager'=>$pager) );
	}

	function block_statuses_mms()
	{
		global $g_page_user_id, $g_current_user_id;
		$user_id = $g_page_user_id 
			? $g_page_user_id : JWLogin::GetCurrentUserId();
		$user = JWUser::GetUserInfo($user_id);

		if (JWSns::IsProtected($user, $g_current_user_id))
			return;

		$total = JWStatus::GetStatusMmsNum($user_id);
		$pager = new JWPager(array(
					'rowCount' => $total,
					'pageSize' => 10,
					));

		$status_data = JWStatus::GetStatusIdsFromUserMms($user_id, $pager->pageSize, $pager->offset);

		/* display */
		$this->block_statuses($status_data);
		$this->block_pager( array('pager'=>$pager) );
	}

	function block_statuses_tag($args=array())
	{
		global $g_page_user_id, $g_current_user_id;
		$tag_ids = array();
		$topic_only = true;
		$author_user_id = false;
		if (isset($args['tag_ids']))
			$tag_ids = $args['tag_ids'];
		if (isset($args['topic_only']))
			$topic_only = $args['topic_only'];
		if (isset($args['author_user_id']))
			$author_user_id = $args['author_user_id'];

		if ( $author_user_id ) {
			$tag_id = abs(intval($tag_ids));
			$total = JWDB_Cache_Status::GetCountPostByIdTagAndIdUser($tag_id, $author_user_id);
			$pager = new JWPager(array(
						'rowCount' => $total,
						));
			$status_data = JWDB_Cache_Status::GetStatusIdsPostByIdTagAndIdUser($tag_id, $author_user_id, $pager->pageSize, $pager->offset);
		} else {
			settype($tag_ids, 'array');
			if ( empty($tag_ids) ) {
				$user_id = $g_page_user_id|$g_current_user_id;
				$tag_ids = JWTagFollower::GetFollowingIds($user_id);
			}

			$total = JWStatus::GetStatusNumFromTagIds($tag_ids, $topic_only);
			$pager = new JWPager(array(
						'rowCount' => $total,
						));
			$status_data = JWStatus::GetStatusIdsFromTagIds($tag_ids, $pager->pageSize, $pager->offset, $topic_only);
		}

		/* display */
		$this->block_statuses($status_data);
		$this->block_pager( array('pager'=>$pager) );
	}

	function block_statuses_public()
	{
		//one user one line -- simple;	
		$status_data = JWStatus::GetStatusIdsFromPublic(100);
		$combined = array_combine($status_data['status_ids'], $status_data['user_ids']);
		$combined = array_unique($combined);
		$m = count($combined)>20 ? 20 : count($combined);
		$status_ids = array_slice(array_keys($combined),0,$m);
		$user_ids = array_slice(array_values($combined),0,$m);
		$status_data = array(
				'status_ids' => $status_ids,
				'user_ids' => $user_ids,
				);

		/* display */
		$this->block_statuses($status_data);
	}

	function block_dm($args=array())
	{
		$user_id = JWLogin::GetCurrentUserId();
		if ( isset($args['reply']) )
		{
			$box = JWMessage::INBOX;
			$messages = array($args['reply']);
			$user_ids = array($args['reply']['idUserSender']);
			$headline = true; 
		}
		else
		{
			$box = isset($args['box']) ? $args['box'] : 'in';
			if ( $box == 'out' ) $box = JWMessage::OUTBOX;
			if ( $box == 'in' ) $box = JWMessage::INBOX;
			if ( $box == 'no' ) $box = JWMessage::NOTICE;

			//messages
			$total = JWMessage::GetMessageNum($user_id, $box);
			$pager = new JWPager(array('rowCount'=>$total));
			$message_data = JWMessage::GetMessageIdsFromUser($user_id, $box, $pager->pageSize, $pager->offset);
			$message_ids = $message_data['message_ids'];
			$user_ids = $message_data['user_ids'];
			$messages = JWMessage::GetDbRowsByIds($message_ids);
			$headline = false;
			
			if ($box != JWMessage::OUTBOX) {
				$new_ids = array();
				foreach( $messages AS $one ) {
					if('notRead'==$one['messageStatusReceiver'])
						$new_ids[] = $one['id'];
				}
				JWMessage::SetMessageStatus($new_ids, JWMessage::INBOX, JWMessage::MESSAGE_HAVEREAD);
			}
		}

		//messages and users
		$users = JWDB_Cache_User::GetDbRowsByIds( $user_ids );

		$reply_ids = JWFunction::GetColArrayFromRows($messages, 'idMessageReplyTo');
		$reply_ids = array_diff(array_unique($reply_ids),array(NULL));
		$replies = JWDB_Cache_Message::GetDbRowsByIds($reply_ids);

		JWRender::Display('block_dm', array(
					'inbox' => ($box!=JWMessage::OUTBOX),
					'messages' => $messages,
					'replies' => $replies,
					'users' => $users,
					'headline' => $headline,
					));
		if ($pager) $this->block_pager( array('pager'=>$pager) );
	}
}
?>
