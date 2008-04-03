<?php
/**
 * @package	JiWai.de
 * @copyright   AKA Inc.
 * @author	wqsemc@jiwai.com
 * @date	2007-12-18
 * @version	$Id$
 */

/**
 * JWFormValidate
 */

class JWFormValidate 
{
	static public function Validate()
	{
		$args = func_get_args();

		if ( 0===count($args) )
		{
			return true;
		}
	
		/* get first parameter */
		$key = array_shift( $args );
		if ( is_array( $key ) )
		{
			$items = $key;
			$result = array();
			foreach( $items as $one ) 
			{
				$key = array_shift( $one );
				$ret = self::_Validate( $key, $one );
				if ( $ret !== true )
				{
					array_push( $result, $ret );
				}
			}
			return empty($result) ? true : $result;
		}
		else if ( is_string($key) && $key )
		{
			return self::_Validate( $key, $args );
		}
		return true;
	}

	static private function _Validate($key, $arguments)
	{
		$func_name = 'Validate'.$key;
		$class_name = 'JWFormValidate';

		if ( method_exists($class_name, $func_name) )
		{
			return call_user_func_array( array($class_name, $func_name), $arguments );
		}
		else
		{
			throw new JWException("unsupport $class_name::$func_name");
		}

		return true;
	}

	static public function ValidateNameScreen($name_screen)
	{
		if ( false == JWUser::IsValidName($name_screen, false) )
		{
			if ( preg_match('/^\d+$/', $name_screen ) ) 
			{
				return "用户名 不能完全是数字";
			}
			else if ( strlen( $name_screen ) < 4 ) 
			{
				return "用户名 不能短于 4 个字符";
			}
			else
			{
				return "用户名 含有非法字符";
			}
		}

		$exist_id = intval( JWUser::IsExistName($name_screen) );
		if ( $exist_id==0 || $exist_id==JWLogin::GetCurrentUserId() ) 
		{
			return true; 
		}

		return "用户名 已经被使用";
	}

	static public function ValidateNameUrl($name_url)
	{

		if ( false == JWUser::IsValidName($name_url, false) )
		{
			if ( preg_match('/^\d+$/', $name_url ) ) 
			{
				return "永久地址 不能完全是数字";
			}
			else if ( empty( $name_url ) ) 
			{
				return true;//"永久地址 不能为空";
			}
			else if ( strlen( $name_url ) < 4 ) 
			{
				return "永久地址 不能短于 4 个字符";
			}
			else if ( strlen( $name_url ) > 20 )
			{
				return "永久地址 不能长于 20 个字符";
			}
			else
			{
				return "永久地址 包含有非法字符";
			}
		}

		$exist_id = intval( JWUser::IsExistUrl($name_url) );
		if ( $exist_id==0 || $exist_id==JWLogin::GetCurrentUserId() ) 
		{
			return true;
		}

		return "永久地址 已经被使用";
	}

	static public function ValidateNameFull($name_full)
	{
		if ( false == JWUser::IsValidFullName($name_full, false) )
		{
			if ( preg_match('/^\d/', $name_full ) ) 
			{
				return "姓名 不能以数字开头";
			}
			else if ( strlen( $name_full ) < 2  || strlen($name_full) > 40 ) 
			{
				return "姓名 长度必须在 2-40 字节之间";
			}
			else
			{
				return "姓名 含有非法字符";
			}
		}

		return true;
	}

	static public function ValidateEmail($email)
	{
		if ( false == JWUser::IsValidEmail($email, false) )
		{
			return "Email地址 不合法";
		}

		$exist_id = intval( JWUser::IsExistEmail($email) );
		if ( $exist_id==0 || $exist_id==JWLogin::GetCurrentUserId() ) 
		{
			return true; 
		}

		return "Email地址 已经被使用";
	}

	static public function ValidateUrl($url)
	{
		$info = @parse_url( $url );
		if ( isset( $info['scheme'] ) 
				&& ( strtolower( $info['scheme'] ) != 'http'
					&& strtolower($info['scheme']) != 'https'
				))
		{
			return "网址必须以http://或https://开头";
		}

		if ( false == isset( $info['scheme'] ) ) 
		{
			@list($info['host'], $info['path']) = explode( '/', ltrim($info['path'], '/'), 2 );
		}

		if ( preg_match( '/^([\w\-]+)(\.[\w\-]+)*(\.[a-z]{2,})$/', @$info['host'] ) 
				|| preg_match('/^\d+\.\d+\.\d+\.\d+$/', @$info['host']) )
		{
			return true;
		}

		return "网址中的域名部分 不合法";
	}

	static public function ValidateCompare($compare1, $compare2, $minlength=6, $maxlength=16, $compare1_tip='密码', $compare2_tip='确认密码')
	{
		if ( strlen($compare1)<$minlength)
			return "${compare1_tip}长度少于${minlength}个字符";

		if ( strlen($compare1)>$maxlength)
			return "${compare1_tip}长度多于${maxlength}个字符";

		if ( strlen($compare2)<$minlength)
			return "${compare2_tip}长度少于${minlength}个字符";

		if ( strlen($compare2)>$maxlength)
			return "${compare2_tip}长度多于${maxlength}个字符";

		if ($compare1 != $compare2 )
			return "${compare2_tip}与${compare1_tip}不相同";

		return true;
	}

	static public function ValidateDeviceNoAndNameScreen($device_no, $name_screen)
	{
		if ( strlen( $device_no ) < 1 ) 
		{
			return "号码 不能为空";
		}

		if ( strlen( $name_screen ) < 1 ) 
		{
			return "用户名 不能为空";
		}

		if ( preg_match('/^\d+$/', $name_screen ) ) 
		{
			return "用户名 不能完全是数字";
		}
		else if ( strlen( $name_screen ) < 4 ) 
		{
			return "用户名 不能少于4个字符";
		}
		else if ( strlen( $name_screen ) > 20 ) 
		{
			return "用户名 不能长于20个字符";
		}
		else
		{
		}

		if (false == JWDevice::IsValid($device_no, 'all'))
		{
			return "号码 含有非法字符";
		}

		if ( false == JWUser::IsValidName($name_screen, false) )
		{
			return "用户名 含有非法字符";
		}

		$userId_row = JWUser::GetUserInfo($name_screen, 'idUser');
		$IsExistNameScreen = empty($userId_row) ? false : true;

		$deviceUserId_rows = JWUser::GetSearchDeviceUserIds($device_no, JWDevice::$allArray);
		$IsExistDeviceNo = empty($deviceUserId_rows) ? false : true;

		if ( !$IsExistNameScreen && !$IsExistDeviceNo )
		{
			return "第一次来?请从左边进入吧";
		}
		else
		{
			foreach ($deviceUserId_rows as $deviceUserId_row)
			{
				if ($deviceUserId_row == $UserId_row)
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

	static public function ValidateDeviceNoAndNameScreenMatch($device_no, $name_screen)
	{
		$device_no=trim($device_no," \t");
		$name_screen=trim($name_screen," \t");
		if ( strlen( $name_screen ) < 1 ) 
		{
			return "用户名 不能为空";
		}

		if ( preg_match('/^\d+$/', $name_screen ) ) 
		{
			return "用户名 不能完全是数字";
		}
		else if ( strlen( $name_screen ) < 4 ) 
		{
			return "用户名 不能少于4个字符";
		}
		else if ( strlen( $name_screen ) > 20 ) 
		{
			return "用户名 不能长于20个字符";
		}
		else if ( false == JWUser::IsValidName($name_screen, false) )
		{
			return "用户名 含有非法字符";
		}
		else
		{
		}

		$idUser = JWUser::GetUserInfo($name_screen, 'idUser');
		if (!empty($idUser) )
		{
			if ( JWUser::IsWebUser($idUser) )
			{
				return '用户名 已经被使用，请更改';
			}

			$device_rows     = JWDevice::GetDeviceRowByUserId($idUser);
			if ( !empty($device_rows) )
			{
				foreach ($device_rows as $device_row)
				{    
					if (empty($device_row['secret']))
					{ 
						$dev_name = JWDevice::GetNameFromType($device_row['type']);
						$dev_name = $dev_name . '号';

						if (empty($device_no))
						{
							return '第一次接触叽歪？换个用户名！用过叽歪？填写<span id="DevName" style="color:#000000">' . $dev_name . '</span>'; 
						}
						else if ($device_no != $device_row['address'])
						{
							return $dev_name . ' 或 用户名 不正确';
						}
						else
						{
							return true;
						}
					}
				}   
			}

			return "用户名 已经被使用，请更改";
		}

		return true;
	}
}
?>
