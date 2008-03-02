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
                <p class="black15bold">你和你关注的人</p>
                <div class="gadgetUWA">
<table>
<tr>
<td>
<p><iframe frameborder="0" id="frame_1204426526" src="http://www.netvibes.com/api/uwa/frame/uwa_standalone.php?id=1204426526&moduleUrl=http%3A%2F%2Fapi.jiwai.de%2Fuwa%2Findex.xhtml&commUrl=http://eco.netvibes.com/uwa.html" width="320" height="400" scrolling="no"></iframe></p>
</td>
<td valign="top">
<p><a target="_blank" href="http://eco.netvibes.com/subscribe/230699"><img src="http://eco.netvibes.com/img/add2netvibes.png" width="91" height="17" alt="Add to Netvibes"/></a></p>
<p><a target="_blank" href="http://blog.sohu.com/manage/module.do?m=preview&url=http%3A%2F%2Fapi.jiwai.de%2Fuwa%2Findex.xhtml"><img src="http://ow.blog.sohu.com/styles/images/btn_add.gif" width="91" alt="Add to Sohu Blog"/></a> 搜狐博客</p>
<p>也可以用于:
<ul class="otherinstall autoclear">
<li><a target="_blank" href="http://eco.netvibes.com/subscribe/230699?platform=igoogle" title="Get widget for iGoogle"><img src="http://eco.netvibes.com/style/public_v2/img/install-igoogle.png" width="16" height="16" alt="iGoogle"/> iGoogle</a></li>
<li><a target="_blank" href="http://eco.netvibes.com/subscribe/230699?platform=dashboard" title="Get widget for Apple Dashboard"><img src="http://eco.netvibes.com/style/public_v2/img/install-dashboard.png" width="16" height="16" alt="Apple Dashboard"/> Apple Dashboard</a></li>
<li><a target="_blank" href="http://eco.netvibes.com/subscribe/230699?platform=opera" title="Get widget for Opera"><img src="http://eco.netvibes.com/style/public_v2/img/install-opera.png" width="16" height="16" alt="Opera"/> Opera</a></li>
<li><a target="_blank" href="http://eco.netvibes.com/subscribe/230699?platform=vista" title="Get widget for Windows Vista"><img src="http://eco.netvibes.com/style/public_v2/img/install-vista.png" width="16" height="16" alt="Vista"/> Windows Vista</a> <em class="beta">beta</em></li>
<li><a target="_blank" href="http://eco.netvibes.com/subscribe/230699?platform=live" title="Get widget for Windows Live"><img src="http://eco.netvibes.com/style/public_v2/img/install-live.png" width="16" height="16" alt="Live"/> Windows Live</a> <em class="beta">beta</em></li>
</ul>
</p>
</td>
</tr>
</table>
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

