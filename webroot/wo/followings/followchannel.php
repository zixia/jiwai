<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined(false);

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

		$tagRow = JWDB_Cache_Tag::GetDbRowById( $idTag ); 
		$userRow = JWUser::GetUserInfo( $idLoginedUser );

                JWSns::ExecWeb($idLoginedUser, "follow #$tagRow[name]", '打开关注此#');

	}
	else // no pathParam?
	{
		JWSession::SetInfo('error','哎呀！系统路径好像不太正确');
	}
}

JWTemplate::RedirectBackToLastUrl('/');
exit;
?>
