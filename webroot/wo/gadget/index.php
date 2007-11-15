<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();
/*
@header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
@header("Cache-Control: no-store, no-cache, must-revalidate");
@header("Pragma: no-cache");
*/

?>
<html>

<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="settings">
<?php JWTemplate::accessibility() ?>
<?php JWTemplate::header() ?>

<div id="container">
    <?php JWTemplate::UserGadgetNav('index'); ?>
    <div class="tabbody" style="height:680px;" >

        <h2>说明</h2>
        <fieldset>
            <p>我们目前支持 <a href="javascript">JavaScript</a> 、<a href="flash">Flash</a> 、<a href="image">Image</a> 等三种格式。</p>
            <p>在不同的博客站点，插入叽歪de窗可贴的方法略有不同。</p>
            <p>你的博客是：</p>
            
            <style type="text/css">
            #blogs li { margin-top: 10px; }
            </style>
            
            <ul id="blogs">
            <li><a class="ext" href="http://help.jiwai.de/BlogbusWidget"><img width="185" height="55" border="0" alt="博客大巴" src="http://www.blogbus.com/images/site_v4/logo.gif"/>BlogBus</a><span class="exttail">∞</span></li><br/>
            <li><a class="ext" href="http://help.jiwai.de/SinablogWidget"><img width="180" height="33" alt="新浪博客" src="http://image2.sina.com.cn/blog/in061204img/yocc061219/3.5logo.gif"/>新浪博客</a><span class="exttail">∞</span></li><br>
            <li><a class="ext" href="http://help.jiwai.de/SohublogWidget"><img border="0" alt="SohuBlog" src="http://blog.sohu.com/home/new/style/images/bloglogo.jpg"/>Sohu博客</a><span class="exttail">∞</span></li><br>
            <li><a class="ext" href="http://help.jiwai.de/MsnSpaceWidget"><span style="font-size:2em">Windows Live<span class="tm">™</span> Spaces</span> MSN Space</a><span class="exttail">∞</span></li><br>
            <li><a class="ext" href="http://help.jiwai.de/BloggerWidget"><img width="173" height="50" src="http://blogger.com/img/logo100.gif" alt=" Blogger "/>Blogger</a><span class="exttail">∞</span></li><br>
            <li><a class="ext" href="http://help.jiwai.de/NeteaseblogWidget"><img src="http://blog.163.com/style/common/index/image/logo.gif" alt="网易博客">网易博客</a><span class="exttail">∞</span></li><br>
            <li><a class="ext" href="http://help.jiwai.de/TianyaWidget"><img width="151" height="47" src="http://blog.tianya.cn/images/blog_2.gif"/>天涯博客</a><span class="exttail">∞</span></li><br>
            <li><a class="ext" href="http://help.jiwai.de/YculWidget"><img width="185" height="55" border="0" alt="歪酷博客 Ycul Blog - 记录我们的时代" src="http://sta.yculblog.com/images/logo/general-185x55.gif"/>歪酷博客</a><span class="exttail">∞</span></li><br>
            </ul>
        </fieldset>
    </div>
<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
