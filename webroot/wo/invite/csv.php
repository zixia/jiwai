<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$emails = array();
if ( $_FILES && isset($_FILES['clist']) ) 
{
	$file_info = @$_FILES['clist'];
	if ( 0===$file_info['error'] )
	{
		$c = file_get_contents( $file_info['tmp_name'] );
		$contacts = preg_split('/([,;\s\r\n])+/', $c, -1, PREG_SPLIT_NO_EMPTY);
		foreach($contacts AS $one) {
			if (JWDevice::IsValid($one,'email'))
				$emails[] = $one;
		}
	}
	else if ( $file_info['error'] != 4 ) 
	{
		switch ( $file_info['error'] )
		{
			case UPLOAD_ERR_INI_SIZE:
				JWSession::SetInfo('notice', '文件尺寸太大了，请将文件缩小后重新上传。');
				break;
			default:
				$error_html = '上传通讯录文件失败，请检查文件是否损坏，或可尝试另选文件进行上传。';
				JWSession::SetInfo('notice',$error_html);
				break;
		}
		JWTemplate::RedirectBackToLastUrl();
	}
}

if (empty( $emails) ) {
	JWTemplate::RedirectBackToLastUrl();
}

//build and save contact_list

$current_user_id = JWLogin::GetCurrentUserId();
$cache_key = md5(uniqid("${current_user_id}_clist"));

$contact_list= array();
foreach ($emails AS $one) {
	$contact_list[$one] = $one.','.$one;
}

$cache_object = array( 
		'user_id' => $current_user_id,
		'contact_list' => $contact_list,
		);

//we initial cache 30 minutes
$memcache = JWMemcache::Instance();
$memcache->Set( $cache_key, $cache_object, 0, 1800 );
JWTemplate::RedirectToUrl( '/wo/invite/step/'.$cache_key );
?>
