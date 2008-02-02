<?php
require_once("../../../jiwai.inc.php");

define ('SP_IP', '211.157.106.111');
define ('ZX_IP', '211.99.222.55');

error_log( var_export( $_REQUEST , true ), 3, '/tmp/requestsms' );

$debug = false;
if ( false == $debug )
{
	$proxy_ip 	= JWRequest::GetProxyIp();
	$client_ip 	= JWRequest::GetClientIp();

	$over_ip = $proxy_ip .','. $client_ip;
	if (false === strpos($over_ip, SP_IP) && false === strpos($over_ip, ZX_IP) )
	{
		header('HTTP/1.0 401 Unauthorized');
		die ("You must use registered IP address.");
	}
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

error_log( var_export( $_REQUEST , true ), 3, '/tmp/requestsms' );

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

		if ( true ) {
			$moKey = JWDB_Cache::GetCacheKeyByFunction( array( 'JWWosms', 'UserMO'), $arg_src );
			$memcache = JWMemcache::Instance();
			$memcache->Set( $moKey, time(), 0, 60 );

			$v = intval( JWRuntimeInfo::Get('ROBOT_COUNT_SMS_MO') );
			JWRuntimeInfo::Set( 'ROBOT_COUNT_SMS_MO', ++$v );

		}
		
		/*
		 *	FIXME
		 *	被拆分成多条的短信
		 */ 
		$arg_msg = preg_replace('/^(%3F)+/', '', $arg_msg);


		/** DELA max pieces of gsm-sms */
		$cut_key = JWDB_Cache::GetCacheKeyByFunction( array( 'JWWosms', 'StoreCut'), $arg_src );
		$memcache = JWMemcache::Instance();
		$old_msg = $memcache->Get( $cut_key );

		if( mb_strlen( $arg_msg , 'GBK') > 3 
				&& mb_substr($arg_msg,0,1,'GBK') == '?' 
				&& ( mb_substr($arg_msg,1,1,'GBK') == '?'
                    || mb_substr($arg_msg,2,1,'GBK') == '?' 
                    )
        ) {

			$net_msg = mb_substr( $arg_msg, 3, 67, 'GBK');

			$old_len = mb_strlen( $old_msg, 'GBK' );
			$net_len = mb_strlen( $net_msg, 'GBK' );

			//error_log( "net_len: $net_len\n", 3 , '/tmp/requestsms' );

			if( $net_len < 67 ) { // it may be last content;

				//error_log( "retrieve it!\n ", 3, "/tmp/requestsms");
				
				if( $old_len == 0 ) { // may this msg come quick than the head msg;

					$memcache->Set( $cut_key, $net_msg, 0, 600 );
					return true;

				}else{  // merge the exists one;    ########## FINISHED ########

					$arg_msg = $old_msg . $net_msg;
					$memcache->Del( $cut_key );

				}

			}else{
				//error_log( "long msg\n", 3, "/tmp/requestsms");

				if( $old_len < 67 && $old_len > 0 ) { ######### FINISHED ######
					$arg_msg = $net_msg . $old_msg ;
					$memcache->Del( $cut_key );
				}else{
					$new_msg = $old_msg . $net_msg ;
					$memcache->Set( $cut_key, $new_msg, 0, 600 ); // store about 10mins
					return true;
				}
			}
		}else{
			if( $old_msg ) {

				$old_arg_msg = iconv('GBK','UTF-8', $old_msg );
				if( $arg_gid == JWSms::GID_CHINAMOBILE ) {
					$arg_linkid = rand(100000000,999999999);
				}
				JWSms::ReceiveMo($arg_src, $arg_dst, $old_arg_msg, $arg_linkid, $arg_gid);
				$memcache->Del( $cut_key );
			}
		}

		$arg_msg = iconv('GBK//IGNORE','UTF-8//IGNORE', $arg_msg );
		if( $arg_gid == JWSms::GID_CHINAMOBILE ) {
			$arg_linkid = rand(100000000,999999999);
		}
		return JWSms::ReceiveMo($arg_src, $arg_dst, $arg_msg, $arg_linkid, $arg_gid);
}

function mop_report()
{
		global $arg_src, $arg_gid
				, $arg_msgid, $arg_state, $arg_errcode;


		return JWSms::DeliveReport($arg_src, $arg_msgid,$arg_state, $arg_errcode, $arg_gid);
}

?>
