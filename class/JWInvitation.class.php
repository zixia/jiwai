<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Invitation Class
 */
class JWInvitation {
	/**
	 * Instance of this singleton
	 *
	 * @var JWInvitation
	 */
	static private $msInstance;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWInvitation
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
	 *	idUser 邀请另外一个用户
	 *	当前用户邀请邮件地址加入网站
	 *	@idUser		int		发送邀请者idUser
	 *	@address	string	被邀请者的地址
	 *	@type		string	被邀请者地址的类型(msn/sms/email... )
	 *	@message	string	用户写给被邀者的话
	 *	@return		idUser	被邀请用户的 idUser，失败为 null;
	 */
	static public function Create($idUser, $address, $type, $message, $inviteCode)
	{
		$idUser		= intval($idUser);

		if ( !is_int($idUser) )
			throw new JWException('id not int');

		if ( ! JWDevice::IsValid($address,$type) ){
			JWLog::Instance()->Log(LOG_NOTICE, "JWInvitation::Create($idUser, $address, $type, ...) found invalid address");
			return null;
		}


		$sql = <<<_SQL_
INSERT INTO	Invitation
SET 		idUser			= $idUser
			, address		= '$address'
			, type			= '$type'
			, message		= '$message'
			, code			= '$inviteCode'
			, timeCreate	= NOW()
_SQL_;

		try
		{
			$result = JWDB::Execute($sql) ;
		}
		catch(Exception $e)
		{
			JWLog::Instance()->Log(LOG_ERR, $e.toText() );
			return null;
		}

		return JWDB::GetInsertId();
	}


	/*
	 *
	 *
	 */
	static public function GetInvitationRowById($idInvitations)
	{
	}


	static public function GetInvitationInfoById($idInvitation)
	{
		$idInvitation = intval($idInvitation);
		if ( 0>=$idInvitation )
			throw new JWException ('must int');

		return JWDB::GetTableRow('Invitation', array('id'=>$idInvitation));
	}

	static public function GetInvitationInfoByCode($code)
	{
		$row = JWDB::GetTableRow('Invitation', array('code'=>$code));
		$row['idInvitation'] = $row['id'];

		return $row;
	}

	static public function GetInvitationrIdByCode($code)
	{
		$row = self::GetInvitationInfoByCode($code);
		return $row['idUser'];
	}

	/*
	 *	设置 $idInvitations 之间互相为好友（包括邀请者）
	 *	@param	array of int	$idInvitations	邀请表的主键
	 */
	static public function SetReciprocal( $idInvitations )
	{
		if (empty($idInvitations))
			throw new JWException('no idInvitations');

		$idReciprocal = intval(max($idInvitations));

		$in_condition	= JWDB::GetInConditionFromArray($idInvitations);

		$sql = <<<_SQL_
UPDATE		Invitation
SET			idReciprocal=$idReciprocal
WHERE		id IN ( $in_condition )
_SQL_;
		return JWDB::Execute($sql);
	}


	static public function Accept($idInvitation)
	{
		$idInvitation = JWDB::CheckInt($idInvitation);

		$now = time();

		return JWDB::UpdateTableRow('Invitation', $idInvitation, array ('timeAccept'=>$now) );
	}


	static public function Destroy($idInvitation)
	{
		$idInvitation = JWDB::CheckInt($idInvitation);

		$sql = <<<_SQL_
DELETE FROM	Invitation
WHERE		id=$idInvitation
_SQL_;
		return JWDB::Execute($sql);
	}

}
?>
