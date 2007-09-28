<?php
class StockCmdRobot{

	/**
	 *
	 */

	static private $lingo = array(
		'CREATE', 	//CREATE 600000 浦发银行, CREATE 123 XXXXX 
		'DELETE', 	//DELETE 600000 | DELETE 123
		'ALTER', 	//ALTER 600000 大大, ALTER 123 ABC

		'LISTCATE', 	//显示 已注册的所有分类
		'LISTSTOCK',	//显示 已注册的所有股票

		'LISTSUP',	//显示 某股票的上级用户，如 LISTSUP 600000
		'LISTSUB',	//显示 某分类的股票 如 LISTSUB 123

		'SUBTO', 	//建立级联关系 -- 用法 SUBTO 600000 123 100 200 400 500 600
		'UNSUB', 	//删除级联关系 -- 用法 UNSUB 600000 123 100 200 400 500 600

		'MKSUB', 	//建立级联关系 -- 用法 MKSUB 123 600000 6000001 6000
		'RMSUB', 	//删除级联关系 -- 用法 RMSUB 123 600000 6000001 6000

		'INFO', 	//-- 用法 INFO 600000 想说的话
		'SHOW', 	//-- 用法 SHOW 600000 , SHOW 123

		'HELP', 	//-- 显示本帮助信息
	);
	static public function Execute($cmd){

		$cmd = trim( $cmd );

		if( false == preg_match('/^\s*([[:alpha:]]+)\s*(\S*)\s*/', $cmd, $matches ) ){
			return false;
		}

		$lingo = strtoupper( $matches[1] );
		if(false == in_array( $lingo, self::$lingo ) ){
			return false;
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
				return  ( $isStock ? "股票" : "分类号" ) ."(${number}) 新登记为：$nameFull";
			}

		}else{
			return 'CREATE 命令后带2个参数 3位分类号|6位股票号 中文名称\n';
		}
	}

	static public function Lingo_ALTER($cmd) {
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
			return 'CREATE 命令后带2个参数 3位分类号|6位股票号 中文名称\n';
		}
	}

	static public function Lingo_ListStock($cmd){
		return self::_List( JWComStockAccount::T_STOCK );
	}
	static public function Lingo_ListCate($cmd){
		return self::_List( JWComStockAccount::T_CATE );
	}

	static public function _List( $type ){

		$ids = JWComStockAccount::GetIdUsersByType( $type );
		$userRows = JWUser::GetDbRowsByIds( $ids );
		
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

	static public function Lingo_HELP($cmd){
		return <<<_HELP_
'CREATE', 	//CREATE 600000 浦发银行, CREATE 123 XXXXX 
'DELETE', 	//DELETE 600000 | DELETE 123
'ALTER', 	//ALTER 600000 大大, ALTER 123 ABC

'LISTCATE', 	//显示 已注册的所有分类
'LISTSTOCK',	//显示 已注册的所有股票

'LISTSUP',	//显示 某股票的上级用户，如 LISTSUP 600000
'LISTSUB',	//显示 某分类的股票 如 LISTSUB 123

'SUBTO', 	//建立级联关系 -- 用法 SUBTO 600000 123 100 200 400 500 600
'UNSUB', 	//删除级联关系 -- 用法 UNSUB 600000 123 100 200 400 500 600
'MKSUB', 	//建立级联关系 -- 用法 MKSUB 123 600000 6000001 6000
'RMSUB', 	//删除级联关系 -- 用法 RMSUB 123 600000 6000001 6000

'INFO', 	//-- 用法 INFO 600000 想说的话
'SHOW', 	//-- 用法 SHOW 600000 , SHOW 123
'HELP', 	//-- 显示本帮助信息
_HELP_;
	}
}
?>
