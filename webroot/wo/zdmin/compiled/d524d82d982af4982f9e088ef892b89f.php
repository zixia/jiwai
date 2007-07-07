<html>
<head>
<title>叽歪de(JiWai&trade;)后台管理系统</title>
<style>
body { margin:0px; line-height:150%; font-size:14px; width:1000px;}
td,th{ font-size:14px; }
#header {padding:20px;text-align:center;font-size:32px; font-weight:bold; background-color:#456789; color:#FFF;}
#footer {padding:10px;text-align:center;font-size:14px; border-top:1px solid #999;}

.notice { border:1px solid #900; margin:10px; background:#FFD9D9;}
.notice td { padding:1em 3em 1em 3em; } 
.page { width:100%; }
.clear { clear:both; }

#left { width:180px; float:left; padding:10px; border-right:1px solid #999; margin-right:10px;}
#left h2 { font-size:16px; margin:0px; }
#left ul { margin:0px; padding:0px; display:block;}
#left li { margin:5px; list-style:none; display:block; margin:5px;}
#left li a{color:#00F;}
#left li a:hover { font-size:15px; font-weight:bold; }
#left li.selected a{ font-size:15px; font-weight:bold; color:#F00;}

#main { padding:10px; }
#main h2 { font-size:20px; margin:0 0 15px 0; }
#main h3 { font-size:16px; margin:0 0 10px 0; }

.result{ margin:15px; padding:0px; text-align:center; color:#104755; background-color:#b0b4c8; }
.result tr{ text-align:right; color:#333; font-weight: normal; background-color:#fff; nowrap; }
.result th{ background:orange; color:#333; border: 0px solid #777; font-weight: bold; nowrap; }
.result td{ text-align:right; color:#333; font-weight: normal; nowrap; }

</style>
</head>
<body>
<div id="header">
	叽歪de后台管理系统
</div>

<div class="page">
	<div id="left">
		<h2>内容审查</h2>
		<ul>
			<li <?= $this->menu_nav=='filterwords'?'class="selected"':'' ?>><a href="/wo/zdmin/filterwords">禁忌词设置</a></li>
			<li <?= $this->menu_nav=='filterdict'?'class="selected"':'' ?>><a href="/wo/zdmin/filterdict">禁忌词典生成</a></li>
			<li <?= $this->menu_nav=='statusexam'?'class="selected"':'' ?>><a href="/wo/zdmin/statusexam">JiWai更新审查</a></li>
		</ul>
		<h2>JiWai更新管理</h2>
		<ul>
			<li <?= $this->menu_nav=='statusupdate'?'class="selected"':'' ?>><a href="/wo/zdmin/statusupdate">叽歪更新同步</a></li>
			<li <?= $this->menu_nav=='statusdelete'?'class="selected"':'' ?>><a href="/wo/zdmin/statusdelete">删除某条更新 [Y]</a></li>
		</ul>
		<h2>JiWai用户管理</h2>
		<ul>
			<li <?= $this->menu_nav=='userquery'?'class="selected"':'' ?>><a href="/wo/zdmin/userquery">用户查询</a></li>
			<li <?= $this->menu_nav=='usershield'?'class="selected"':'' ?>><a href="/wo/zdmin/usershield">屏蔽用户列表</a></li>
			<li <?= $this->menu_nav=='usersetting'?'class="selected"':'' ?>><a href="/wo/zdmin/usersetting">修改用户设置</a></li>
		</ul>
		<h2>JiWai运营数据查询</h2>
		<ul>
			<li <?= $this->menu_nav=='userstatus'?'class="selected"':'' ?>><a href="/wo/zdmin/userstatus">用户更新查询</a></li>
			<li <?= $this->menu_nav=='userregistered'?'class="selected"':'' ?>><a href="/wo/zdmin/userregistered">注册用户查询 [Y]</a></li>
			<li <?= $this->menu_nav=='imquery'?'class="selected"':'' ?>><a href="/wo/zdmin/imquery">IM设备查询 [Y]</a></li>
			<li <?= $this->menu_nav=='smsquery'?'class="selected"':'' ?>><a href="/wo/zdmin/smsquery">手机绑定查询</a></li>
		</ul>
	</div>
	<div id="main">
