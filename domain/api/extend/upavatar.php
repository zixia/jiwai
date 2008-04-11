<?php
require_once('../../../jiwai.inc.php');

$current_user_id = JWApi::GetAuthedUserId();
if( ! $current_user_id )
{
	JWApi::RenderAuth(JWApi::AUTH_HTTP);
}

$status = $idPartner = null;
extract($_POST, EXTR_IF_EXISTS);

/* default setting */
$device = 'api';
$time_create = time();
$is_signature = 'N';
$server_address = 'api.jiwai.de';
$options = array(
	'nofilter' => true,
	'idPartner' => intval($idPartner),
);

$avatar_file_info = @$_FILES['avatar_file'];
if ( isset($avatar_file_info) 
	&& 0===$avatar_file_info['error'] 
	&& preg_match('/image/',$avatar_file_info['type']) 
   )   
{ 
    /* work around with widsets */
    if ($avatar_file_info['name'] === 'avatar_file') {
        $avatar_file_info['name'] = 'avatar' . time(). '.jpg';
    }
	$user_named_file = '/tmp/' . $avatar_file_info['name'];

	if ( move_uploaded_file($avatar_file_info['tmp_name'], $user_named_file) )
	{
		$picture_id = JWPicture::SaveUserIcon($current_user_id, $user_named_file);
		if ( $picture_id )
		{
            $options['idPicture'] = $picture_id;
            JWUser::SetIcon($current_user_id, $picture_id);
            if ($status)
                JWSns::UpdateStatus( $current_user_id, $status, $device, $time_create, $is_signature, $server_address, $options );
			echo '+OK';
			exit;
		}
	}

    @unlink ( $user_named_file );
}
else
{
	JWApi::OutHeader(400,true);
}

echo '-ERR';
?>
