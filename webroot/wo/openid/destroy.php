<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined();

$logined_user_id=JWLogin::GetCurrentUserId();

if ( $logined_user_id )
{
	$param = $_REQUEST['pathParam'];

	if ( preg_match('/^\/(\d+)$/',$param,$match) )
	{
		$openid_id = $match[1];

		if ( ! JWOpenID::IsUserOwnId($logined_user_id, $openid_id) )
		{
			JWTemplate::RedirectTo404NotFound();
		}
		
		$openid_db_row = JWOpenID::GetDbRowById($openid_id);

		$openid_url = $openid_db_row['urlOpenid'];

		if ( JWOpenID::Destroy($openid_id) )
		{
			$notice_html = <<<_HTML_
$openid_url 删除成功。
_HTML_;
		}
		else
		{
			$error_html = <<<_HTML_
哎呀！由于系统故障，删除失败了……
请稍后再试。
_HTML_;
		}
	}

	if ( !empty($error_html) )
		JWSession::SetInfo('error',$error_html);

	if ( !empty($notice_html) )
		JWSession::SetInfo('notice',$notice_html);
}


if ( array_key_exists('HTTP_REFERER',$_SERVER) )
	$redirect_url = $_SERVER['HTTP_REFERER'];
else
	$redirect_url = '/';

header ("Location: $redirect_url");
exit(0);
?>
