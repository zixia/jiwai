<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

define('DEFAULT_GADGET_COUNT', 3);

JWLogin::MustLogined();
$user_info   = JWUser::GetCurrentUserInfo();
$user_id = $user_info['id'];
$name_screen = $user_info['nameScreen'];
$name_url = $user_info['nameUrl'];

if( $user_info['protected'] == 'Y')
{
    $sub_menu = 'image';
    require_once( './noperm.php' );
    exit(0);
}

?>
<html>
<head>
<?php JWTemplate::html_head(); ?>

</head>

<body class="account" id="create">
<?php JWTemplate::accessibility() ?>
<?php JWTemplate::header() ?>

<!-- ul id="accessibility">
<li>
你正在使用手机吗？请来这里：<a href="http://m.JiWai.de/">m.JiWai.de</a>!
</li>
<li>
<a href="#navigation" accesskey="2">跳转到导航目录</a>
</li>
<li>
<a href="#side">跳转到功能目录</a>
</li>
</ul -->

<div id="container">
    <p class="top">窗可贴</p>
    <div id="wtMainBlock">
        <div class="leftdiv">
            <ul class="leftmenu">
                <li><a href="/wo/gadget/">窗可贴说明</a></li>
                <li><a href="/wo/gadget/image/" class="active">图片窗可贴</a></li>
                <li><a href="/wo/gadget/flash/">Flash窗可贴</a></li>
                <li><a href="/wo/gadget/javascript/">代码窗可贴</a></li>
            </ul>
        </div><!-- leftdiv -->
        <div class="rightdiv">
            <div class="lookfriend">
                <form method="post" id="f">
                <p><input type="hidden" id="m" value="2" />
                <span class="black15bold">样式：</span><input type="radio" name="mode" value="2" onclick="$('c').disabled=false; $('m').value=2;" checked /><span class="pad3">侧栏型</span>
                <input type="radio" name="mode" value="1" onclick="$('only').selected=true;$('c').disabled=true; $('m').value=1" /><span class="pad3">签名型</span>
                <input type="radio" name="mode" value="0" onclick="$('c').disabled=false;$('m').value=0;" /><span class="pad3">跑马灯</span></p>
                <p><span class="black15bold">宽度：</span>&nbsp;
                    <input name="textfield" id="w" type="text" class="inputStyle1" style="width:50px;" value="200" />&nbsp;<span class="pad3">像素</span></p>
                <p><span class="black15bold">条数：</span>&nbsp;
                    <select id="c" name="count" size="1" class="select">
                    <option value="1" id="only">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>

                    <option value="5" selected>5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                    <option value="10">10</option>

                    </select>&nbsp;<span class="pad3">条新叽歪</span></p>
<script type="text/javascript">
function draw() 
{
    //alert( $('pic_url') );
    var url = "http://api.jiwai.de/gadget/image/<?php echo $user_id;  ?>/c" + $("c").value 
        + "/w"+ $("w").value 
        + "/m" + $("m").value + "/gadget.png";
    $('pic_url').value=url; 
    $('ubb_url').value="[url=http://jiwai.de/<?php echo $name_url;  ?>/][img]" + url + " [/img][/url]";
    $('html_url').value='<a href="http://jiwai.de/<?php echo $name_url; ?>/" target="_blank" ><img src=' + url + ' title="叽歪" alt="叽歪" /></a>'; 
    $("o").src = url;
}
</script>
                </form>
                <p><input name="Submit" type="button" class="submitbutton" onclick="draw();" value="预览并更新代码" /></p>
                <p class="black15bold">预览：</p>
                <p><img id="o" title="叽歪" alt="叽歪" src="http://api.jiwai.de/gadget/image/<?php echo $user_id; ?>/c5/w200/m2/gadget.png"/>    </p>
                <p class="black15bold">代码：</p>
                <p class="black14">图片网址<span class=copytips id=pic_url_tip style="margin-left:15px">图片网址复制成功</span></p>
                <p class="gadgetimage"><textarea id="pic_url" rows="1" class="textarea" readonly="readonly"  style="width:525px; height:18px" onclick="JiWai.copyToClipboard(this);" >http://api.jiwai.de/gadget/image/<?php echo $user_id; ?>/c5/w200/m2/gadget.png</textarea></p>

                <p class="black14">UBB代码（论坛专用代码）<span class=copytips id=ubb_url_tip style="margin-left:15px">UBB代码复制成功</span></p>
                <p class="gadgetimage"><textarea id="ubb_url" rows="1" class="textarea" readonly="readonly"  style="width:525px; height:32px" onclick="JiWai.copyToClipboard(this);" >[url=http://jiwai.de/<?php echo $name_url;  ?>/][img]http://api.jiwai.de/gadget/image/<?php echo $user_id; ?>/c5/w200/m2/gadget.png[/img][/url]</textarea></p>
                <p class="black14">XHTML代码<span class=copytips id=html_url_tip style="margin-left:15px">XHTML代码复制成功</span></p>
                <p class="gadgetimage"><textarea id="html_url" rows="2" class="textarea" readonly="readonly"  style="width:525px; height:32px" onclick="JiWai.copyToClipboard(this);"><a title="叽歪" alt="叽歪" href="http://jiwai.de/<?php echo $name_url; ?>/" target="_blank"><img src="http://api.jiwai.de/gadget/image/<?php echo $user_id;  ?>/c5/w200/m2/gadget.png" /></a></textarea></p>


            </div><!-- lookfriend -->
            <div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
        </div><!-- rightdiv -->
    </div><!-- #wtMainBlock -->

    <div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer(); ?>
</body>
</html>

