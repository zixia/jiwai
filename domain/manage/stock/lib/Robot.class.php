<?php
class StockCmdRobot{

	/**
	 *
	 */

	static private $lingo = array(
		'CREATE', 	//CREATE 600000 浦发银行, CREATE 123 XXXXX 
		'DELETE', 	//DELETE 600000 | DELETE 123

		'ALTER', 	//ALTER 600000
		'SET',	 	//ALTER 600000

		'CATE', 	//显示 已注册的所有分类
		'STOCK',	//显示 已注册的所有股票

		'LIST',		//显示从属关系 -- 如 LIST 600000 | LIST 200

		'SUBCREATE', 	//建立级联关系 -- 用法 SUBCREATE 123 600000 6000001 | SUBCREATE 600000 123 145 200
		'SUBDELETE', 	//删除级联关系 -- 用法 SUBDELETE 123 600000 6000001 | SUBDELETE 600000 123 456 222

		'INFO', 	//-- 用法 INFO 600000 想说的话
		'SHOW', 	//-- 用法 SHOW 600000 , SHOW 123
		'HELP', 	//-- 显示本帮助信息
	);

	static private $alias = array(
		'C' => 'CREATE',
		'D' => 'DELETE',

		'A' => 'ALTER',
		'S' => 'SET',
		
		'L' => 'LIST',
		'SC' => 'SUBCREATE',
		'SD' => 'SUBDELETE',

		'WEB' => 'INFO',
		'IM' => 'INFO',

		'H' => 'HELP',
	);

	static public function Execute($cmd){

		$cmd = trim( $cmd );

		if( false == preg_match('/^\s*([[:alpha:]]+)\s*(\S*)\s*/', $cmd, $matches ) ){
			return false;
		}

		$lingo = strtoupper( $matches[1] );
		if(false == in_array( $lingo, self::$lingo ) ){
			if( false == isset( self::$alias[ $lingo ] ) ){
				return false;
			}
			$lingo = self::$alias[ $lingo ];
		}

		$func = array( 'StockCmdRobot', 'Lingo_'.$lingo );
		if( false == is_callable( $func ) )
			return false;

		return call_user_func_array($func, $cmd);
	}

	static public function Lingo_CREATE($cmd) {
		if( preg_match( '/^\w+\s+(\d{3}|\d{6})\s+(\S+)$/i', $cmd, $matches ) ) {
			
			$number = $matches[1];
			$isStock = $number > 999 ;
			$nameScreen = 'gp'. $number;
			$nameFull = $matches[2];

			$userInfo = JWUser::GetUserInfo( $nameScreen );

			if( false == empty($userInfo) ){
				return  ( $isStock ? "股票" : "分类号" ) ."(${number}) 已登记为：$userInfo[nameFull]，修改请使用ALTER指令";
			}

			$uArray = array(
				'nameScreen' => $nameScreen,
				'nameFull' => $nameFull,
				'pass' => JWDevice::GenSecret(8),
			);

			if( $idUser = JWUser::Create($uArray) ){
				$type =  $isStock ?  JWComStockAccount::T_STOCK : JWComStockAccount::T_CATE;
				JWComStockAccount::Set( $idUser, $type );

				$options = array(
					'filter' => 'N',
					'notify' => 'N',
				);
				if ( $idConference = JWConference::Create($idUser, $options ) ) {
					JWUser::SetConference( $idUser, $idConference );
				}

				return  ( $isStock ? "股票" : "分类号" ) ."(${number}) 新登记为：$nameFull";
			}

		}else{
			return 'CREATE 命令后带2个参数 3位分类号|6位股票号 中文名称\n';
		}
	}

	static public function Lingo_ALTER($cmd) {
		if( preg_match( '/^\w+\s+(\d{3}|\d{6})\s*$/i', $cmd, $matches ) ) {
			
			$number = $matches[1];
			$isStock = $number > 999 ;
			$nameScreen = 'gp'. $number;
			$nameFull = $matches[2];

			$userInfo = JWUser::GetUserInfo( $nameScreen );

			if( empty($userInfo) ){
				return  ( $isStock ? "股票" : "分类号" ) ."(${number}) 尚未登记，请使用CREATE命令登记";
			}

			$uArray = array(
				'nameFull' => $nameFull,
			);

			JWLogin::Login( $userInfo['idUser'] );
			return '+/profile.php';

		}else{
			return 'ALTER 命令后带 1 个参数 3位分类号|6位股票号\n';
		}
	}

	static public function Lingo_SET($cmd) {
		if( preg_match( '/^\w+\s+(\d{3}|\d{6})\s*$/i', $cmd, $matches ) ) {
			
			$number = $matches[1];
			$isStock = $number > 999 ;
			$nameScreen = 'gp'. $number;
			$nameFull = $matches[2];

			$userInfo = JWUser::GetUserInfo( $nameScreen );

			if( empty($userInfo) ){
				return  ( $isStock ? "股票" : "分类号" ) ."(${number}) 尚未登记，请使用CREATE命令登记";
			}

			$uArray = array(
				'nameFull' => $nameFull,
			);

			JWLogin::Login( $userInfo['idUser'] );
			return '+/meeting.php';

		}else{
			return 'SET 命令后带 1 个参数 3位分类号|6位股票号\n';
		}
	}

	static public function Lingo_ALTER_OLD($cmd) {
		if( preg_match( '/^\w+\s+(\d{3}|\d{6})\s+(\S+)$/i', $cmd, $matches ) ) {
			
			$number = $matches[1];
			$isStock = $number > 999 ;
			$nameScreen = 'gp'. $number;
			$nameFull = $matches[2];

			$userInfo = JWUser::GetUserInfo( $nameScreen );

			if( empty($userInfo) ){
				return  ( $isStock ? "股票" : "分类号" ) ."(${number}) 尚未登记，请使用CREATE命令登记";
			}

			$uArray = array(
				'nameFull' => $nameFull,
			);

			JWDB::UpdateTableRow( 'User', $userInfo['id'], $uArray );

			return  ( $isStock ? "股票" : "分类号" ) ."(${number}) 已重新登记为：$nameFull";
		}else{
			return 'ALTER 命令后带2个参数 3位分类号|6位股票号 中文名称\n';
		}
	}

	static public function Lingo_Stock($cmd){
		$ids = JWComStockAccount::GetIdUsersByType( JWComStockAccount::T_STOCK );
		if( empty( $ids ) ){
			return "系统中还没有登记股票账户";
		}
		$userRows = JWUser::GetDbRowsByIds( $ids );
		return self::_ListAccount( $userRows );
	}
	static public function Lingo_Cate($cmd){
		$ids = JWComStockAccount::GetIdUsersByType( JWComStockAccount::T_CATE );
		if( empty( $ids ) ){
			return "系统中还没有登记分类账户";
		}
		$userRows = JWUser::GetDbRowsByIds( $ids );
		return self::_ListAccount( $userRows );
	}

	static public function _ListAccount( $userRows ){

		$rtn = array( );
		foreach( $userRows as $r ) {
			$gpId = ltrim( $r['nameScreen'], 'gp' );
			$rtn[ $gpId ] = $r;
		}
		ksort( $rtn );
		
		$stop = 2;
		$return = null;
		$i = 0;
		foreach( $rtn as $k=>$g ) {
			if( $i && $i%2 == 0 ) {
				$return .= "\n";
			}
			$return .= sprintf("%-50s", "($k) $g[nameFull]");
			$i++;
		}

		return $return;
	}

	static public function Lingo_SubCreate($cmd){
		$param_array = preg_split('/\s+/', $cmd );
		if( count( $param_array ) < 3 ) {
			return "SUBCREATE 后至少应该有 2 个参数。";
		}
		
		$cmd = array_shift($param_array);
		$me = array_shift($param_array);

		if( false == preg_match( '/^(\d{3}|\d{6})$/', $me ) ){
			return "SUBCREATE 后第一个参数只能是：3位数字的分类号、6位数字的股票代码。";
		}
		
		$isStock = ( $me > 1000 ) ? true : false;
		$pattern = $isStock ? '/^\d{3}$/' : '/^\d{6}$/';

		$param_array = array_unique( $param_array );

		$meUser = JWUser::GetUserInfo( 'gp' . $me );
		if( empty( $meUser ) ){
			return "系统中还未登记". ( $isStock ? '股票代码' : '分类代码' ) . ': '.$me;
		}

		$sucArray = array();
		foreach( $param_array as $object ) {
			if( false == preg_match( $pattern, $object ) ) 
				continue;

			$oUser = JWUser::GetUserInfo( 'gp' . $object );
			if( $oUser ) {

				if( $isStock ) {
					JWFollowRecursion::Create( $meUser['id'], $oUser['id'] );
				}else{
					JWFollowRecursion::Create( $oUser['id'], $meUser['id'] );
				}
				array_push( $sucArray, $oUser );
			}
		}

		if( empty( $sucArray ) ) {
			return 'SUBCREATE 后的参数都不是有效的'. ( $isStock ? '股票代码' : '分类代码' );
		}

		$partResult = self::_ListAccount( $sucArray ) ;

		return "已经为" . "【(${me})$meUser[nameFull]】" . "成功建立了" 
			. ( $isStock ? '上级' : '下级' ) . "关联关系：\n\n$partResult";

	}

	static public function Lingo_SubDelete($cmd){
		$param_array = preg_split('/\s+/', $cmd );
		if( count( $param_array ) < 3 ) {
			return "SUBDELETE 后至少应该有 2 个参数。";
		}
		
		$cmd = array_shift($param_array);
		$me = array_shift($param_array);

		if( false == preg_match( '/^(\d{3}|\d{6})$/', $me ) ){
			return "SUBDELETE 后第一个参数只能是：3位数字的分类号、6位数字的股票代码。";
		}

		$isStock = ( $me > 1000 ) ? true : false;
		$pattern = $isStock ? '/^\d{3}$/' : '/^\d{6}$/';

		$param_array = array_unique( $param_array );
		
		//获取操作对象
		$meUser = JWUser::GetUserInfo( 'gp' . $me );
		if( empty( $meUser ) ){
			return "系统中还未登记". ( $isStock ? '股票代码' : '分类代码' ) . ': '.$me;
		}
		
		//取消所有订阅关系
		if( in_array( '0', $param_array ) ){

			$userIds = JWFollowRecursion::GetSuperior( $meUser['id'], 1, $isStock );
			$userIds = array_diff( $userIds, array( $meUser['id'] )) ;
			$param_array = JWUser::GetDbRowsByIds( $userIds );
			if( empty( $param_array ) ) {
				return "【(${me})$meUser[nameFull]】" . "不存在级联关系";
			}
		}


		$sucArray = array();
		foreach( $param_array as $object ) {
			
			if( is_array( $object ) ) {
				$oUser = $object;
			}else{
				if( false == preg_match( $pattern, $object ) ) 
					continue;
				$oUser = JWUser::GetUserInfo( 'gp' . $object );
			}

			if( $oUser ) {

				if( $isStock ) {
					JWFollowRecursion::Destroy( $meUser['id'], $oUser['id'] );
				}else{
					JWFollowRecursion::Destroy( $oUser['id'], $meUser['id'] );
				}
				array_push( $sucArray, $oUser );
			}
		}

		if( empty( $sucArray ) ) {
			return 'SUBDELETE 后的参数都不是有效的'. ( $isStock ? '股票代码' : '分类代码' );
		}

		$partResult = self::_ListAccount( $sucArray ) ;

		return "已经为" . "【(${me})$meUser[nameFull]】" . "成功取消了" 
			. ( $isStock ? '上级' : '下级' ) . "关联关系：\n\n$partResult";

	}

	static public function Lingo_List($cmd){

		$param_array = preg_split( '/\s+/', $cmd );
		if( count( $param_array ) == 1 )
			return "LIST 参数后必须带 1 个参数：3位分类代码、6位股票代码";

		$cmd = array_shift($param_array);
		$me = array_shift($param_array);

		if( false == preg_match( '/^(\d{3}|\d{6})$/', $me ) ){
			return "LIST 后第一个参数只能是：3位数字的分类号、6位数字的股票代码。";
		}
		
		$isStock = ( $me > 1000 ) ? true : false;

		//获取操作对象
		$meUser = JWUser::GetUserInfo( 'gp' . $me );
		if( empty( $meUser ) ){
			return "系统中还未登记". ( $isStock ? '股票代码' : '分类代码' ) . ': '.$me;
		}

		$userIds = JWFollowRecursion::GetSuperior( $meUser['id'], 1, $isStock );
		$userIds = array_diff( $userIds, array( $meUser['id'] ) );

		if( empty( $userIds ) ) {
			return "【(${me})$meUser[nameFull]】" . "不存在级联关系";
		}

		$userRows = JWUser::GetDbRowsByIds( $userIds );

		$partResult = self::_ListAccount( $userRows ) ;
		
		return "【(${me})$meUser[nameFull]】" . ( $isStock ? '上级' : '下级' ) . "关联关系：\n\n$partResult";
	}

	static public function Lingo_Info($cmd) {
		$param_array = preg_split( '/\s+/', $cmd, 3 );

		if( count( $param_array ) != 3  )
			return "用法：". strtoupper($param_array[0]). " 代码 想说的话。";

		$cmd = strtoupper( array_shift($param_array) );
		$me = array_shift($param_array);
		if( false == preg_match( '/^(\d{3}|\d{6})$/', $me ) ){
			return "$cmd 后第一个参数只能是：3位数字的分类号、6位数字的股票代码。";
		}
		$isStock = ( $me > 1000 ) ? true : false;

		//获取操作对象
		$meUser = JWUser::GetUserInfo( 'gp' . $me );
		if( empty( $meUser ) ){
			return "系统中还未登记". ( $isStock ? '股票代码' : '分类代码' ) . ': '.$me;
		}

		$text = trim( array_shift($param_array) );
		if( null == $text ) {
			return "$cmd 命令提示你：总得说点什么吧？";
		}

		$to = ( strtoupper($cmd) =='INFO' ) ? 'ALL' : strtoupper( $cmd );
		//冒充INFO说话；
		$options = array(
			'idConference' => $meUser['idConference'],
			'filterConference' => false,
			'notify' => $to,
		);
		
		if( $idStatus = JWSns::UpdateStatus( $meUser['id'], $text, 'web', null, 'N', 'web', $options ) ) {
			return "发布股市信息成功，更新的编号：$idStatus , 通知到：$to";
		}

		return "发布消息失败，请联系管理员 GTalk: shwdai@gmail.com , MSN: shwdai@msn.com";
	}

	static public function Lingo_Show($cmd){
		$param_array = preg_split( '/\s+/', $cmd );
		if( count( $param_array ) < 2  )
			return "SHOW 用法：SHOW 3位分类代码或6位股票代码";

		$cmd = array_shift($param_array);
		$me = array_shift($param_array);
		if( false == preg_match( '/^(\d{3}|\d{6})$/', $me ) ){
			return "SHOW 后第一个参数只能是：3位数字的分类号、6位数字的股票代码。";
		}
		$isStock = ( $me > 1000 ) ? true : false;

		//获取操作对象
		$meUser = JWUser::GetUserInfo( 'gp' . $me );
		if( empty( $meUser ) ){
			return "系统中还未登记". ( $isStock ? '股票代码' : '分类代码' ) . ': '.$me;
		}

		$type = $isStock ? '股票' : '分类';
		$numFollowers = JWFollower::GetFollowerNum( $meUser['id'] );
		$numConference = $meUser['idConference'] ? 
				JWStatus::GetStatusNumFromConference($meUser['idConference']) : 0; 
		return <<<_RTN_
代码：	$me
名称：	$meUser[nameFull]
类型：	$type
订阅：	$numFollowers (人)
信息： 	$numConference (条)
_RTN_;
	}

	static public function Lingo_Delete($cmd) {
		return "DELETE 命令暂时不开放";
	}

	static public function Lingo_HELP($cmd){
		$reply = array();

		$reply['HELP']  = <<<_HELP_
支持的命令有：( 命令不区分大小写 )

	'CREATE', 	# 建立股票帐户 或 建立分类帐户
	'ALTER', 	# 修改股票账户 或 分类账户的基本属性；
	'SET',		# 设置股票账户 或 分类账户的会议模式属性；
	'CATE', 	# 显示已登记的所有分类账户
	'STOCK',	# 显示已登记的所有股票账户
	'LIST',		# 显示股票用户与分类用户的级联关系
	'SUBCREATE', 	# 建立股票用户与分类用户的级联关系
	'SUBDELETE', 	# 删除股票用户于分类用户的级联关系
	'INFO', 	# 使用某账户发信息
	'SHOW', 	# 显示某账户的详细信息
	'HELP', 	# 显示本帮助
	'CLEAR',	# 清除控制台信息

具体命令用法请执行： HELP + 命令名称
其他帮助内容：[代码]
_HELP_;

		$reply['CREATE'] = <<<_HELP_
[CREATE]
	格式：CREATE 代码 用户名称
	作用：用来新建股票账户或分类账户。
	注意：如果系统已注册相同代码，则本命令什么也不做，如需修改，请使用 ALTER 命令。
	缩写：C
_HELP_;
		
		$reply['代码'] = <<<_HELP_
代码：
	1、系统中代码被分为两类
		a、第一类为 6 位数字的股票代码，如：600000；
		b、第二类为 3 位数字的分类代码，如：123，分类代码的由管理员自由分配，无确定规则；
	2、代码为纯数字，3位 或 6 位，其他不合法，如 000 不能等同与 0；
_HELP_;
		
		$reply['ALTER'] = <<<_HELP_
[ALTER]
	格式：ALTER 代码
	作用：用来改变已登记代码的账户详细资料：名称、邮件地址、网址、头像、介绍等。
	注意：
		如果该代码未被登记，请使用 CREATE 命令。
		如果该命令会弹出一个模式对话框，如被拦截，请设置浏览器不拦击本域的弹窗。
	缩写：A
_HELP_;

		$reply['SET'] = <<<_HELP_
[SET]
	格式：SET 代码
	作用：用来改变已登记代码的账户的会议属性：参与限制、信息过滤规则等。
	注意：
		如果该代码未被登记，请使用 CREATE 命令。
		如果该命令会弹出一个模式对话框，如被拦截，请设置浏览器不拦击本域的弹窗。
	缩写：S
_HELP_;

		$reply['CATE'] = <<<_HELP_
[CATE]
	格式：CATE
	作用：列出系统中已登记的所有股票分类。
_HELP_;
		$reply['STOCK'] = <<<_HELP_
[STOCK]
	格式：STOCK
	作用：列出系统中已登记的所有股票分类。
_HELP_;
		$reply['LIST'] = <<<_HELP_
[LIST]
	格式：LIST 代码
	作用：列出该代码对应账户的级联关系。
	缩写：L
_HELP_;
		$reply['SUBCREATE'] = <<<_HELP_
[SUBCREATE]
	格式：SUBCREATE 代码0 代码1 [代码2 ...]
	作用：建立 代码0 同 （代码1 [代码2 ...]）之间的级联关系
	注意：
		如果 代码0 为分类代码，则后续代码必须为的股票代码。
		如果 代码0 为股票代码，则后续代码必须为的分类代码。
		本命令 至少需要 2 个参数，[]号内为可选。
	缩写：SC
_HELP_;
		$reply['SUBDELETE'] = <<<_HELP_
[SUBDELETE]
	格式：SUBCREATE 代码0 代码1 [代码2 ...]
	作用：销毁 代码0 同 （代码1 [代码2 ...]）之间的级联关系
	注意：
		如果 代码0 为分类代码，则后续代码必须为的股票代码。
		如果 代码0 为股票代码，则后续代码必须为的分类代码。
		本命令 至少需要 2 个参数，[]号内为可选。
		如果 代码1 或 后续的 代码 中有 0 作用参数，则认为销毁 代码0 对应账户所有级联关系。
	缩写：SD
_HELP_;
		$reply['SHOW'] = <<<_HELP_
[SHOW]
	格式：SHOW 代码
	作用：显示 代码 对应账户的信息：订阅人数，会议消息数等；
_HELP_;
		$reply['INFO'] = <<<_HELP_
[INFO]
	格式：INFO 代码 待发布的信息
	作用：使用 代码 对应账户发布股市信息，并进行通知。
	注意：
		使用 IM 命令发布信息时，默认信息只会通知到IM设备上，不会通知到SMS。
		使用 WEB 命令时，信息只出现在WEB，不发出通知；INFO 命令则通知所有设备。
	缩写：IM WEB
_HELP_;

		$param_array = preg_split('/\s+/', $cmd );
		if( count( $param_array )  == 1 )
			return $reply['HELP'];

		$subject = strtoupper( $param_array[1] );

		if( isset( $reply[ $subject ] ) )
			return $reply[ $subject ];

		return $reply[ 'HELP' ];
	}
}
?>
