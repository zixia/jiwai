<?php
require_once( '../../../jiwai.inc.php' );
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
}

if( $ret === true ) {
	exit(0);
}else{
	echo $ret;
}

function check_nameScreen($v){

	if( false == JWUser::IsValidName($v, false) ){
		if( preg_match('/^\d/', $v ) ) {
			return "你选用的用户名 $v 不能以数字开头";
		}else if( strlen( $v ) < 5 ) {
			return "你选用的用户名 $v 不能短于 5 个字符";
		}else{
			return "你选用的用户名 $v 含有非法字符";
		}
	}

	$idExist = intval( JWUser::IsExistName($v) );
	if( $idExist==0 || $idExist==JWLogin::GetCurrentUserId() ) {
		return true;
	}

	return "你选用的用户名：$v 已经被使用";
}

function check_nameFull($v){

	if( false == JWUser::IsValidFullName($v, false) ){
		if( preg_match('/^\d/', $v ) ) {
			return "你的姓名 $v 不能以数字开头";
		}else if( strlen( $v ) < 2  || strlen($v) > 40 ) {
			return "你选用的用户名 $v 长度必须在 2-40 字节之间";
		}else{
			return "你选用的用户名 $v 含有非法字符";
		}
	}

	return true;
}

function check_email($v){

	if( false == JWUser::IsValidEmail($v, false) ){
		return "你选用的Email地址：$v 不合法";
	}

	$idExist = intval( JWUser::IsExistEmail($v) );
	if( $idExist==0 || $idExist==JWLogin::GetCurrentUserId() ) {
		return true;
	}

	return "你选用的Email地址：$v 已经被使用";
}
?>
