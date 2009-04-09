<?php
/**
 * @package	JiWai.de
 * @copyright	AKA Inc.
 * @author  	zixia@zixia.net
 */

/**
 * JiWai.de Robot Lingo Class(via Api)
 */
class JWRobotLingoApi {
	/**
	 * Instance of this singleton
	 *
	 * @var JWRobotLingo
	 */
	static private $msInstance;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWRobotLingo
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
	 *
	 */
	static function	Lingo_Help($robotMsg)
	{
		$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_HELP_SUC' );
		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}

	/*
	 *
	 */
	static function	Lingo_Tips($robotMsg)
	{
		$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_TIPS_SUC' );
		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}

	/*
	 *
	 */
	static function	Lingo_Get($robotMsg, $idUser)
	{
		/*
	 	 *	解析命令参数
	 	 */
		$body = $robotMsg->GetBody();
		$body = JWTextFormat::ConvertCorner( $body );

		$address_user_id = intval($idUser);
		$address_user_row = JWDB_Cache_User::GetDbRowById($address_user_id);

		if ( ! preg_match('/^\w+\s+(\S+)\s*$/i',$body,$matches) ){
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_GET_HELP' );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		$friend_name = $matches[1];

		/*
		 *	获取被订阅者的用户信息
		 */
		$friend_user_db_row = JWUser::GetUserInfo( $friend_name );

		if ( empty($friend_user_db_row) ) {
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_NOUSER', array($friend_name,) );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		/*
		 * 检查好友关系
		 */
		if( $friend_user_db_row['protected'] == 'Y' 
				&& false == JWFollower::IsFollower($address_user_id, $friend_user_db_row['idUser'] )
		  ){
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_GET_NOPERM', array(
				$friend_user_db_row['nameScreen'],
			));
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		if( $friend_user_db_row['idConference'] ) {
			$status_ids = JWStatus::GetStatusIdsFromConferenceUser($friend_user_db_row['idUser'], 1);
		}else{
			$status_ids = JWStatus::GetStatusIdsFromUser($friend_user_db_row['idUser'], 1);
		}

		$sender = $friend_user_db_row['nameScreen'];

		if ( empty($status_ids['status_ids']) )
		{
			$status = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_GET_NOSTATUS' );
		}
		else
		{
			$status_id = $status_ids['status_ids'][0];

			$status_rows = JWStatus::GetDbRowsByIds ( array($status_id) );
			$status_row = $status_rows[$status_id];
			$status	= $status_row['status'];

			if( $status_row['idUser'] != $friend_user_db_row['idUser'] ) {
				$senderUser = JWUser::GetUserInfo( $status_row['idUser'] );
				$sender = $sender.'['.$senderUser['nameScreen'].']';
			}
		}
		

		$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_GET_SUC', array($sender, $status, ) );
		return JWRobotLogic::ReplyMsg($robotMsg, $reply );
	}

	/*
	 *
	 */
	static function	Lingo_Whois($robotMsg, $idUser)
	{
		/*
	 	 *	解析命令参数
	 	 */
		$body = $robotMsg->GetBody();
		$body = JWTextFormat::ConvertCorner( $body );

		if ( ! preg_match('/^\w+\s+(\S+)\s*$/i',$body,$matches) ) {
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_WHOIS_HELP' );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		$friend_name 		= $matches[1];
		$friend_user_row	= JWUser::GetUserInfo($friend_name);

		if ( empty($friend_user_row['idUser']) ) {
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_NOUSER', array($friend_name,) );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply );
		}


		$register_date	= date("Y年n月",strtotime($friend_user_row['timeCreate']));
	
		$reply= "姓名：$friend_user_row[nameFull]，注册时间：$register_date";

		if ( !empty($friend_user_row['bio']) )
			$reply .= "，自述：$friend_user_row[bio]";

		if ( $location = JWLocation::GetLocationName($friend_user_row['location']) )
			$reply .= "，位置：$location";

		if ( !empty($friend_user_row['url']) )
			$reply .= "，网站：$friend_user_row[url]";

		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}

	/*
	 *
	 */
	static function	Lingo_Whoami($robotMsg, $idUser)
	{
		$serverAddress = $robotMsg->GetHeader('serveraddress');

		$address_user_id = intval($idUser);

		if ( empty($address_user_id) )
		{
			// 可能 device 还在，但是用户没了。
			// 删除 device.
			JWDevice::Destroy($device_db_row['idDevice']);
			self::CreateAccount($robotMsg);
			return null;
		}

		$address_user_row = JWUser::GetUserInfo($address_user_id);
		$is_web_user = JWUser::IsWebUser($address_user_row['idUser']);
	
		if ( $is_web_user )
		{
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_WHOAMI_WEB', array( $address_user_row['nameScreen'], ) );
		}
		else
		{
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_WHOAMI_IM', array( $address_user_row['nameScreen'], ) );
		}

		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}

    static function Lingo_Dict($robotMsg, $idUser)
    {
		/*
	 	 *	解析命令参数
	 	 */
		$body = $robotMsg->GetBody();
		$body = JWTextFormat::ConvertCorner( $body );

		if ( ! preg_match('/^\w+\s+(\S+)\s*$/i',$body,$matches) ) {
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_DICT_HELP' );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		$dict_query = $matches[1];
        $dict_result = json_decode(
                file_get_contents('http://e.jiwai.de/lab/dict/?q=' . urlencode($dict_query))
                );

        if (empty($dict_result)) {
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_DICT_NIL', array($dict_query) );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
        }

        switch ($dict_result->type) {
            case 'return' :
                $reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_DICT_MATCH', array(
                            $dict_query,
                            $dict_result->result[0]->exp,
                            $dict_result->result[0]->bookname
                            ));
                break;
            case 'match' :
                $reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_DICT_GUESS', array(
                            $dict_result->result[0]->def
                            ));
                break;
            default :
                $reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_DICT_NIL', array($dict_query) );
                break;
        }

		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
    }

}
?>
