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

$options = array(	 'title'		=> '随时随地记录与分享 / 叽歪网向导 1-4 '
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
<script language="javascript">
function $(el) {
   return document.getElementById(el);
}
</script>
</head>

<body class="normal" id="front">

<?php JWTemplate::accessibility() ?>

<body>
<div id="IndexContainer">
    <div class="containerL">
    	<div id="jiwaiLogo"><a href="http://JiWai.de/">叽歪网</a></div>
	</div>
		<div class="containerTopR"><img src="<?php echo JWTemplate::GetAssetUrl('/images/tour_03.gif');?>" alt="随时随地记录与分享" title="随时随地记录与分享" width="238" height="39" />
</div>
</div>
<div id="wizardFrame" class="wizardFrame">
	<div id="wizardNav" class="wizardNav">
		<ul>
			<li class="navOff"><a href="<?php echo JW_SRVNAME ."/wo/wizard/";?>"><span class="num">&</span>叽歪网简介</a></li>
			<li class="navOn"><span class="num">1</span>手机博客</li>
			<li class="navOff"><a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_2";?>"><span class="num">2</span>QQ与MSN聊天</a></li>
			<li class="navOff"><a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_3";?>"><span class="num">3</span>群发短信</a></li>
			<li class="navOff"><a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_4";?>"><span class="num">4</span>叽歪大屏幕</a></li>
		</ul>
	</div>
	<div id="wizardContent">
	<div id="wizardPic" class="wizardPic"> <img src="<?php echo JWTemplate::GetAssetUrl('/images/mobile.jpg');?>" width="360" height="315" alt="想用手机写博客，发照片？" title="想用手机写博客，发照片？"/></div>
	  <div id="wizardText" class="winzardText">
	    <div class="Intro"><p class="IntroTitle"><span class="IntroNum">1</span>想用手机写博客，发照片？</p></div>
			<p>叽歪网可以帮助你通过短信、彩信立即书写你的博客！</p>
			<p>脑海中经常浮现的一些转瞬即逝的好点子？立即通过<span class="blue">短信</span>发送到叽歪网。</p>
			<p>在生活中经常看到有趣的场面？手机拍照，立即通过<span class="blue">彩信</span>发送到叽歪网。</p>
			<p class="note2"><a href="javascript:void(0);" onclick="$('readme').style.display = 'none'==$('readme').style.display ? 'block' : 'none'" class="orange14">如何操作，点击这里查看说明</a></p>
		<div class="details" id="readme" style="display:none;">
		   <p class="detailsPo"><span class="detailsNum orange">1</span><b>注册叽歪网</b> - 用手机发送 <span class="blue12">zc+空格+你想要的用户名</span> 到1066808866，例如“zc 美女”，不区分字母大小写
		   <p class="detailsPo"><span class="detailsNum orange">2</span><b>发表文字</b> - 通过短信发送你的话到 1066808866</p> 
		   <p class="detailsPo"><span class="detailsNum orange">3</span><b>发表图片</b> - 通过彩信发送照片到 <span class="blue12">m@jiwai.de</span> <span class="black12"></span><a href="http://help.jiwai.de/MMS" target="_blank"><img src="<?php echo JWTemplate::GetAssetUrl('/images/icon.gif');?>" border="0" /></a><span class="black12"></span></p>
		   <p class="note4">只需三步，就可以随时记录你的话和你的照片，并分享给大家，就是这么简单！<br/>
		   <span class="gray">﹡发短信给叽歪网，与发短信给普通手机费用完全一样</span></p>
		</div>
			<p class="black12 gray"> 你还可在博客中嵌入搞笑的视频，分享美妙的音乐。<a target="_blank" href="http://blog.jiwai.de/index.php/archives/25"><img src="<?php echo JWTemplate::GetAssetUrl('/images/icon.gif');?>" border="0" /></a></p>
  	  </div>
		<div id="wizardPage">
			<ul>
				<li class="pre"><a target="_blank" href="<?php echo JW_SRVNAME . "/wo/account/create";?>"><img src="<?php echo JWTemplate::GetAssetUrl('/images/loginup.jpg');?>" width="85" height="30" border="0" alt="太棒了！现在就注册叽歪网！" title="太棒了！现在就注册叽歪网！"/></a></li>
				<li class="rightnow">或 <a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_2";?>">下一步：QQ与MSN聊天</a> <em class="orange">&raquo;</em></li>
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
