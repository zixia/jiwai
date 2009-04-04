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
	 *	param 设置这个命令接受的最多参数。如果用户输入多于这个最大值，则不当作lingo处理。
	 *	（如用户输入"on the way home"）
	 */
	static private $msRobotLingo = array (
			'HELP' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_Help', 'param'=>1 ),
			'TIPS' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_Tips', 'param'=>0 ),

			'ADD' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_Add', 'param'=>1 ),

			'ON' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_On', 'param'=>1),
			'OFF' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_Off', 'param'=>1),

			'FOLLOW' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_Follow', 'param'=>10),
			'LEAVE' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_Leave', 'param'=>10),

			'NOTICE' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_NOTICE', 'param'=>1),

			'GET' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_Get', 'param'=>1),
			'NUDGE' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_Nudge', 'param'=>1),
			'WHOIS' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_Whois', 'param'=>1),

			'ACCEPT' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_Accept', 'param'=>1),
			'DENY' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_Deny', 'param'=>1),
			'CANCEL' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_Cancel', 'param'=>1),

			'D' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_D', 'param'=>999), 

			'REG' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_Reg', 'param'=>2),
			'WHOAMI' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_Whoami', 'param'=>0),

			//Track
			'TRACK' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_Track', 'param'=>6),
			'UNTRACK' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_UnTrack', 'param'=>6),

			//Block
			'BLOCK' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_Block', 'param'=>999),
			'UNBLOCK' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_UnBlock', 'param'=>999),

			//PASS
			'PASS'	=> array('class'=>'JWRobotLingo', 'func'=>'Lingo_Pass', 'param'=>1 ),

			//Merge
			'MERGE' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_Merge', 'param'=>2 ),

			//VOTE
			'VOTE' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_Vote', 'param'=>2),

			//DICT
			'DICT' => array('class'=>'JWRobotLingo', 'func'=>'Lingo_Dict', 'param'=>1),

			//DD Read the first unread message
			'DD' => array('class' => 'JWRobotLingo', 'func'=>'Lingo_DD', 'param' => 0),

			//Lingo For SP
			'A' => array('class' => 'JWRobotLingo_SP', 'func'=>'Lingo_A', 'param' => 0),
			);


	/*
	 *	记录所有的机器人命令的alias
	 */
	static private $msRobotLingoAlias = array (
			//Alias of HELP/TIPS
			'H' => 'HELP',
			'T' => 'TIPS',

			//Alias of ON
			'KAI' => 'ON',
			'START' => 'ON',
			'WAKE' => 'ON',
			'K' => 'ON',

			//Alias of OFF
			'GUAN' => 'OFF', 
			'STOP' => 'OFF',
			'SLEEP' => 'OFF',
			'G' => 'OFF',

			'INVITE' => 'ADD',
			'NAO' => 'NUDGE',
			'NAONAO' => 'NUDGE',
			'NN' => 'NUDGE',

			'REMOVE' => 'LEAVE',
			'DELETE' => 'LEAVE',

			'ZHUCE' => 'REG',
			'ZC' => 'REG',
			'GM' => 'REG',
			'GAIMING' => 'REG',

			//Follow
			'F' => 'FOLLOW',

			//Extension 
			'WOSHISHUI' => 'WHOAMI',
			'WOSHISHEI' => 'WHOAMI',

			//Track|Trace
			'TRACKS' => 'TRACK',
			'TRACE' => 'TRACK',
			'UNTRACE' => 'UNTRACK',
			'UNTRACKS' => 'UNTRACK',

			//Passwrod
			'PASSWORD' => 'PASS',
			'PWD' => 'PASS',
			'PW' => 'PASS',
			'PASSWD' => 'PASS',
			'MIMA' => 'PASS',

			//MerGE
			'HEBING' => 'MERGE',

			//VOTE
			'TP' => 'VOTE',

			//Message
			'!' => 'D',
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
	 *	@param	bool $intercept
	 *	@return	array		Lingo Msg 的对应处理函数（通过call_user_func调用）
	 false		不是 lingo Msg
	 */
	static public function GetLingoFunctionFromMsg($robot_msg)
	{
		if ( empty($robot_msg) )
			throw new JWException('null param?');

		if ( $robot_msg->GetIsInterceptable() ) 
		{
			/** 拦击 FOLLOW | F | LEAVE | L | DELETE **/
			JWRobotLingoIntercept::Intercept_FollowOrLeave($robot_msg);

			/** 拦击冻灾 */
			JWRobotLingoIntercept::Intercept_TagDongZai($robot_msg);

			JWRobotLingoIntercept::Intercept_PreAndId($robot_msg);
		}

		$body = $robot_msg->GetBody();
		$server_address = $robot_msg->GetHeader('serveraddress');
		$type = $robot_msg->GetType();
		$address = $robot_msg->GetAddress();

		//get and set idUserConference [ only for SMS ]
		$conference_id = self::GetLingoConferenceId( $server_address, $address, $type );

		if( ($body == '00000' || $body == '0000') && $type=='sms' ) 
		{
			return $lingo_function = array('JWRobotLingo', 'Lingo_0000');
		}

		$body = JWTextFormat::ConvertCorner( $body );
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
		$lingo_pair = self::GetLingoPairFromFuncCode( $server_address, $address, $type );

		/**
		 * Get Lingo Pair [lingo and alias] From IdConference
		 */
		$lingo_pair = empty($lingoPair) ? self::GetLingoPair( $conference_id ) : $lingo_pair;

		//##################################################
		//########### End Lingo Pair Fetch Logic ###########
		//##################################################

		if ( isset( $lingo_pair['alias'][$lingo] ) ) 
		{
			$lingo = $lingo_pair['alias'][$lingo];
		} 
		else if ( isset($lingo_pair['lingo'][$lingo]) ) 
		{
			;
		}
		else 
		{
			return false;
		}

		$lingo_info = $lingo_pair['lingo'][$lingo] ;
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
	static function GetLingoPairFromFuncCode($server_address, $mobile_no, $type='sms')
	{

		if( $type != 'sms' )
			return array();

		$pre_and_id = JWFuncCode::FetchPreAndId( $server_address, $mobile_no );
		if( empty($pre_and_id) )
			return array();

		$lingo = self::$msRobotLingo;
		$alias = self::$msRobotLingoAlias;
		switch ($pre_and_id['pre'])
		{
			case JWFuncCode::PRE_REG_INVITE:
			case JWFuncCode::PRE_REG_INVITE_13:
			case JWFuncCode::PRE_REG_INVITE_15:
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
	static function GetLingoPair( $conference_id ) 
	{

		$lingo = self::$msRobotLingo;
		$alias = self::$msRobotLingoAlias;

		switch ($conference_id)
		{
			case 5: //Wu Ye Xin Yu
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
			case 9:  //Qin Zi Gang Wan
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

	static function GetLingoConferenceId( $server_address, $address, $type = 'sms' )
	{

		if( $type != 'sms' )
			return 0;

		$f = func_get_args();
		$parse_info = JWFuncCode::FetchConference( $server_address, $address );
		if( empty( $parse_info ) )
			return 0;

		return $parse_info['conference']['id'];
	}

}
?>
