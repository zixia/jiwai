<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined(false);

//die(var_dump($_REQUEST));

$logined_user_id	= JWLogin::GetCurrentUserId();

$redirect_url	= "http://jiwai.de/";

if ( $logined_user_id )
{
	$param = $_REQUEST['pathParam'];

	if ( preg_match('/^\/(\d+)\/(.*)$/',$param,$match) ){
		$balloon_id 	= $match[1];
		$redirect_url	= $match[2] ? $match[2] : $_SERVER['HTTP_REFERER'];

		$balloon_row	= JWBalloon::GetDbRowById($balloon_id);

//die(" [ $logined_user_id ] ");
//die(var_dump($balloon_row));
		if ( $logined_user_id == $balloon_row['idUser'] )
		{
			JWBalloon::Destroy($balloon_id);
		}
	}
}

header ("Location: $redirect_url");
exit(0);
?>
