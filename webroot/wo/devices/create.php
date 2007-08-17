<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined();


//echo "<pre>"; die(var_dump($_REQUEST));
if ( ($idUser=JWLogin::GetCurrentUserId())
		&& array_key_exists('device',$_REQUEST) ){

	$aDeviceInfo = $_REQUEST['device'];

	if ( 'newsmth'==$aDeviceInfo['type'] &&  ! preg_match('/@/',$aDeviceInfo['address']) )
		$aDeviceInfo['address'] .= '@newsmth.net';


	$is_succ = JWDevice::Create($idUser
							, $aDeviceInfo['address']
							, $aDeviceInfo['type'] );

	$address	= $aDeviceInfo['address'];
	$type		= strtoupper($aDeviceInfo['type']);


	$type 	= JWDevice::GetNameFromType($type);

	$error_html = '';
	$notice_html = '';

	if ( null===$is_succ )
	{
		$error_html = <<<_ERR_
$type 帐号 $address 未能通过叽歪de系统检查，请您检查是否输入了正确的 $type 帐号（EMail需要写全@域名）。如有疑问请
_ERR_;
		$error_html .= '<a href="' . JWTemplate::GetConst('ContactUsUrl') . '">联系我们</a>';
	} 
	else if ( false===$is_succ )
	{
		$error_html = <<<_ERR_
对不起，$address 已经被使用，请您输入一个没有被使用的 $type 帐号。如有疑问请
_ERR_;
		$error_html .= '<a href="' . JWTemplate::GetConst('UrlContactUs') . '">联系我们</a>';
	}
	else if ( $is_succ )
	{
		$notice_html = <<<_INFO_
$type 帐号 $address 添加成功，耶！
_INFO_;
	}
	else
	{
		// no condition here
	}

	if ( !empty($error_html) )
		JWSession::SetInfo('error',$error_html);

	if ( !empty($notice_html) )
		JWSession::SetInfo('notice',$notice_html);

}

$return_url = '/wo/device/';

if ( isset($_SERVER['HTTP_REFERER']) )
	$return_url = $_SERVER['HTTP_REFERER'];

header ("Location: $return_url");
exit(0);
?>
