<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined();

if ( ($idUser=JWLogin::GetCurrentUserId())
		&& array_key_exists('_method',$_REQUEST)
		&& array_key_exists('pathParam',$_REQUEST) )
{

	$method = $_REQUEST['_method'];
	$param = $_REQUEST['pathParam'];

	$idStatus = null;

	if ( preg_match('/^\/(\d+)$/',$param,$match) )
	{
		$idStatus = $match[1];

		if ( $method==='delete' )
		{
			if ( JWUser::IsAdmin($idUser) || JWStatus::IsUserOwnStatus($idUser, $idStatus)){
				JWStatus::Destroy($idStatus);
			}
			else
			{
				$error_html = <<<_HTML_
<li>您无权删除这条更新（编号 $idStatus ）</li>
_HTML_;
				JWSession::SetInfo('error',$error_html);
			}
		}
	}
}

if ( array_key_exists('HTTP_REFERER',$_SERVER) )
	header ('Location: ' . $_SERVER['HTTP_REFERER']);
else
	header ('Location: /');

exit(0);
?>
