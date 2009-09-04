<?php
require_once( '../config.inc.php' );

JWLogin::MustLogined();

$loginedUserInfo 	= JWUser::GetCurrentUserInfo();
$loginedIdUser 	= $loginedUserInfo['id'];
if ( !empty($_FILES ) )
{
	$file_info = @$_FILES['profile_image'];
	$notice_html = null;
	$error_html = null;
	$pic_changed = true;

	if ( isset($file_info) 
			&& 0===$file_info['error'] 
			&& preg_match('/image/',$file_info['type']) 
	   )
	{

		$user_named_file = '/tmp/' . $file_info['name'];
		if ( move_uploaded_file($file_info['tmp_name'], $user_named_file) )
		{
			$picture_id = JWPicture::SaveUserIcon($loginedUserInfo['id'], $user_named_file);
			if ( $picture_id )
			{
				preg_match('/([^\/]+)$/',$user_named_file,$matches);
				JWUser::SetIcon($loginedUserInfo['id'],$picture_id);

				if ( null == $loginedUserInfo['idPicture'] ) 
				{
					JWSns::SetUserStatusPicture( $loginedUserInfo['id'], $picture_id );	
				}

				$pic_changed = true;
				JWSession::SetInfo('notice','头像修改成功。');
			}
			else
			{
				$contact_url = JWTemplate::GetConst('UrlContactUs');
				$pic_changed = false;
				$error_html = '上传头像失败，请检查头像图件是否损坏，或可尝试另选文件进行上载。如有疑问，请<a href="'.$contact_url.'">联系我们</a>';
				JWSession::SetInfo('error',$error_html);
			}

			@unlink ( $user_named_file );
		}
	}
	else if ( isset($file_info) 
			&& $file_info['error']>0 
			&& 4!==$file_info['error']
		)
	{
		switch ( $file_info['error'] )
		{
			case UPLOAD_ERR_INI_SIZE:
				JWSession::SetInfo('notice', '头像文件尺寸太大了，请将图片缩小分辨率后重新上载。');
				break;
			default:
				JWSession::SetInfo('notice','抱歉，你选择的图像没有上传成功，请重试。');
				break;
		}
	}
	redirect();
}


$shortcut = array( 'public_timeline', 'logout', 'my', 'search', 'favourite', 'message' , 'followings', 'index', 'replies' );
JWRender::Display( 'wo/avatar', array(
    'loginedUserInfo' => $loginedUserInfo,
    'shortcut' => $shortcut,
));
?>
