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
		$idPageUser = intval($match[1]);

		$friendRow = JWUser::GetUserInfo( $idPageUser );
		$userRow = JWUser::GetUserInfo( $idLoginedUser );

                JWSns::ExecWeb($idLoginedUser, "follow $friendRow[nameScreen]", '打开关注');
		
		if( $note ) {
			if( $idExist = JWFollowerRequest::IsExist( $friendRow['id'], $idLoginedUser ) ) {
				$upArray = array( 'note' => $note, );
				JWDB::UpdateTableRow( 'FollowerRequest', $idExist, $upArray );
			}
		}

	}
	else // no pathParam?
	{
		JWSession::SetInfo('error','哎呀！系统路径好像不太正确');
	}
}

JWTemplate::RedirectBackToLastUrl('/');
exit;
?>
