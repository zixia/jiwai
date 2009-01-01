<?php
require_once ('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$current_user_id = JWLogin::GetCurrentUserId();

$param = $_REQUEST['pathParam'];
if ( preg_match('/^\/(\d+)$/',$param,$match) )
{
	$other_user_id = intval($match[1]);
	$design = new JWDesign($other_user_id);
	if ( $design->IsDesigned() ) {
		$design->Save($current_user_id);
	} else {
		$design->Destroy($current_user_id);
	}
	JWSession::SetInfo('notice', '修改自定义配色成功！');
}

JWTemplate::RedirectBackToLastUrl();
?>
