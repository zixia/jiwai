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
			 'HELP'		=> array('class'=>'JWRobotLingo', 'func'=>'Lingo_Help' 	,'param'=>1 )
			,'TIPS'		=> array('class'=>'JWRobotLingo', 'func'=>'Lingo_Tips' 	,'param'=>0 )

			,'ON'		=> array('class'=>'JWRobotLingo', 'func'=>'Lingo_On' 	,'param'=>1)
			,'OFF'		=> array('class'=>'JWRobotLingo', 'func'=>'Lingo_Off' 	,'param'=>1)

			,'FOLLOW'	=> array('class'=>'JWRobotLingo', 'func'=>'Lingo_Follow','param'=>10)
			,'LEAVE'	=> array('class'=>'JWRobotLingo', 'func'=>'Lingo_Leave' ,'param'=>10)

			,'ADD'		=> array('class'=>'JWRobotLingo', 'func'=>'Lingo_Add' 	,'param'=>1)
			,'DELETE'	=> array('class'=>'JWRobotLingo', 'func'=>'Lingo_Delete','param'=>1)

			,'GET'		=> array('class'=>'JWRobotLingo', 'func'=>'Lingo_Get' 	,'param'=>1)
			,'NUDGE'	=> array('class'=>'JWRobotLingo', 'func'=>'Lingo_Nudge' ,'param'=>1)
			,'WHOIS'	=> array('class'=>'JWRobotLingo', 'func'=>'Lingo_Whois' ,'param'=>1)

			,'ACCEPT'	=> array('class'=>'JWRobotLingo', 'func'=>'Lingo_Accept','param'=>1)
			,'DENY'		=> array('class'=>'JWRobotLingo', 'func'=>'Lingo_Deny' 	,'param'=>1)

			,'D'		=> array('class'=>'JWRobotLingo', 'func'=>'Lingo_D' 	,'param'=>999)

			,'REG'		=> array('class'=>'JWRobotLingo', 'func'=>'Lingo_Reg' 	,'param'=>2)

			,'WHOAMI'	=> array('class'=>'JWRobotLingo', 'func'=>'Lingo_Whoami','param'=>0)
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
		$address = $robotMsg->GetAddress();

		//get and set idUserConference [ only for SMS ]
		$idUserConference = self::GetLingoUser( $serverAddress, $address, $type );
		$robotMsg->SetIdUserConference( $idUserConference );

		if( ($body == '00000' || $body == '0000') && $type=='sms' ) 
		{
			return $lingo_function = array('JWRobotLingo', 'Lingo_0000');
		}

		$body = self::ConvertCorner( $body );
		if ( false == preg_match('/^([[:alpha:]]+)\s*(\w*)/',$body,$matches) ) 
		{
			return false;
		}

		$lingo 	= strtoupper($matches[1]);
		$param	= $matches[2];

		//##################################################
		///########### Begin Lingo Pair Fetch Logic ########
		//##################################################

		/**
		 * Get Lingo Pair [lingo and alias] From FuncCode
		 */
		$lingoPair = self::GetLingoPairFromFuncCode( $serverAddress, $address, $type );

		/**
		 * Get Lingo Pair [lingo and alias] From IdConference
		 */
		$lingoPair = empty($lingoPair) ? self::GetLingoPair( $idUserConference ) : $lingoPair;
		
		//##################################################
		//########### End Lingo Pair Fetch Logic ###########
		//##################################################

		if ( isset( $lingoPair['alias'][$lingo] ) ) 
		{
			$lingo = $lingoPair['alias'][$lingo];
		} 
		else if ( isset($lingoPair['lingo'][$lingo]) ) 
		{
			;
		}
	       	else 
		{
			return false;
		}

		$lingo_info = $lingoPair['lingo'][$lingo] ;
		$param_count = empty($param) ? 0 : count( preg_split('/\s+/',$param) );

	 	/**
	  	 * lingo_info[param] 设置这个命令接受的最多参数
		 * 如果用户输入多于这个最大值，则不当作lingo处理。
		 * （如用户输入"on the way home"）
		 */
		if ( $param_count > $lingo_info['param'] )
		{
			return false;
		}

		$lingo_function = array($lingo_info['class'], $lingo_info['func']);

		if ( false == is_callable($lingo_function) )
		{
			JWLog::Log(LOG_ERR, "JWRobotLingoBase::GetLingoFunctionFromMsg found lingo[$lingo] is unimpl");
			return false;
		} 
		return $lingo_function;
	}
	
	/**
	 * GetLingo from funcode
	 */
	static function GetLingoPairFromFuncCode($serverAddress, $mobileNo, $type='sms'){

		if( $type != 'sms' )
			return array();

		$preAndId = JWFuncCode::FetchPreAndId( $serverAddress, $mobileNo );
		if( empty($preAndId) )
			return array();

		$lingo = self::$msRobotLingo;
		$alias = self::$msRobotLingoAlias;
		switch( $preAndId['pre'] ) {
			case JWFuncCode::PRE_REG_INVITE:
			{
				$lingo['F'] =  array(
						'class'=>'JWRobotLingo_Add',
						'func'=>'Lingo_F',
						'param'=>1,
					);
				return array(
						'alias' => $alias,
						'lingo' => $lingo,
					);
			}
			break;
			case JWFuncCode::PRE_MMS_NOTIFY:
			{
				$lingo['DM'] = array(
						'class'=>'JWRobotLingo_Add',
						'func'=>'Lingo_DM',
						'param'=>0,
					);
				return array(
						'alias' => $alias,
						'lingo' => $lingo,
					);
			}
			break;
			case JWFuncCode::PRE_STOCK_CATE:
			case JWFuncCode::PRE_STOCK_CODE:
			{
				$lingo['ZX'] = array(
						'class'=>'JWRobotLingo_Stock',
						'func'=>'Lingo_ZX',
						'param'=>2,
					);
				$alias['ZC'] = 'ZX';
				$alias['F'] = 'FOLLOW';
				$alias['L'] = 'LEAVE';
				return array(
						'alias' => $alias,
						'lingo' => $lingo,
					);
			}
			break;
		}
		return array();
	}

	/**
	 * GetLingo from idConference;
	 */
	static function GetLingoPair( $idUserConference ) {

		$lingo = self::$msRobotLingo;
		$alias = self::$msRobotLingoAlias;

		switch( $idUserConference ) {
			case 99:
			{
				$lingo = array(
					'GM' => array(
						'class'=>'JWRobotLingo',
						'func'=>'Lingo_Reg',
						'param'=>2,
					),
					'WOSHISHUI' => array(
						'class'=>'JWRobotLingo',
						'func'=>'Lingo_Whoami',
						'param'=>0,
					),
				);
				$alias = array();
			}
			break;
			case 28006:
			{
				$lingo = array(
					'A' => array(
						'class'=>'JWRobotLingo',
						'func'=>'Lingo_Follow',
						'param'=>0,
					),
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

	static function GetLingoUser( $serverAddress, $address, $type = 'sms' ){

		if( $type != 'sms' )
			return 0;

		$f = func_get_args();
		$parseInfo = JWFuncCode::FetchConference( $serverAddress, $address );
		if( empty( $parseInfo ) )
			return 0;

		return $parseInfo['user']['id'];
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
