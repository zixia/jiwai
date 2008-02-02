<?php
require_once('../../../jiwai.inc.php');

if ( array_key_exists('username',$_POST) )
{
	$username = $_POST['username'];
	$password_one = $_POST['password_one'];
	$password_confirm = $_POST['password_confirm'];

	if ( false==JWUser::IsValidName( $username ) )
	{
		echo '-用户名不合法';
		exit(0);
	}

	if ( ''==strval(trim($password_one)) ) 
	{
		echo '-密码不能为空';
		exit(0);
	}

	if ( $password_one != $password_confirm )
	{
		echo '-密码与确认密码不一致，请重新输入';
		exit(0);
	}

	if ( false==JWUser::IsExistName( $username ) )
	{
		$user_array = array(
			'nameScreen' => $username,
			'pass' => $password_one,
		);

		$user_id = JWUser::Create( $user_array );
		if ( $user_id )
		{
			JWLogin::Login( $user_id, true );
			echo '+'.$user_id;
		}
		else
		{
			echo '-系统出现故障，请稍后注册';
			exit(0);
		}
	}
	else
	{
		echo '-用户名已经被占用';
		exit(0);
	}
}
?>
