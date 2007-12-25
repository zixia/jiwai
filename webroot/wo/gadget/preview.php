<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

define('DEFAULT_GADGET_COUNT', 3);
define('DEFAULT_GADGET_THEME', 'iChat');

JWLogin::MustLogined();

$user_info	= JWUser::GetCurrentUserInfo();
$user_id	= $user_info['id'];

if( $user_info['protected'] == 'Y' ) {
	$sub_menu = 'javascript';
	require_once( './noperm.php' );
	exit;
}


$div_id = "JiWai_de__gadget_timeline_user_3_iChat_UTF-8_$user_id";
$gadget_script_html = <<<_HTML_
<div><div id="$div_id"><script type='text/javascript' charset="utf-8" src='http://api.jiwai.de/gadget/timeline/$user_id.js?selector=user&count=3&theme=iChat&thumb=24&gadget_div=$div_id'></script></div><div style='font: 0px/0px sans-serif;clear: both;display: block'></div><div clear='both' style='text-align:center'><a title='叽歪' alt='叽歪' href='http://JiWai.de/$user_info[nameUrl]/' target='_blank' style='align:middle'>$user_info[nameFull]的叽歪档案<img src="http://asset.jiwai.de/img/favicon.gif" style="align:middle; border:0" /></a></div>
    </div>
_HTML_;

if ( isset($_REQUEST['gadget']) )
{
	
	$gadget = $_REQUEST['gadget'];

	if ( isset($gadget['hidefollow']) )
		$gadget['hidefollow']=true;
	else
		$gadget['hidefollow']=false;

	$div_id = "JiWai_de__gadget_timeline_$gadget[selector]_$gadget[count]_$gadget[theme]_$gadget[encoding]_$user_id";

	$gadget_script_html = 	"<div>";

	$gadget_script_html	.= 	"<div id='$div_id'>"
							."<script type='text/javascript' charset='utf-8' src='http://api.jiwai.de/gadget/timeline/$user_id.js"
									."?selector=$gadget[selector]"
									."&count=$gadget[count]"
                                    ."&theme=$gadget[theme]"
									."&thumb=$gadget[pictsize]"
									."&gadget_div=$div_id"
							."'></script>"
							."</div>";
						;

	$gadget_script_html .= "<div style='font: 0px/0px sans-serif;clear: both;display: block;'> </div>";

	if ( ! $gadget['hidefollow'] )
	{
			$sub_user_str = $user_info['nameFull'] . '的叽歪档案';

			if ( isset($gadget['encoding']) && 'UTF-8'!=strtoupper($gadget['encoding']) )
				$sub_user_str = mb_convert_encoding($sub_user_str, $gadget['encoding'], "UTF-8");

			$gadget_script_html .= "<div clear='both' style='text-align:center'>"
									."<a title='叽歪' alt='叽歪' href='http://JiWai.de/$user_info[nameUrl]/' target='_blank' style='align:middle'>"
									."$sub_user_str"
									."<img src='http://asset.jiwai.de/img/favicon.gif' border='0' />"
									."</a>"
									."</div>";
								;
	}

	$gadget_script_html	.= "</div>";

}

$theme_list	= array ( 	 'DOS_Box'			=> false
						,'text'			    => true
						,'iChat'			=> true
						,'Lined-Paper'		=> true
						,'PHP'				=> true
						,'PingPongPicture'	=> true
						,'SerenePicture'	=> false
						,'Swiss'			=> false
						,'Windows9x'		=> false
						,'WindowsXP'		=> false
					);
?>
<html>
<head>
<?php  JWTemplate::html_head(); ?>
</head>
<body>
<?php JWTemplate::accessibility(); ?>
<?php //JWTemplate::header(); ?>
    <div id="container">
        <p class="black14">请复制以下代码粘贴到你的blog或者网站相应位置<span class=copytips id=javascript_url_tip style="margin-left:15px">代码复制成功</span>
        </p>
        <p class="gadgetimage"><textarea id="javascript_url" rows="7" class="textarea" readonly="readonly"  style="width:760px; height:150px;align:center;" onclick="JiWai.copyToClipboard(this);" ><?php echo htmlspecialchars($gadget_script_html); ?></textarea></p>
        <p class="black15bold">预览：</p>
        <p><?php echo $gadget_script_html; ?></p>

    </div>
<?php //JWTemplate::footer(); ?>
</body>
</html>
