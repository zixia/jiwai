<?php
$debug = false;

if ( ! $debug )
	die ("Unauthorized.");


$ERROR_CODE = array(	52	=> 'HE_ERR_MSG'
					, 53	=> 'HE_ERR_USERNUMBER'
					, 54	=> 'HE_ERR_PID'
					, 55	=> 'HE_ERR_MOFLAG'
					, 56	=> 'HE_ERR_GATEWAY'
					, 57	=> 'HE_ERR_MSGTYPE'
					, 58	=> 'HE_ERR_ILLEGAL_IP'
					, 59	=> 'HE_ERR_ILLEGAL_APPID'
				);


define ('MT_TYPE_MO_FIRST',		0); // MO点播引起的第一条MT消息
define ('MT_TYPE_MO_NOT_FIRST',	1); // MO点播引起的非第一条MT消息
define ('MT_TYPE_NO_MO',		2); // 非MO点播引起的MT消息
define ('MT_TYPE_SYSTEM',		3); // 系统反馈引起的MT消息


define ('FEE_FREE',				0); // 免费消息
define ('FEE_NORMAL',			1); // 正常收费
define ('FEE_MONTHLY_LIST',		2); // 包月话单
define ('FEE_MONTHLY_DOWNLOAD',	3); // 包月下发


$appid	= null;	// 数字，应用编号，需分配
$gid	= null;	// 数字，网关ID
$dst	= null;	// 数字,目的手机号 
$pid	= null;	// 数字,产品ID
$msg	= null;	// 文字，消息内容，（URL编码）
$linkid	= null;	// 如果mo里面有带下来，(没有不填，不要乱填)
$func	= null; //数字，长号码，只加自己的扩展号


$func	= '8816';
$msg 	= urlencode("你好！我是测试消息。 [ ] !@#$%^&*()_+|}{\"?>< Hello! I'm a test msg!\nnew line");
$dst	= '13911833788';

/*
$pid	= 
$linkid = 
$appid	= 
$gid	=
*/

$moflag		= MT_TYPE_NO_MO;
$msgtype	= FEE_FREE;

//define ('MT_HTTP_URL',	'http://211.157.106.111:8092/sms/submit');
define ('MT_HTTP_URL',	'http://beta.jiwai.de/wo/dump');

$url = MT_HTTP_URL . "?appid=$appid"
					. "&gid=$gid"
					. "&dst=$dst"
					. "&pid=$pid"
					. "&msg=$msg"
					. "&linkid=$linkid"
					. "&func=$func"
					. "&moflag=$moflag"
					. "&msgtype=$msgtype"
					. "&src=13210011001" //XXX src ??
				;

echo "Calling: [$url]<br>\n";

ob_flush();
flush();

$return_content = file_get_contents($url);

if ( !preg_match('/^(\d+)\s+(\s+)$/',$return_content,$matches) )
{
	echo "return content parse err:[$return_content] <br>\n";
}

$ret	= $matches[1];
$msgid	= $matches[2];

echo "mt returns: ret[$ret] / msgid[$msgid] <br>\n";
?>
