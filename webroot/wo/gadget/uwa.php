<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();
JWLogin::MustLogined();
$user_id	= JWLogin::GetCurrentUserId();
$user_info = JWUser::GetUserInfo($user_id);

if( $user_info['protected'] == 'Y')
{
	$sub_menu = 'flash';
	require_once( './noperm.php' );
	exit;
}

?>
<html>
<head>
<?php JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
));?>
</head>

<body class="account" id="create">

<?php JWTemplate::header() ?>

<div id="container">
    <p class="top">窗可贴</p>
    <div id="wtMainBlock">
        <div class="leftdiv">
            <ul class="leftmenu">
                <li><a href="/wo/gadget/">窗可贴说明</a></li>
                <li><a href="/wo/gadget/image/">图片窗可贴</a></li>
                <li><a href="/wo/gadget/flash/">Flash窗可贴</a></li>
                <li><a href="/wo/gadget/uwa/" class="now">UWA窗可贴</a></li>
                <li><a href="/wo/gadget/javascript/">代码窗可贴</a></li>
            </ul>
        </div><!-- leftdiv -->
        <div class="rightdiv">
            <div class="lookfriend">
                <p class="black15bold">UWA窗可贴效果图</p>
                <div class="gadgetUWA">
<table>
<tr>
<td>
<p><img src="<? echo JWTemplate::GetAssetUrl('/images/uwa_snap.png');?>" alt="叽歪网UWA窗可贴效果图" title="叽歪网UWA窗可贴效果图"/></p>
</td>
<td width="100">
</td>
<td valign="top">
<p><a target="_blank" title="添加到Netvibes" href="http://eco.netvibes.com/subscribe/230699"><img src="<? echo JWTemplate::GetAssetUrl('/images/btn_netvibes.gif');?>" width="91" height="17" alt="添加到Netvibes"/></a></p>
<p><a title="添加到搜狐博客" target="_blank" href="http://blog.sohu.com/manage/module.do?m=preview&url=http%3A%2F%2Fapi.jiwai.de%2Fuwa%2Findex.xhtml"><img src="<? echo JWTemplate::GetAssetUrl('/images/btn_add.gif');?>" width="91" alt="添加到搜狐博客"/></a><br/>添加到搜狐博客</p>
<p>也可以用于
<ul class="otherinstall autoclear">
<li><a title="添加到iGoogle" target="_blank" href="http://eco.netvibes.com/subscribe/230699?platform=igoogle"><img src="<? echo JWTemplate::GetAssetUrl('/images/install-igoogle.png');?>" width="16" height="16" alt="添加到iGoogle"/> iGoogle</a></li>
<li><a title="添加到Apple Dashboard" target="_blank" href="http://eco.netvibes.com/subscribe/230699?platform=dashboard"><img src="<? echo JWTemplate::GetAssetUrl('/images/install-dashboard.png');?>" width="16" height="16" alt="添加到Apple Dashboard"/> Apple Dashboard</a></li>
<li><a title="添加到Opera" target="_blank" href="http://eco.netvibes.com/subscribe/230699?platform=opera"><img src="<? echo JWTemplate::GetAssetUrl('/images/install-opera.png');?>" width="16" height="16" alt="添加到Opera"/> Opera</a></li>
<li><a title="添加到Vista" target="_blank" href="http://eco.netvibes.com/subscribe/230699?platform=vista"><img src="<? echo JWTemplate::GetAssetUrl('/images/install-vista.png');?>" width="16" height="16" alt="添加到Vista"/> Windows Vista</a> <em class="beta">beta</em></li>
<li><a title="添加到Live" target="_blank" href="http://eco.netvibes.com/subscribe/230699?platform=live"><img src="<? echo JWTemplate::GetAssetUrl('/images/install-live.png');?>" width="16" height="16" alt="添加到Live"/> Windows Live</a> <em class="beta">beta</em></li>
</ul>
</p>
</td>
</tr>
</table>

<br/><br/>
<a title="搜狐博客开放平台" target="_blank" href="http://ow.blog.sohu.com"><img src="<? echo JWTemplate::GetAssetUrl('/images/blog_sohu_com.gif');?>"alt="搜狐博客开放平台"/></a>
		</div><!-- gadgetUWA -->
            </div><!-- lookfriend -->
                <div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>

        </div><!-- rightdiv -->
    </div><!-- #wtMainBlock -->
                <div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->
<?php JWTemplate::footer(); ?>
</body>
</html>

