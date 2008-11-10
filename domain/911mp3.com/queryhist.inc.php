<?php

$HIST_FILE = "hist.txt";

function save_query_hist($keyword)
{
	global $HIST_FILE;

	$now = strftime("%Y-%m-%d %T",time());
	$ip	= @$_SERVER['REMOTE_ADDR'];

	$client = 'pc';
	if ( isset($_SERVER['HTTP_ACCEPT']) 
			&& (strpos($_SERVER['HTTP_ACCEPT'],'vnd.wap.wml')!==FALSE) 
		)
	{
		$client = 'mobile';
	}
	
	$fh = fopen($HIST_FILE, 'a') or die("can't open file");
	fwrite($fh, "\n$now\t$ip\t$client\t$keyword");
	return fclose($fh);
}

function load_query_hist($num)
{
	global $HIST_FILE;
	$num = intval($num);
	$str = `tail -$num $HIST_FILE`;
	$arr = split("\n",$str);
	$arr = preg_replace('/^.+\t/','',$arr);
	return array_reverse(array_unique($arr));
}

//save_query_hist('zixia');

//print_r(load_query_hist(3));
?>
