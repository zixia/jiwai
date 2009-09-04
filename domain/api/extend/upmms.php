<?php
require_once('../../../jiwai.inc.php');

$current_user_id = JWApi::GetAuthedUserId();
if( ! $current_user_id )
{
	JWApi::RenderAuth(JWApi::AUTH_HTTP);
}

$status = $idPartner = null;
extract($_POST, EXTR_IF_EXISTS);

if ( null==$status )
{
	JWApi::OutHeader(400,true);
}

/* default setting */
$device = 'api';
$time_create = time();
$is_signature = 'N';
$server_address = 'api.jiwai.de';
$options = array(
	'nofilter' => true,
	'idPartner' => intval($idPartner),
);

$mms_file_info = @$_FILES['mms_file'];
/* change type limit for hesin client mms */
if ( isset($mms_file_info) 
	&& 0===$mms_file_info['error'] 
	&& ( preg_match('/image/',$mms_file_info['type']) 
		|| ( null==$mms_file_info['type'] && preg_match('/\.(jpg|jpeg|gif|png)$/i', $mms_file_info['name']))
	   )
   )   
{ 
    /* work around with widsets */
    if ($mms_file_info['name'] === 'mms_file') {
        $mms_file_info['name'] = 'mms' . time(). '.jpg';
    }
	$user_named_file = '/tmp/' . $mms_file_info['name'];

	if ( move_uploaded_file($mms_file_info['tmp_name'], $user_named_file) )
	{
		$picture_id = JWPicture::SaveUserIcon($current_user_id, $user_named_file, 'MMS');
		if ( $picture_id )
		{
			$options['idPicture'] = $picture_id;
			$options['statusType'] = 'MMS';
                	JWSns::UpdateStatus($current_user_id, $status, $device, $time_create, $server_address, $options);
			echo '+OK';
			exit;
		}
	}
}
else
{
	JWApi::OutHeader(400,true);
}

echo '-ERR';
?>
