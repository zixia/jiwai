<?php
/**
 * @package	 JiWai.de
 * @copyright   AKA Inc.
 * @author	  wqsemc@jiwai.com
 * @version	 $Id$
 */

/**
 * JWBuddy_Import
 */

class JWBuddy_Import
{
	/**
	 * Instance of this singleton
	 *
	 * @var 
	 */
	static private $msInstance;

	const HAVE_FOLLOW = 0;
	const NOT_FOLLOW = 1;
	const NOT_REG = 2;

	/**
	 * Instance of this singleton class
	 *
	 * @return 
	 */
	static public function &Instance()
	{
		if (!isset(self::$msInstance)) {
			$class = __CLASS__;
			self::$msInstance = new $class;
		}
		return self::$msInstance;
	}

	static public function GetFriendsByIdUserAndRows($idUser, $friends_rows, $type=array('email', 'msn', 'gtalk','newsmth','facebook','yahoo'))
	{
		$idUser = JWDB::CheckInt( $idUser );
		$rows = array(
			self::HAVE_FOLLOW => array(),
			self::NOT_FOLLOW => array(),
			self::NOT_REG => array(),
		);

		foreach( $friends_rows as $k => $friends_row )
		{
			if ( is_array($friends_row) )
			{
				$friends_row = $friends_row['email'] . ',' . $friends_row['nameScreen'];
			}

			$friends_array = explode(',',  $friends_row, 2);
			$device_address = $friends_array[0];

			$device_row = JWUser::GetSearchDeviceUserIds($device_address, $type, 'idUser');

			if (!empty($device_row))
			{
				$idFriend = $device_row[0];
				$is_follow = JWFollower::IsFollower($idFriend, $idUser);
				if ($is_follow)
					$rows[self::HAVE_FOLLOW][$idFriend] = $friends_row;
				else
					$rows[self::NOT_FOLLOW][$idFriend] = $friends_row;
			}
			else
			{
				$rows[self::NOT_REG][$device_address] = $friends_row;
			}
		}

		return $rows;
	}

	static public function SendMailInvitation($idUser, $emails, $subject="", $idInvited=null)
	{
		if (null==$idInvited)
			$idInvited = JWUser::GetIdEncodedFromIdUser( $idUser );

		$user_info = JWUser::GetUserInfo( $idUser );
		if (empty($user_info))
			return 0;

		$count = 0;
		foreach( $emails as $email ) 
		{
			if( true==JWDevice::IsValid( $email, 'email' ) )
				if( JWMail::SendMailInvitation( $user_info, $email, $subject, $idInvited ) )
					$count ++;
		}

		return $count;
	}

    static public function GenInvitationMessage($user_info = array(), $idInvited)
    {
        return implode(' ', array(
                    '你在叽歪上的好友',
                    $user_info['nameScreen'],
                    '('.$user_info['nameFull'].')',
                    '邀请你加入叽歪',
                    '点击下面的链接',
                    'http://JiWai.de/wo/invitations/i/' . $idInvited,
                    '接受邀请',
                    ));
    }

    static public function SendIMInvitation($idUser, $accounts, $domain='msn', $idInvited=null)
    {
        if ( !in_array($domain, JWDevice::$allArray) )
            return 0;

		if (null==$idInvited)
			$idInvited = JWUser::GetIdEncodedFromIdUser( $idUser );

		$user_info = JWUser::GetUserInfo( $idUser );
		if (empty($user_info))
			return 0;

		$count = 0;
        $robot_msg = new JWRobotMsg;
        $robot_msg->SetType($domain);
        $robot_msg->SetHeader('LingoFunc', 'INVITE');
        $robot_msg->SetBody(self::GenInvitationMessage($user_info, $idInvited));
		foreach( $accounts as $account ) 
		{
			if( true==JWDevice::IsValid( $account, $domain ) )
            {
                $robot_msg->SetAddress($account);
				if( JWRobot::SendMtQueue( $robot_msg ) )
					$count ++;
            }
		}

		return $count;
    }

    static public function SendApiInvitation($idUser, $accounts, $domain='fanfou', $idInvited=null)
    {
		if (null==$idInvited)
			$idInvited = JWUser::GetIdEncodedFromIdUser( $idUser );

		$user_info = JWUser::GetUserInfo( $idUser );
		if (empty($user_info))
			return 0;

        $bindOther = JWBindOther::GetBindOther($idUser);
        if ( !isset($bindOther[$domain]) )
            return 0;
        else
            $bindOther = array ($domain => $bindOther[$domain]);

		$count = 0;
        $queue_instance = JWPubSub::Instance('spread://localhost/');
        $queue_message  = self::GenInvitationMessage($user_info, $idInvited);
		foreach( $accounts as $account ) 
		{
			if( true==JWDevice::IsValid( $account, 'api' ) )
            {
                $bindOther[$domain]['direct_message'] = true;
                $bindOther[$domain]['receiver']  = $account;
                $queue_data = array(
                        'device' => 'web',
                        'message' => $queue_message,
                        'not_reply' => true,
                        'not_conference' => true,
                        'bind' => $bindOther,
                        'sender' => $idUser,
                        );

                $queue_instance->Publish('/statuses/bindother', $queue_data);
                $count ++;
            }
		}

		return $count;
    }

	static public function GetCacheKeyByTypeAndUsernameAndPassword($type, $username, $password)
	{
		$info = array($type, $username, $password);
		$md5_info = md5(serialize($info));
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array( 'JWBuddy_Import', 'GetFriendsByIdUserAndRows' ), array($md5_info));
		return $mc_key;
	}
}
?>
