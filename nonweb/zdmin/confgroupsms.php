<?php
require_once( dirname(__FILE__) . '/function.php');

$allow_user = array(1, 863, 37340); //Zixia, Lecause, ShenQQ

$content = $number = $date_begin = $date_end = null;
extract( $_POST, EXTR_IF_EXISTS );

if ( $content && $number !== '' && $date_end && $date_begin )
{
	DoPost( $number, $content, $date_begin, $date_end );
	Header("Location: confgroupsms.php");
}
else if ( $_POST )
{
;
}
else
{
$date_begin = date('Y-m-d', strtotime("1 months ago") );
$date_end = date('Y-m-d', strtotime("tomorrow") );
$content = " [叽歪网]";
}

function DoPost( $number, $content, $date_begin, $date_end )
{
	global $allow_user;
	if ( false == in_array($_SESSION['idUser'] , $allow_user ) )
	{
		setTips("只有id在 ". var_export( $allow_user, true ) . " 中的用户才能群发短信");
		Header('Location: confgroupsms.php');
		exit;
	}

	$conference = JWConference::GetDbRowFromNumber( $number );
	if ( empty( $conference ) )
		return 0;
	
	$sql = "SELECT distinct(address) FROM Status s,Device d WHERE s.idUser=d.idUser and d.type='sms' AND s.idConference=$conference[id] AND s.timeCreate>='".JWDB::EscapeString($date_begin)."' AND s.timeCreate<='".JWDB::EscapeString($date_end)."'";

	$rows = JWDB::GetQueryResult( $sql, true );

	$count = 0;
	foreach( $rows AS $one )
	{
		$code = JWSPCode::GetCodeByMobileNo( $one['address'] );
		if ( empty($one) )
			continue;

		$server_address = $code['code'] . $code['func'] . $code['funcPlus'] . '10' . $number ;
		JWRobot::SendMtRawQueue($one['address'], 'sms', $content, $server_address, null);
		$count++;
	}
	
	/**
	$code = JWSPCode::GetCodeByMobileNo( '13955457592' );
	$server_address = $code['code'] . $code['func'] . $code['funcPlus'] ;
	JWRobot::SendMtRawQueue( '13955457592', 'sms', $content, $server_address, null );
	echo "send to seek<br/>";
	**/

	setTips( "群发到 $count 位会议参与者" );

	return $count;
}

JWRender::Display("confgroupsms", array(
		'content' => $content,
		'date_begin' => $date_begin,
		'date_end' => $date_end,
	    'menu_nav' => 'confgroupsms',
    ));
?>
