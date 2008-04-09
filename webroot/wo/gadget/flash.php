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
                <li><a href="/wo/gadget/flash/" class="now">Flash窗可贴</a></li>
                <li><a href="/wo/gadget/uwa/">UWA窗可贴</a></li>
                <li><a href="/wo/gadget/javascript/">代码窗可贴</a></li>
            </ul>
        </div><!-- leftdiv -->
        <div class="rightdiv">
            <div class="lookfriend">
                <p class="black15bold">你和你关注的人</p>
                <div class="gadgetFlash">
                    <embed pluginspage=" http://www.macromedia.com/go/getflashplayer" quality="high" allowscriptaccess="always" align="middle" flashvars="userid=<?php echo $user_id; ?>" src=" http://asset.jiwai.de/gadget/flash/friends_gadget_maker.swf" type="application/x-shockwave-flash" height="600" width="530" name="jiwai_badge"/>
                    </embed>
		</div><!-- gadgetFlash -->
            </div><!-- lookfriend -->
                <div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>

        </div><!-- rightdiv -->
    </div><!-- #wtMainBlock -->
                <div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->
<?php JWTemplate::footer(); ?>
</body>
</html>

