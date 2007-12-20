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
		$rows = array();
		foreach( $friends_rows as $k => $friends_row )
		{
			$friends_array = explode(',',  $friends_row, 2);
			$device_row = JWUser::GetSearchDeviceUserIds($friends_array[0], $type, 'idUser');

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
				$rows[self::NOT_REG][$friends_array[0]] = $friends_row;
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

	static public function GetCacheKeyByTypeAndUsernameAndPassword($type, $username, $password)
	{
		$info = array($type, $username, $password);
		$md5_info = md5(serialize($info));
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array( 'JWBuddy_Import', 'GetFriendsByIdUserAndRows' ), array($md5_info));
		return $mc_key;
	}
}
?>
