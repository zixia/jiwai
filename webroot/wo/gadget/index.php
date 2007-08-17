<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();
/*
@header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
@header("Cache-Control: no-store, no-cache, must-revalidate");
@header("Pragma: no-cache");
*/


JWLogin::MustLogined();


$user_info		= JWUser::GetCurrentUserInfo();

//var_dump($user_info);
?>
<html>

<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="gadget">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div id="container" class="subpage">

<h2><?php echo $user_info['nameScreen']?>的窗可贴</h2>

<?php JWTemplate::UserGadgetNav('index'); ?>

<br />
<br />

<h3>我们目前支持 <a href="javascript">Javascript</a> 和 <a href="flash">Flash</a>，很快会支持 Gif 格式。</h3>
			
<h3>在不同的博客站点，插入叽歪de窗可贴的方法略有不同。</h3>
<h3>你的博客是：</h3>
<br>

<style type="text/css">
#blogs li { margin-top: 10px }
</style>

<ul id="blogs">
<li><a class="ext" href="http://help.jiwai.de/SinablogWidget"><img width="180" height="33" alt="新浪博客" src="http://image2.sina.com.cn/blog/in061204img/yocc061219/3.5logo.gif"/>新浪博客</a><span class="exttail">∞</span></li><br>
<li><a class="ext" href="http://help.jiwai.de/SohublogWidget"><img border="0" alt="SohuBlog" src="http://blog.sohu.com/home/new/style/images/bloglogo.jpg"/>Sohu博客</a><span class="exttail">∞</span></li><br>
<li><a class="ext" href="http://help.jiwai.de/MsnSpaceWidget"><span style="font-size:2em">Windows Live<span class="tm">™</span> Spaces</span> MSN Space</a><span class="exttail">∞</span></li><br>
<li><a class="ext" href="http://help.jiwai.de/BloggerWidget"><img width="173" height="50" src="http://blogger.com/img/logo100.gif" alt=" Blogger "/>Blogger</a><span class="exttail">∞</span></li><br>
<li><a class="ext" href="http://help.jiwai.de/NeteaseblogWidget"><img src="http://blog.163.com/style/common/index/image/logo.gif" alt="网易博客">网易博客</a><span class="exttail">∞</span></li><br>
<li><a class="ext" href="http://help.jiwai.de/TianyaWidget"><img width="151" height="47" src="http://blog.tianya.cn/images/blog_2.gif"/>天涯博客</a><span class="exttail">∞</span></li><br>
<li><a class="ext" href="http://help.jiwai.de/YculWidget"><img width="185" height="55" border="0" alt="歪酷博客 Ycul Blog - 记录我们的时代" src="http://sta.yculblog.com/images/logo/general-185x55.gif"/>歪酷博客</a><span class="exttail">∞</span></li><br>
<li><a class="ext" href="http://help.jiwai.de/BlogusWidget"><img width="227" height="28" border="0" alt="BlogUS The Our Life" src="http://www.blogus.cn/OblogStyle/SysStyle/blogus/top_logo.gif"/>Blogus</a><span class="exttail">∞</span></li>
</ul>

<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
