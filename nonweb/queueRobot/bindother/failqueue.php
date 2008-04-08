<?php
require_once '../../../jiwai.inc.php';

define( 'FB_MAX_IDEL', 6400000 ); //u second
define( 'FB_MIN_IDEL',  100000 ); //u second
define( 'FB_MAX_TRY', 6 );
define( 'FB_FAIL_FILE', '/tmp/fail_bindother' );
define( 'FB_INTERVAL', 10 ); //second

$now_idle = FB_MIN_IDEL;

function idle()
{
	global $now_idle;
	usleep( $now_idle );
	echo "[IDEL] " . ( $now_idle/1000) . "ms\n";
	$now_idle *= 2;

	if ( $now_idle >= FB_MAX_IDEL )
	{
		$now_idle = FB_MAX_IDEL;
	}
}

function enter_main_loop()
{
	global $now_idle;
	$fails_from_base = array();

	while( true )
	{
		$fails_from_file = get_fail();
		if ( count($fails_from_file) )
		{
			$now_idle = FB_MIN_IDEL;
			foreach( $fails_from_file AS $one )
			{
				$one['t']++;
				$bind = $one['b'];
				$one['n'] = time() + $one['t'] * FB_INTERVAL;

				if ( JWBindOther::PostStatus($one['b'], $one['m']) )
				{
					echo "[SUCC] $bind[service]://$bind[loginName]\n";
				}
				else
				{
					echo "[FAIL][FILE][T:$one[t]] $bind[service]://$bind[loginName]\n";
					array_push( $fails_from_base, $one );
				}
			}
		}

		if ( count($fails_from_base) )
		{
			$fails_from_base_tmp = array();
			foreach( $fails_from_base AS $one )
			{
				$time = time();
				$bind = $one['b'];

				/* wait time interval*/
				if ( $one['n'] > $time )
				{
					array_push( $fails_from_base_tmp, $one );
					continue;
				}

				$now_idle = FB_MIN_IDEL;
				$one['t']++;
				if ( JWBindOther::PostStatus($one['b'], $one['m']) )
				{
					echo "[SUCC] $bind[service]://$bind[loginName]\n";
				}
				else
				{
					$one['n'] = time() + $one['t'] * FB_INTERVAL;
					if ( $one['t'] > FB_MAX_TRY )
					{
						echo "[DROP] $bind[service]://$bind[loginName]\n";
					}
					else
					{
						echo "[FAIL][BASE][T:$one[t]] $bind[service]://$bind[loginName]\n";
						array_push( $fails_from_base_tmp, $one );
					}
				}
			}

			$fails_from_base = $fails_from_base_tmp;
		}

		idle();
	}
}

function get_fail()
{
	$fails = array();

	if ( false == file_exists( FB_FAIL_FILE ) )
	{
		return $fails;
	}

	$contents = file_get_contents( FB_FAIL_FILE );
	@unlink( FB_FAIL_FILE );

	$farray = preg_split( '/[\r|\n]/', $contents, -1, PREG_SPLIT_NO_EMPTY );
	
	foreach( $farray AS $one )
	{
		$fail = json_decode( base64_decode($one), true );
		$fail['t'] = 0;
		$fail['n'] = 0;
		$fails[] = $fail;
	}

	return $fails;
}

enter_main_loop();
?>
