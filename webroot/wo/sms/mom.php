<?php
require_once("../../../jiwai.inc.php");

define ('SP_IP', '211.157.106.172');

$debug = false;
if (!$debug)
{
	$proxy_ip 	= JWRequest::GetProxyIp();
	$client_ip 	= JWRequest::GetClientIp();

	if (false && SP_IP!=$proxy_ip && SP_IP!=$client_ip )
	{
		header('HTTP/1.0 401 Unauthorized');
		die ("You must use registered IP address.");
	}
}

/**/
error_log( var_export( $_REQUEST , true )."\n" , 3, '/tmp/requestmom' );
/**/

$arg_type = @$_REQUEST['type'];
$arg_op = ( $arg_type ) ? @$_REQUEST['op'] : null;

switch ($arg_type)
{
	case 'sync': // share Subscribe
        switch( $arg_op ) {
            case 1:
                $ret = mom_subscribe();
            break;
            case 2:
                $ret = mom_unsubscribe();
            break;
        }
        break;
	case 'rpt':
		$ret = mom_report();
		break;
	default:
		$postedXml = isset($HTTP_RAW_POST_DATA) ? 
		       	trim($HTTP_RAW_POST_DATA) : trim(file_get_contents("php://input"));
		if( $postedXml || CONSOLE ) {
			$ret = mom_mo( $postedXml );
		}else {
			$ret = false;
		}
}

if ( $ret )
	echo "OK";

exit(0);

function mom_subscribe()
{
    return true;
}

function mom_unsubscribe()
{
    return true;
}

function mom_report()
{
    return true;
}

function mom_mo( $postedXml = null )
{
    if( null == $postedXml ) {
        $f = base64_encode("编码后的数据");
        $i = base64_encode( file_get_contents('1.gif') );

        $postedXml = <<<DATA
<mmsMO>
    <GatewayID>1</GatewayID>
    <Sender>13966483592</Sender>
    <Receiver>25208</Receiver>
    <Subject>测试彩信</Subject>
    <LinkID></LinkID>
    <MMS>
        <smil id="1.smil" encode="base64"></smil>
        <content>
            <item id="2.gif" encode="base64">$i</item>
            <item id="1.txt" encode="base64">$f</item>
        </content>
    </MMS>
</mmsMO>
DATA;
	return true;
    }
    if( $postedXml || CONSOLE ){
        error_log( $postedXml, 3, '/tmp/requestmom' );
    }

    return JWMms::ReceiveMo( $postedXml );
}
?>
