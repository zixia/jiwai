<?php 
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();
?>
<html>
<head>
    <?php JWTemplate::html_head(); ?>
</head>

<body class="account" id="create">

<?php JWTemplate::accessibility(); ?>
<?php JWTemplate::header(); ?>
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
                    <li><a href="/wo/gadget/" class="active">窗可贴说明</a></li>
                    <li><a href="/wo/gadget/image/">图片窗可贴</a></li>

                    <li><a href="/wo/gadget/flash/">Flash窗可贴</a></li>
                    <li><a href="/wo/gadget/javascript/">代码窗可贴</a></li>
                </ul>
            </div><!-- leftdiv -->
            <div class="rightdiv">
            	<div class="lookfriend">
            		<p class="black15bold">在你的博客或者论坛签名档上自动显示你的叽歪</p>
            		<p class="gray12">你可以根据喜好自由样式，大小以及内容。</p>
            		<p class="gray12">根据Blog的实际情况和个人喜好，可以选择图片、Flash或者JavaScript代码三种不同形式的窗可贴</p>
            		<p class="gadget12">我们罗列出了一些窗可贴的使用方法，希望对您有所帮助：</p>
        		</div><!-- lookfriend -->

        		<div class="lookfriend">
            		<ul class="gadget">
                		<li><a href="http://www.blogbus.com" class="smallblack"><img src="<?php echo JWTemplate::GetAssetUrl('/images/gadget_blogbus.gif'); ?>" title="博客大巴" border="0" /><p class="smallblack">博客大巴</p></a></li>
                		<li><a href="http://blog.sina.com.cn" class="smallblack"><img src="<?php echo JWTemplate::GetAssetUrl('/images/gadgett_sina.gif'); ?>" width="30" height="30" title="新浪博客" /><p class="smallblack">新浪博客</p></a></li>
                		<li><a href="http://spaces.live.com" class="smallblack"><img src="<?php echo JWTemplate::GetAssetUrl('/images/gadget_msn.gif'); ?>" width="30" height="30" title="MSN空间" /><p class="smallblack">MSN空间</p></a></li>
                		<li><a href="http://blog.sohu.com/" class="smallblack"><img src="<?php echo JWTemplate::GetAssetUrl('/images/gadget_sohu.gif'); ?>" width="30" height="30" title="搜狐博客" /><p class="smallblack">搜狐博客</p></a></li>
                		<li><a href="http://blog.163.com/" class="smallblack"><img src="<?php echo JWTemplate::GetAssetUrl('/images/gadget_163.gif'); ?>" width="30" height="30" title="网易博客" /><p class="smallblack">网易博客</p></a></li>
                		<li><a href="http://blog.mop.com/" class="smallblack"><img src="<?php echo JWTemplate::GetAssetUrl('/images/gadget_maopu.gif'); ?>" width="30" height="30" title="猫扑博客" /><p class="smallblack">猫扑博客</p></a></li>
                		<li><a href="http://blog.tfol.com/" class="smallblack"><img src="<?php echo JWTemplate::GetAssetUrl('/images/gadget_tianhu.gif'); ?>" width="30" height="30" title="天虎博客" /><p class="smallblack">天虎博客</p></a></li>
                		<li><a href="http://www.blogchinese.com/" class="smallblack"><img src="<?php echo JWTemplate::GetAssetUrl('/images/gadget_blogchinese.gif'); ?>" width="30" height="30" title="BlogChinese" /><p class="smallblack">BlogChinese</p></a></li>

                		<li><a href="http://blog.tom.com/" class="smallblack"><img src="<?php echo JWTemplate::GetAssetUrl('/images/gadget_tom.gif'); ?>" width="30" height="30" title="TOM博客" /><p class="smallblack">TOM博客</p></a></li>
                		<li><a href="http://blog.qq.com/" class="smallblack"><img src="<?php echo JWTemplate::GetAssetUrl('/images/gadget_qzone.gif'); ?>" width="30" height="30" title="QQ空间" /><p class="smallblack">QQ空间</p></a></li>
                		<li><a href="http://www.blogcn.com/" class="smallblack"><img src="<?php echo JWTemplate::GetAssetUrl('/images/gadget_blogcn.gif'); ?>" width="30" height="30" title="博客中国" /><p class="smallblack">博客中国</p></a></li>
                		<li><a href="http://www.hexun.com/" class="smallblack"><img src="<?php echo JWTemplate::GetAssetUrl('/images/gadget_hexun.gif'); ?>" width="30" height="30" title="和讯中国" /><p class="smallblack">和讯博客</p></a></li>
                		<li><a href="http://www.bokeren.com" class="smallblack"><img src="<?php echo JWTemplate::GetAssetUrl('/images/gadget_bokeren.gif'); ?>" width="30" height="30" title="博客人" /><p class="smallblack">博客人</p></a></li>
                		<li><a href="http://blog.tianya.cn/" class="smallblack"><img src="<?php echo JWTemplate::GetAssetUrl('/images/gadget_tianya.gif'); ?>" width="30" height="30" title="天涯博客" /><p class="smallblack">天涯博客</p></a></li>
                		<li><a href="http://www.yculblog.com/" class="smallblack"><img src="<?php echo JWTemplate::GetAssetUrl('/images/gadget_yculblog.gif'); ?>" width="30" height="30" title="歪酷博客" /><p class="smallblack">歪酷博客</p></a></li>
            		</ul>
        		</div><!-- lookfriend -->
    		<div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>

    		</div><!-- rightdiv -->
		</div><!-- #wtMainBlock -->
		<div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->
<?php JWTemplate::footer(); ?>
</body>
</html>

