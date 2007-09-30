<?php
require_once( dirname(dirname(dirname(__FILE__))).'/jiwai.inc.php' );

define( 'SP_ALL', 'ALL' );
define( 'SP_MOBILE', 1 );
define( 'SP_UNICOM', 2 );
define( 'SP_PAS', 3 );

define( 'SP_TEST', 999 );
define( 'MSG_TEST', 'MSG_TEST' );

define( 'FETCH_STEP', 200 );
define( 'SEND_INTERVAL', 20000 ); //20ms, 20*1000 us


$options = getOpt('s:m:');
if( false == isset($options['s']) ) $options['s'] = SP_TEST;
if( false == isset($options['m']) ) $options['m'] = MSG_TEST;

$msgContent = trim( JWRuntimeInfo::Get( $options['m'] ) );
if( $msgContent == null ) {
	die( "No message need to be send.\n" );
}

/** 手机服务商 **/
$supplier = $options['s'];

$phoneArray = array();
if( $supplier == SP_TEST ) {
	$phoneArray = array(
				'13955457592', '13601369910', //'13911153525',
				//'13520805254', '13911833788', '13810746178',
	);
	SendToSms( $phoneArray, $msgContent, $supplier );
}else{
	for( $i=0; ; $i++ ) {
		$startPos = $i * FETCH_STEP;
		$SQL = "SELECT address FROM Device WHERE type='sms' ORDER BY ID DESC LIMIT $startPos , " . FETCH_STEP;
		$rows = JWDB::GetQueryResult( $SQL, true );
		$rowCount = count( $rows ) ;

		$phoneArray = array();
		foreach( $rows as $r ) {
			array_push( $phoneArray, $r['address'] );
		}
		SendToSms( $phoneArray, $msgContent, $supplier );

		/** 已经取完 **/
		if( $rowCount < FETCH_STEP ) 
			break;
	}
}

/**
 * Send To Phone Array
 */
function SendToSms( $phoneArray, $msgContent, $supplier = SP_ALL ) 
{
	foreach( $phoneArray as $phone ) 
	{
		switch( $supplier )
		{
			case SP_ALL:
			case SP_TEST:
				JWSms::SendMt( $phone , $msgContent );
			break;
			default:
			{
				$sp = JWDevice::GetMobileSP( $phone );
				if( $supplier == $sp ) 
				{
					usleep( SEND_INTERVAL );
					JWSms::SendMt( $phone, $msgContent );
				}
			}
			break;
		}
	}
}
?>
