<?php
require_once ('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$note = null;
extract( $_GET, EXTR_IF_EXISTS );

$current_user_id = JWLogin::GetCurrentUserId();

if ( $current_user_id )
{
	$param = $_REQUEST['pathParam'];
	if ( preg_match('/^\/(\d+)$/',$param,$match) ){
		$tag_id = intval($match[1]);
		$tag_row = JWDB_Cache_Tag::GetDbRowById( $tag_id ); 
                JWSns::ExecWeb($current_user_id, "leave #$tag_row[name]", '取消关注此#');
	}
	else // no pathParam?
	{
		JWSession::SetInfo('error','哎呀！系统路径好像不太正确');
	}
}

JWTemplate::RedirectBackToLastUrl('/');
exit;
?>
