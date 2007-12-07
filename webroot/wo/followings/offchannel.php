<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined();

//die(var_dump($_SERVER));
//die(var_dump($_REQUEST));

$note = null;
extract( $_GET, EXTR_IF_EXISTS );

$idLoginedUser=JWLogin::GetCurrentUserId();

if ( $idLoginedUser )
{
	$param = $_REQUEST['pathParam'];
	if ( preg_match('/^\/(\d+)$/',$param,$match) ){
		$idTag = intval($match[1]);

		$tagRow = JWTag::GetDbRowById( $idTag ); 
		$userRow = JWUser::GetUserInfo( $idLoginedUser );

                JWSns::ExecWeb($idLoginedUser, "off #$tagRow[name]", '取消此#更新通知');

	}
	else // no pathParam?
	{
		JWSession::SetInfo('error','哎呀！系统路径好像不太正确');
	}
}

JWTemplate::RedirectBackToLastUrl('/');
exit;
?>
