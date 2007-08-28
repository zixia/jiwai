<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();

if ( preg_match('#^/(\w+)$#',@$_REQUEST['pathParam'],$matches) )
{
	$secret	= $matches[1];

	$user_id	= JWLogin::LoadRememberMe($secret);


	if ( $user_id )
	{
		JWLogin::DelRememberMe($user_id, $secret);

		JWLogin::Login($user_id, false);

		JWSession::SetInfo('reset_password', 1);

		$notice_html = <<<_HTML_
现在你可以设置新密码了，下次不要再忘记喽！:-)
_HTML_;
		JWSession::SetInfo('notice',$notice_html);

		header('Location: /wo/account/settings');

		exit(0);
	}
	else
	{
		JWTemplate::RedirectTo404NotFound();
		exit(0);
	}
}

?>

