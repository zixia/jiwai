<?php
require_once("../../../jiwai.inc.php");

define ('SP_IP', '211.157.106.111');

$debug = false;
if ( (!$debug) && (SP_IP!=$_SERVER['REMOTE_ADDR']) )
{
	die ("Not Authorized.");
}

$arg_op		= @$_REQUEST['op'];

$arg_src	= @$_REQUEST['src'];
$arg_gid	= @$_REQUEST['gid'];
$arg_pid	= @$_REQUEST['pid'];
$arg_dst	= @$_REQUEST['dst'];
$arg_linkid	= @$_REQUEST['linkid'];
//XXX rawurlencode / urlencode ?
$arg_msg	= @$_REQUEST['msg'];
$arg_msgid	= @$_REQUEST['msgid'];
$arg_state	= @$_REQUEST['state'];
$arg_errcode= @$_REQUEST['errcode'];


switch ($arg_op)
{
	case 'sub': // share Subscribe
	case 'disub':
		$ret = mop_subscribe();
		break;
	case 'mo':
		$ret = mop_mo();
		break;
	case 'rpt':
		$ret = mop_report();
		break;
	default:
		JWLog::Instance('SMS')->Log("unknown sms op: $arg_op");
		$ret = false;
}

if ( $ret )
	echo "OK";

exit(0);

/////////////////////////////////////////////

function mop_subscribe()
{
		global $arg_op, $arg_src, $arg_gid, $arg_pid;

		$sub_flag = ('sub'==$arg_op)
						? true
						: false
					;


		return JWSms::SubscribeReport($arg_src, $sub_flag, $arg_pid, $arg_gid);
}

function mop_mo()
{
		global $arg_src, $arg_gid, $arg_dst
				, $arg_linkid, $arg_msg;

		//error_log ( "mop.php mop_mo received urlencode msg: [$arg_msg]" );
		
		$arg_msg = preg_replace('/^(%3F)+/', '', $arg_msg);

		$arg_msg = iconv('GBK','UTF-8',urldecode($arg_msg));

		//file_put_contents("/tmp/zixia.txt", "$arg_src -> $arg_dst\n$arg_msg");
		return JWSms::ReceiveMo($arg_src, $arg_dst, $arg_msg, $arg_linkid, $arg_gid);
}

function mop_report()
{
		global $arg_src, $arg_gid
				, $arg_msgid, $arg_state, $arg_errcode;


		return JWSms::DeliveReport($arg_src, $arg_msgid,$arg_state, $arg_errcode, $arg_gid);
}

?>
