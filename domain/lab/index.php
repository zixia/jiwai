<?php
require_once('../../jiwai.inc.php');
JWTemplate::html_doctype();

?>

<html>
<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="settings">
<?php JWTemplate::accessibility() ?>
<?php JWTemplate::header() ?>

<div id="container">
    <div id="labNav" class="subtab">
        <a href="/" class="now">叽歪de大中国</a>
    </div>
    <div class="tabbody">
        <h2><a href="/googlemap/">叽歪de大中国 (JiWai.de Vision)</a></h2>
        <fieldset>
        <p>在中国地图上观看大家正在叽歪些什么。（自己想出现在地图上？填写自己的位置城市信息就可以啦！）</p>
        <p>2007/06/13 -          <a style="color: rgb(111, 111, 111);" href="mailto:wo@jiwai.de">给我们反馈</a></p>

        <h2><a href="http://groups.google.com/group/jiwai-development-talk">和大家一起讨论</a></h2>
        <p><img src="http://groups.google.com/groups/img/3/groups_bar_zh-CN.gif" height=26 width=132 alt="Google 网上论坛"></p>
        <p>订阅 jiwai-development-talk</p>
        <form action="http://groups.google.com/group/jiwai-development-talk/boxsubscribe">
        <p>电子邮件： <input type=text name=email> <input type=submit name="sub" value="订阅"></p>
        </form>
        <p><a href="http://groups.google.com/group/jiwai-development-talk">访问此论坛</a></p>
        </fieldset>
    </div>

<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
