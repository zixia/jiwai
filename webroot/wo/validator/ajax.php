<?php
require_once( '../../../jiwai.inc.php' );
$k = $v = $v2 = null;
extract( $_REQUEST, EXTR_IF_EXISTS );

$ret = true;
if(null==v2)
{
	if( $k && function_exists( 'check_'.$k ) ) 
	{
		$funcName = 'check_'.$k;
		if( true === ( $ret = $funcName($v) ) )
			exit(0);
	}
}
else
{
	if( $k && function_exists( 'check_'.$k ) ) 
	{
		$funcName = 'check_'.$k;
		if( true === ( $ret = $funcName($v, $v2) ) )
			exit(0);
	}
}

echo $ret;

function check_nameScreen($v){

	if( false == JWUser::IsValidName($v, false) ){
		if( preg_match('/^\d+$/', $v ) ) {
			return "用户名 $v 不能完全是数字";
		}else if( strlen( $v ) < 4 ) {
			return "用户名 $v 不能短于 4 个字符";
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

function check_nameUrl($v){

	if( false == JWUser::IsValidName($v, false) ){
		if( preg_match('/^\d/', $v ) ) {
			return "主页地址 $v 不能以数字开头";
		}else if( strlen( $v ) < 5 ) {
			return "主页地址 $v 不能短于 5 个字符";
		}else{
			return "主页地址 $v 含有非法字符";
		}
	}

	$idExist = intval( JWUser::IsExistUrl($v) );
	if( $idExist==0 || $idExist==JWLogin::GetCurrentUserId() ) {
		return true;
	}

	return "主页地址：$v 已经被使用";
}

function check_nameFull($v){

	if( false == JWUser::IsValidFullName($v, false) ){
		if( preg_match('/^\d/', $v ) ) {
			return "姓名 不能以数字开头";
		}else if( strlen( $v ) < 2  || strlen($v) > 40 ) {
			return "姓名 长度必须在 2-40 字节之间";
		}else{
			return "姓名 含有非法字符";
		}
	}

	return true;
}

function check_email($v){

	if( false == JWUser::IsValidEmail($v, false) ){
		return "Email地址 不合法";
	}

	$idExist = intval( JWUser::IsExistEmail($v) );
	if( $idExist==0 || $idExist==JWLogin::GetCurrentUserId() ) {
		return true;
	}

	return "Email地址 已经被使用";
}

function check_url($v){
	$info = @parse_url( $v );
	if( isset( $info['scheme'] ) 
			&& ( strtolower( $info['scheme'] ) != 'http'
				&& strtolower($info['scheme']) != 'https'
			)
		){
		//return "$info[scheme]网址必须以http://或https://打头";
		return "网址必须以http://或https://开头";
	}

	if( false == isset( $info['scheme'] ) ) {
		@list($info['host'], $info['path']) = explode( '/', ltrim($info['path'], '/'), 2 );
	}

	if( preg_match( '/^([\w\-]+)(\.[\w\-]+)*(\.[a-z]{2,})$/', @$info['host'] ) 
			|| preg_match('/^\d+\.\d+\.\d+\.\d+$/', @$info['host']) 
			){
		return true;
	}

	//return "网址中的域名部分$info[host]不合法";
	return "网址中的域名部分 不合法";
}

function check_DeviceNoAndNameScreen($v, $v2)
{
    if( strlen( $v ) < 1 ) 
    {
        return "号码 不能为空";
    }

    if( strlen( $v2 ) < 1 ) 
    {
        return "用户名 不能为空";
    }

    if( preg_match('/^\d/', $v2 ) ) 
    {
        return "用户名 不能以数字开头";
    }
    else if( strlen( $v2 ) < 4 ) 
    {
        return "用户名 不能少于4个字符";
    }
    else if( strlen( $v2 ) > 20 ) 
    {
        return "用户名 不能长于20个字符";
    }
    else
    {
    }

    if(false == JWDevice::IsValid($v, 'all'))
    {
		return "号码 含有非法字符";
    }

	if( false == JWUser::IsValidName($v2, false) )
    {
		return "用户名 含有非法字符";
	}

    $UserId_row       = JWUser::GetUserInfo($v2, 'idUser');
    $IsExistNameScreen =  empty($UserId_row) ? false : true;

    $deviceUserId_rows    = JWUser::GetSearchDeviceUserIds($v, array('sms','qq','msn','skype','newsmth'));
    $IsExistDeviceNo =  empty($deviceUserId_rows) ? false : true;

    if ( !$IsExistNameScreen && !$IsExistDeviceNo )
    {
        return "第一次来?请从左边进入吧";
    }
    else
    {
        foreach($deviceUserId_rows as $deviceUserId_row)
        {
            if($deviceUserId_row == $UserId_row)
            {
                if ( JWUser::IsWebUser($UserId_row) )
                {
                    return '<a href="/wo/login">为什么不重新用网页登录</a>';
                }
                else
                {
                    return '';
                }
            }
        }
        return "号码 或 用户名 不正确";
    }
}

function check_DeviceNoAndNameScreen2($v, $v2){
    $v=trim($v," \t");
    $v2=trim($v2," \t");
    if( strlen( $v ) < 1 ) 
    {
        return "用户名 不能为空";
    }

    if( preg_match('/^\d/', $v ) ) 
    {
        return "用户名 不能以数字开头";
    }
    else if( strlen( $v ) < 4 ) 
    {
        return "用户名 不能少于4个字符";
    }
    else if( strlen( $v ) > 20 ) 
    {
        return "用户名 不能长于20个字符";
    }
	else if( false == JWUser::IsValidName($v, false) )
    {
		return "用户名 含有非法字符";
    }
    else
    {
    }

    $idUser = JWUser::GetUserInfo($v, 'idUser');
    if (!empty($idUser) )
    {
        if ( JWUser::IsWebUser($idUser) )
        {
            return '用户名 已经被使用，请更改';
        }

        $device_rows     = JWDevice::GetDeviceRowByUserId($idUser);
        if ( !empty($device_rows) )
            foreach($device_rows as $device_row)
            {    
                if (empty($device_row['secret']))
                { 
                    $devName=JWDevice::GetNameFromType($device_row['type']);
                    $devName=$devName . '号';

                    if(empty($v2))
                    {
                        return '第一次接触叽歪？换个用户名！用过叽歪？填写<span id="DevName" style="color:#000000">' . $devName . '</span>'; 
                    }
                    else if ($v2 != $device_row['address'])
                    {
                        return $devName . ' 或 用户名 不正确';
                    }
                    else
                    {
                        return true;
                    }
                }

            }   

        return "用户名 已经被使用，请更改";
    }

    return true;
}
?>
