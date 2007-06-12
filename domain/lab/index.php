<?php
require_once('../../jiwai.inc.php');
JWTemplate::html_doctype();

?>

<html>

<head>
<?php 
$options = array(	 'title'		=> "实验室"
/*
					,'keywords'		=> htmlspecialchars($keywords)
					,'description'	=> htmlspecialchars($description)
					,'author'		=> htmlspecialchars($keywords)
					,'rss'			=> $rss
					,'refresh_time'	=> '600'
					,'refresh_url'	=> ''
*/
			);


JWTemplate::html_head($options);
?>
</head>


<body class="front" id="front">


<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<div class="separator"></div>

<div id="container">
	<div id="content">
		<div id="wrapper">


<table border=0>
<tr>
<td valign="top" align="right">
<font size="-1" color="#ff0000">新!</font>
</td>
<td valign="top">
	<b><a href="/googlemap/">叽歪de大中国 (JiWai.de Vision)</a></b><br/>
	<font size="-1">在中国地图上观看大家正在叽歪些什么。（自己想出现在地图上？填写自己的位置城市信息就可以啦！）</font><br/>
	<font size="-1" color="#6f6f6f">2007/06/13 -          <a style="color: rgb(111, 111, 111);" href="mailto:wo@jiwai.de">给我们反馈</a>
	 - <a href="http://groups.google.com/group/JiWai-BBS/" style="color: rgb(111, 111, 111);">和大家一起讨论</a>
	</font><br/><br/>
</td>
</tr>
</table>

		</div><!-- wrapper -->
	</div><!-- content -->
</div><!-- #container -->
<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
