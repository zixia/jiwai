<?php
require_once( '/opt/jiwai.de/jiwai.inc.php' );
$k = $v = null;
extract( $_REQUEST, EXTR_IF_EXISTS );

$ret = true;
switch( $k ) {
	case 'nameScreen':
		$ret = check_nameScreen($v);
	break;
	case  'email':
		$ret = check_email($v);
	break;
	case  'nameFull':
		$ret = check_nameFull($v);
	break;
	case 'url':
		$ret = check_url($v);
	break;
	case 'stockCategory':
		$ret = check_stockCategory($v);
	break;
}

if( $ret === true ) {
	exit(0);
}else{
	echo $ret;
}

function check_StockCategory($v){
	$stock_name = "stock_$v";

	if( false == preg_match( '/^stock_[0-9a-z]{3,8}$/i', $stock_name ) ){
		return "必须是 3 - 8 位字母数字";
	}

	$userInfo = JWUser::getUserInfo( $stock_name );

	return empty($userInfo) ? true : "$v 已经被使用";
}

function check_nameScreen($v){

	if( false == JWUser::IsValidName($v, false) ){
		if( preg_match('/^\d/', $v ) ) {
			return "用户名 $v 不能以数字开头";
		}else if( strlen( $v ) < 5 ) {
			return "用户名 $v 不能短于 5 个字符";
		}else{
			return "用户名 $v 含有非法字符";
		}
	}

	$idExist = intval( JWUser::IsExistName($v) );
	if( $idExist==0 || $idExist==JWLogin::GetCurrentUserId() ) {
		return true;
	}

	return "用户名：$v 已经被使用";
}

function check_nameFull($v){

	if( false == JWUser::IsValidFullName($v, false) ){
		if( preg_match('/^\d/', $v ) ) {
			return "姓名 $v 不能以数字开头";
		}else if( strlen( $v ) < 2  || strlen($v) > 40 ) {
			return "姓名 $v 长度必须在 2-40 字节之间";
		}else{
			return "姓名 $v 含有非法字符";
		}
	}

	return true;
}

function check_email($v){

	if( false == JWUser::IsValidEmail($v, false) ){
		return "Email地址：$v 不合法";
	}

	$idExist = intval( JWUser::IsExistEmail($v) );
	if( $idExist==0 || $idExist==JWLogin::GetCurrentUserId() ) {
		return true;
	}

	return "Email地址：$v 已经被使用";
}

function check_url($v){
	$info = @parse_url( $v );
	if( false == isset( $info['scheme'] ) 
			|| ( strtolower( $info['scheme'] ) != 'http'
				&& strtolower($info['scheme']) != 'https'
			)
		){
		return "$info[scheme]网址必须以http://或https://打头";
	}

	if( preg_match( '/^([\w\-]+)(\.[\w\-]+)*(\.[a-z]{2,})$/', @$info['host'] ) 
			|| preg_match('/^\d+\.\d+\.\d+\.\d+$/', @$info['host']) 
			){
		return true;
	}

	return "网址中的域名部分$info[host]不合法";
}
?>
