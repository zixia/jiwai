<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Message Class
 */
class JWMessage {
	/**
	 * Instance of this singleton
	 *
	 * @var JWMessage
	 */
	static private $msInstance;

	const	DEFAULT_NUM_MAX		= 9999;
	const	DEFAULT_MESSAGE_NUM	= 20;

	const	SENT	= 1;
	const	INBOX	= 2;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWMessage
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
	 *	@param	int	$time	unixtime
	 */
	static public function Create( $idUserSender, $idUserReceiver, $message, $device='web', $time=null )
	{
		$idUserSender 	= JWDB::CheckInt($idUserSender);
		$idUserReceiver	= JWDB::CheckInt($idUserReceiver);

		$time = intval($time);

		if ( 0>=$time )
			$time = time();

		// 去掉回车，替换为空格
		$message = preg_replace('[\r\n]',' ',$message);

		return JWDB_Cache::SaveTableRow('Message'
								,array(	 'idUserSender'		=> $idUserSender
										,'idUserReceiver'	=> $idUserReceiver
										,'message'			=> $message
										,'device'			=> $device
										,'timeCreate'		=> JWDB::MysqlFuncion_Now($time)
								)
							);
	}


	/*
	 * @param	int
	 * @return	bool
	 */
	static public function Destroy ($idMessage)
	{
		$idMessage = JWDB::CheckInt($idMessage);

		return JWDB::DelTableRow('Message', array (	'id'	=> $idMessage ));
	}


	/*
	 * @param	int		message pk
	 * @param	int		user pk
	 * @return	bool	if user own messsage ( either from or to )
	 */
	static public function IsUserOwnMessage ($idUser, $idMessage)
	{
		$idUser 	= intval($idUser);
		$idMessage	= intval($idMessage);

		return (
			JWDB::ExistTableRow('Message', array (	 'id'			=> intval($idMessage)
													,'idUserSender'	=> intval($idUser)
											) )
			||
			JWDB::ExistTableRow('Message', array (	 'id'				=> intval($idMessage)
													,'idUserReceiver'	=> intval($idUser)
											) )
		);
	}


	/*
	 *	获取用户的 idMessage 
	 *	@param	int		$idUser	用户的id
	 *	@param	int	$type	INBOX or SENT
	 *	@return	array	array ( 'message_ids'=>array(), 'user_ids'=>array() )
	 *	
	 *	根据 $type 选取 INBOX / SENT ，返回的数组中，会自动将不是自己的用户的数据库col name命名为 idUser
	 */
	static public function GetMessageIdsFromUser($idUser, $type=JWMessage::INBOX, $num=JWMessage::DEFAULT_MESSAGE_NUM, $start=0)
	{
		$idUser	= JWDB::CheckInt($idUser);
		$num	= JWDB::CheckInt($num);

		switch ( $type )
		{
			default:
			case JWMessage::INBOX :
				$where_col_name 	= 'idUserReceiver';
				$select_col_name	= ", idUserSender as idUser, idUserReceiver";
				break;
			case JWMessage::SENT :
				$where_col_name 	= 'idUserSender';
				$select_col_name	= ", idUserSender, idUserReceiver as idUser";
				break;
		}

		$sql = <<<_SQL_
SELECT		id	as idMessage $select_col_name
FROM		Message
WHERE		$where_col_name=$idUser
ORDER BY 	timeCreate desc
LIMIT 		$start,$num
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);

		if ( empty($rows) )
			return array( 'message_ids'=>array() , 'user_ids'=>array() );


		/*
		 *	根据参数，创建 reduce_function，并存入 JWFunction 以备下次使用
		 */
		$func_key_name 		= "JWMessage::GetMessageIdsFromSender_idMessage";
		$func_callable_name	= JWFunction::Get($func_key_name);

		if ( empty($func_callable_name) )
		{
			$reduce_function_content = 'return $row["idMessage"];';
			$reduce_function_param 	= '$row';
			$func_callable_name 	= create_function( $reduce_function_param,$reduce_function_content );

			JWFunction::Set($func_key_name, $func_callable_name);
		}

		// 装换rows, 返回 id 的 array
		$message_ids = array_map(	 $func_callable_name
									,$rows
								);



		/*
		 *	根据参数，创建 reduce_function，并存入 JWFunction 以备下次使用
		 */
		$func_key_name 		= "JWMessage::GetMessageIdsFromSender_idUser";
		$func_callable_name	= JWFunction::Get($func_key_name);

		if ( empty($func_callable_name) )
		{
			$reduce_function_content = 'return $row["idUser"];';
			$reduce_function_param 	= '$row';
			$func_callable_name 	= create_function( $reduce_function_param,$reduce_function_content );

			JWFunction::Set($func_key_name, $func_callable_name);
		}
	
		// 装换rows, 返回 id 的 array
		$user_ids = array_map(	 $func_callable_name
								,$rows
							);

		array_push($user_ids, $idUser);

		return array ( 	 'message_ids'	=> $message_ids
						,'user_ids'		=> $user_ids
					);
	}


	/*
	 *	根据 idMessage 获取 Row 的详细信息
	 *	@param	array	idMessages
	 * 	@return	array	以 idMessage 为 key 的 message row
	 * 
	 */
	static public function GetMessageDbRowsByIds ($idMessages)
	{
		if ( empty($idMessages) )
			return array();

		if ( !is_array($idMessages) )
			throw new JWException('must array');

		$idMessages = array_unique($idMessages);

		$condition_in = JWDB::GetInConditionFromArray($idMessages);

		$sql = <<<_SQL_
SELECT
		id as idMessage
		, idUserSender
		, idUserReceiver
		, message
		, UNIX_TIMESTAMP(timeCreate) AS timeCreate
		, device
FROM	Message
WHERE	id IN ($condition_in)
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);


		if ( empty($rows) ){
			$message_map = array();
		} else {
			foreach ( $rows as $row ) {
				$message_map[$row['idMessage']] = $row;
			}
		}

		return $message_map;
	}

	static public function GetMessageDbRowById ($idMessage)
	{
		$message_db_rows = JWMessage::GetMessageDbRowsByIds(array($idMessage));

		if ( empty($message_db_rows) )
			return array();

		return $message_db_rows[$idMessage];
	}


	static public function GetTimeDesc ($unixtime)
	{
		return JWStatus::GetTimeDesc($unixtime, true);
	}


	static public function FormatMessage ($message)
	{
		$formated_info = JWStatus::FormatStatus($message);
		return $formated_info['status'];
	}


	/*
	 *	@param	int		$idUser
	 *	@param	int		$type
	 *	@return	int		$messageNum for $idUser
	 */
	static public function GetMessageNum($idUser, $type=JWMessage::INBOX)
	{
		$idUser = JWDB::CheckInt($idUser);

		switch ( $type )
		{
			default:
			case JWMessage::INBOX :
				$col_name = 'idUserReceiver';
				break;
			case JWMessage::SENT :
				$col_name = 'idUserSender';
				break;
		}


		$sql = <<<_SQL_
SELECT	COUNT(*) as num
FROM	Message
WHERE	$col_name=$idUser
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}


}
?>
