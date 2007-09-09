<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Robot Lingo Class
 */
class JWRobotLingoBase {
	/**
	 * Instance of this singleton
	 *
	 * @var JWRobotLingo
	 */
	static private $msInstance;

	/*
	 *	记录所有的机器人命令，与函数的对应表。（Lingo函数以 Lingo_ 打头）
	 *	param 设置这个命令接受的最多参数。如果用户输入多于这个最大值，则不当作lingo处理。（如用户输入"on the way home"）
	 */
	static private $msRobotLingo = array (
			 'HELP'		=> array( 'func'=>'Lingo_Help' 	,'param'=>1 )
			,'TIPS'		=> array( 'func'=>'Lingo_Tips' 	,'param'=>0 )

			,'ON'		=> array( 'func'=>'Lingo_On' 	,'param'=>1)
			,'OFF'		=> array( 'func'=>'Lingo_Off' 	,'param'=>1)

			,'FOLLOW'	=> array( 'func'=>'Lingo_Follow','param'=>1)
			,'LEAVE'	=> array( 'func'=>'Lingo_Leave' ,'param'=>1)

			,'ADD'		=> array( 'func'=>'Lingo_Add' 	,'param'=>1)
			,'DELETE'	=> array( 'func'=>'Lingo_Delete','param'=>1)

			,'GET'		=> array( 'func'=>'Lingo_Get' 	,'param'=>1)
			,'NUDGE'	=> array( 'func'=>'Lingo_Nudge' ,'param'=>1)
			,'WHOIS'	=> array( 'func'=>'Lingo_Whois' ,'param'=>1)

			,'ACCEPT'	=> array( 'func'=>'Lingo_Accept','param'=>1)
			,'DENY'		=> array( 'func'=>'Lingo_Deny' 	,'param'=>1)

			,'D'		=> array( 'func'=>'Lingo_D' 	,'param'=>999)

			,'REG'		=> array( 'func'=>'Lingo_Reg' 	,'param'=>2)

			,'WHOAMI'	=> array( 'func'=>'Lingo_Whoami','param'=>0)

			,'MMS'		=> array( 'func'=>'Lingo_Mms'	,'param'=>1)
		);


	/*
	 *	记录所有的机器人命令的alias
	 */
	static private $msRobotLingoAlias = array (
			 'KAI'		=>	'ON'			// alias of ON
			,'START'	=>	'ON'			// alias of ON
			,'WAKE'		=>	'ON'			// alias of ON

			,'GUAN'		=>	'OFF'			// alis of OFF
			,'STOP'		=>	'OFF'			// alis of OFF
			,'SLEEP'	=>	'OFF'			// alis of OFF

			,'INVITE'	=>	'ADD'

			,'NAO'		=>	'NUDGE'
			,'NAONAO'	=>	'NUDGE'
			,'NN'		=>	'NUDGE'

			,'REMOVE'	=>	'DELETE'

			,'ZHUCE'	=>	'REG'
			,'ZC'		=>	'REG'
			,'GM'		=>	'REG'
			,'GAIMING'	=>	'REG'

			,'M'		=>	'MMS'

			/*
		 	 * 	JiWai扩展
			 */
			,'WOSHISHUI'=>	'WHOAMI'
	);


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
	 *	判断一个 RobotMsg 是否为 Lingo Msg
	 *	@param	JWRobotMsg	$robotMsg
	 *	@return	array		Lingo Msg 的对应处理函数（通过call_user_func调用）
				false		不是 lingo Msg
	 */
	static public function GetLingoFunctionFromMsg($robotMsg)
	{
		if ( empty($robotMsg) )
			throw new JWException('null param?');

		$body = $robotMsg->GetBody();
		$serverAddress = $robotMsg->GetServerAddress();
		$type = $robotMsg->GetType();

		//get and set idUserConference [ only for SMS ]
		$idUserConference = self::GetLingoUser( $serverAddress, $type );
		$robotMsg->SetIdUserConference( $idUserConference );

		if( $body == '00000' || $body == '0000' || $type=='sms' ) {
			return $lingo_function = array('JWRobotLingo', 'Lingo_0000');
		}

		$body = self::ConvertCorner( $body );
		if ( ! preg_match('/^([[:alpha:]]+)\s*(\w*)/',$body,$matches) ) 
			return false;

		$lingo 	= strtoupper($matches[1]);
		$param	= $matches[2];

		$lingoPair = self::GetLingoPair( $idUserConference ) ;

		if ( isset( $lingoPair['alias'][$lingo] ) ) {
			// it's a lingo alias
			$lingo = $lingoPair['alias'][$lingo];

		} else if ( isset($lingoPair['lingo'][$lingo]) ) {
			// it's a lingo name, pass.
			;
		} else {
			// no such lingo
			return false;
		}

		$lingo_info	= $lingoPair['lingo'][$lingo] ;

		if ( empty($param) )
			$param_count = 0;
		else
			$param_count = count( preg_split('/\s+/',$param) );


	 	/* 	lingo_info[param] 设置这个命令接受的最多参数, 如果用户输入多于这个最大值，则不当作lingo处理。
		 * 	（如用户输入"on the way home"）
		 */

		if ( $param_count > $lingo_info['param'] )
			return false;

		$lingo_function = array('JWRobotLingo', $lingo_info['func']);

		if ( ! is_callable($lingo_function) )
		{
			JWLog::Log(LOG_ERR, "JWRobotLingo::GetLingoFunctionFromMsg found lingo[$lingo] is unimpl");
			return false;
		}

		return $lingo_function;
	}


	static function GetLingoPair( $idUserConference ) {

		$lingo = self::$msRobotLingo;
		$alias = self::$msRobotLingoAlias;

		switch( $idUserConference ) {
			case 99:
			{
				$lingo = array(
						'GM'	=> array( 'func'=>'Lingo_Reg', 'param'=>2, ),
						'WOSHISHUI'	=> array( 'func'=>'Lingo_Whoami', 'param'=>0, ),
					);
				$alias = array();
			}
			break;
			case 28006:
			{
				$lingo = array(
						'A'	=> array( 'func'=>'Lingo_Follow', 'param'=>1, ),
					);
				$alias = array();
			}
			break;
			default:
			{
			}
			break;
		}

		return array( 
				'lingo' => $lingo,
				'alias' => $alias,
			    );
	}

	static function GetLingoUser( $serverAddress , $type = 'sms' ){

		if( $type != 'sms' ) 
			return 0;

		if( isset( JWConference::$smsAlias[ $serverAddress ] ) ){
			$serverAddress = JWConference::$smsAlias[ $serverAddress ];
		}

		$userInfo = null;
		if( preg_match("/[0-9]{8}(99|1)(\d+)/", $serverAddress, $matches ) ) {
			$normalMeeting = $matches[1] == 99 ? true : false;
			$conference = null;
			if( $normalMeeting ){
				$userInfo = JWUser::GetUserInfo( $matches[2] );
				$conference = JWConference::GetDbRowFromUser( $matches[2] ) ;
			}else{
				$conference = JWConference::GetDbRowFromNumber( $matches[2] );
				if( !empty( $conference ) ){
					$userInfo = JWUser::GetUserInfo( $conference['id'] );
				}
			}
		}
		
		if( empty( $userInfo ) ){
			return 0;	
		} 
		
		return $userInfo['id'];
	}
	

	/**
	 * 将字符串转化为半角，从而支持半角指令
	 * @param string $string , 
	 * @return string
	 */
	static function ConvertCorner($string){
		$corner = array(
			'１' => '1', '２' => '2', '３' => '3', '４' => '4', '５' => '5',
			'６' => '6', '７' => '7', '８' => '8', '９' => '9', '０' => '0',
			'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd', 'ｅ' => 'e',
			'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i', 'ｊ' => 'j',
			'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n', 'ｏ' => 'o',
			'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's', 'ｔ' => 't',
			'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x', 'ｙ' => 'y',
			'ｚ' => 'z', 'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D',
			'Ｅ' => 'E', 'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I',
			'Ｊ' => 'J', 'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N',
			'Ｏ' => 'O', 'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S',
			'Ｔ' => 'T', 'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X',
			'Ｙ' => 'Y', 'Ｚ' => 'Z', '＠' => '@', '　' => ' '
	    	);
		return str_replace(array_keys($corner), array_values($corner), $string);
	}
}
?>
