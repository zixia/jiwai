<?php
require_once('../../../jiwai.inc.php');

JWTemplate::html_doctype();
?>
<html xmlns="http://www.w3.org/1999/xhtml">

<?php 

$status_data 	= JWStatus::GetStatusIdsFromPublic(100);
$status_rows	= JWStatus::GetDbRowsByIds($status_data['status_ids']);
$user_rows		= JWDB_Cache_User::GetDbRowsByIds	($status_data['user_ids']);

$keywords 		= '叽歪网广场 ';
$user_showed 	= array();
foreach ( $user_rows  as $user_id=>$user_row )
{
	if ( isset($user_showed[$user_id]) )
		continue;
	else
		$user_showed[$user_id] = true;

	$keywords .= "$user_row[nameScreen]($user_row[nameFull]) ";
}

$description = '叽歪网广场 ';
foreach ( $status_data['status_ids'] as $status_id )
{
	$description .= $status_rows[$status_id]['status'];
	if ( mb_strlen($description,'UTF-8') > 140 )
	{
			$description = mb_substr($description,0,140,'UTF-8');
			break;
	}
}

$options = array(	 'title'		=> '随时随地记录与分享 / 叽歪网向导 4-4 '
					,'keywords'		=> htmlspecialchars($keywords)
					,'description'	=> htmlspecialchars($description)
					,'author'		=> htmlspecialchars($keywords)
					,'rss_url'		=> "http://api.".JW_HOSTNAME."/status/public_timeline.rss"
					,'rss_title'	=> '叽歪网 - 叽歪网广场 [RSS]'
					,'refresh_url'	=> ''
					,'version_css_jiwai_screen'	=> 'v2'
					,'is_load_all'	=> 'false'
			);

?>
<head>
<?php JWTemplate::html_head($options) ?>
</head>

<body class="normal" id="front">

<?php JWTemplate::accessibility() ?>

<body>
<div id="IndexContainer">
    <div class="containerL">
	    <a href="<?php echo JW_SRVNAME;?>"><img src="<?php echo JWTemplate::GetAssetUrl('/images/logo.gif');?>" alt="叽歪网" title="叽歪网" width="138" height="57" /></a>
	</div>
		<div class="containerTopR"><img src="<?php echo JWTemplate::GetAssetUrl('/images/tour_03.gif');?>" alt="随时随地记录与分享" title="随时随地记录与分享" width="238" height="39" />
</div>
</div>
<div id="wizardFrame" class="wizardFrame">
	<div id="wizardNav" class="wizardNav">
		<ul>
			<li class="navOff"><a href="<?php echo JW_SRVNAME ."/wo/wizard/";?>">叽歪网简介</a></li>
			<li class="navOff"><a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_1";?>">1.手机博客</a></li>
			<li class="navOff"><a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_2";?>">2.QQ与MSN聊天</a></li>
			<li class="navOff"><a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_3";?>">3.群发短信</a></li>
			<li class="navOn">4.叽歪大屏幕</a></li>
		</ul>
	</div>
	<div id="wizardContent">
	<div id="wizardPic" class="wizardPic"> <img src="<?php echo JWTemplate::GetAssetUrl('/images/Big_screen.jpg');?>" width="360" height="277" alt="什么是叽歪大屏幕？" title="什么是叽歪大屏幕？"/></div>
	  <div id="wizardText" class="winzardText">
	    <div class="Intro">
	      <p class="IntroTitle"><span class="IntroNum">4</span>什么是叽歪大屏幕？</p>
	    </div>
			<p class="note7 gray12"><b>应用叽歪大屏幕，你只需</b></p>
			<p class="note7 ">一台投影仪</p>
			<p class="note9">一台上网电脑</p>
			<p class="note8 gray12"><b>通过叽歪大屏幕，你能实现</b></p>
			<p class="note7">会场内外实时互动！</p>
			<p class="note9">会场互动实况网上直播！</p>
			<p class="note9">会议、论坛、展会上亮点频频！</p>
			<p class="note8 gray12"><b>通过叽歪大屏幕，你可以让现场</b></p>
			<p class="note7">每一位与会者都可能成为备受关注的焦点</p>
			<p class="note7">每一位发言者能与观众们活跃的思维和五花八门的问题碰撞出灵感的火花</p>
			<p class="note7">每一个互动环节无论是投票、竞猜、游戏、娱乐都充满轻松愉快的参与体验</p>
		    <p class="Screentitle">叽歪大屏幕成功案例</p>
  	  </div>
	  		<div id="wizardScreen" class="wizardScreen">
		    <p>2007年末两个月内，叽歪小弟的成绩让人刮目相看！</p>
            <p class="gray14"><b>叽歪大屏幕大型会议应用成功案例</b></p>
            <p class="text">全球最大的技术图书出版商O'Reilly Press主办 “China FOO Camp”（300）</p>
			<p class="text">全球知名会展公司Avail主办“在线社区运营及商业化论坛”（100）</p>
			<p class="text">全球可用性专业协会(CPA)主办 “2007中国青年设计节暨大中华区用户体验年会”（500）</p>
			<p class="text">汇集中国最知名博客专业人士“2007中文网志年会”（400）</p>
			<p class="text">中国最大IT技术社区(CSDN)主办“2007国际软件开发2.0大会”（1500）</p>
			<p class="text">中国IT界发行量最大的《计算机世界报》主办“2007中国IT用户年会”（1500）</p>
			<p class="text">中国首家商业运作博客网站BlogBus（博客大巴)“公司年会暨五周年庆典” ( 200 ) </p>
			<p>开源代码灰狐社区Open Source Camp 论坛 （200）</p>
            <p class="gray14"><b>叽歪大屏幕娱乐现场、校园应用成功案例</b></p>
            <p class="text">京城摇滚乐重镇MAO Live House（新时代原创音乐乐园）音乐人Live Show （200）</p>
			<p class="text">清华大学多场次千人规模 “2007清华大学生艺术节”（与人文、美术、机械等学院合作）（1000）</p>
			<p class="gray">﹡括号内为每场次与会人数，供参考</p>
		    <p class="title">如何使用叽歪大屏幕？</p>
			<p>叽歪共享，轻松一步！</p>
            <p class="gray14"><b>现场互动</b></p>
            <p class="text">发送内容到叽歪大屏幕指定短信号码</p>
			<p class="text">即刻让大家在叽歪大屏幕中分享你想说的话</p>
			<p>发短信给叽歪大屏幕，与发短信给普通手机费用完全一样</p>
			<p class="gray14"><b>场外互动</b></p>
			<p class="text">在叽歪网上搜索活动关键字，点击关注按钮</p>
			<p class="text1">即时查看场内互动实况记录，点击回复按钮，即可参与现场互动</p>
			<p class="gray">﹡好消息！ 除了手机短信和登录叽歪网，您还可以通过QQ、MSN、Skype等参与叽歪大屏幕现场互动</p>
			<p class="title">叽歪大屏幕用户反馈</p>
			<p class="text">知名IT评论家Keso如是说：</p>
            <p>“（叽歪大屏幕）是今天最酷的东西。”</p>
			<p class="text">叽歪合作伙伴O'Reilly Press如是说：</p>
            <p>“叽歪大屏幕让人眼前一亮，任何人都能通过叽歪网实时关注查看会场互动动态。”</p>
			<p class="text">2007中文网志年会主办方如是说：</p>
            <p class="text1">“叽歪大屏幕的出现让与会Blogger们兴奋不已，场外参与者的网络留言和短信，也能显示在现场叽歪大屏幕上，演讲者和听众可以马上看到！”</p>
			<p class="text">MAO Live House（新时代原创音乐乐园）如是说：</p>
            <p class="text1">“叽歪大屏幕真得太cool了，屏中不断滚动歌迷们热情洋溢的话语，将现场气氛越推越high，而这一切只需发个短信就可实现。”</p>
			<p class="title">叽歪大屏幕合作联系方式</p>
			<p class="text">邮件：bd*jiwai.com （请将*符号替换为@符号）</p>
            <p>电话：010-58731472-205 王先生</p>
	  </div>
		<div id="wizardPage">
			<ul>
				<li class="pre"><a target="_blank" href="<?php echo JW_SRVNAME . "/wo/account/create";?>"><img src="<?php echo JWTemplate::GetAssetUrl('/images/loginup.jpg');?>" width="85" height="30" border="0" alt="太棒了！现在就注册叽歪网！" title="太棒了！现在就注册叽歪网！"/></a></li>
				<li class="rightnow">或 <a href="<?php echo JW_SRVNAME;?>">返回首页</a> <em class="orange">&raquo;</em></li>
			</ul>
		</div>
	</div><!--wizardContent-->
	<div style="overflow:hidden; clear:both; height:11px; line-height:1px; font-size:1px;"></div>
</div><!--wizardFrame-->
<div id="IndexContainer">
<?php JWTemplate::footer2() ?>
</div>
</body>
</html>
