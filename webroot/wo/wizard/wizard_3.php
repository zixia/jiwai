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

$options = array(	 'title'		=> '随时随地记录与分享 / 叽歪网向导 3-4 '
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
    	<div id="jiwaiLogo"><a href="http://JiWai.de/">叽歪网</a></div>
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
			<li class="navOn">3.群发短信</a></li>
			<li class="navOff"><a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_4";?>">4.叽歪大屏幕</a></li>
		</ul>
	</div>
	<div id="wizardContent">
	<div id="wizardPic" class="wizardPic"> <img src="<?php echo JWTemplate::GetAssetUrl('/images/sms.jpg');?>" width="360" height="310" alt="想方便的免费群发短消息？" title="想方便的免费群发短消息？"/></div>
	  <div id="wizardText" class="winzardText">
	    <div class="Intro">
	      <p class="IntroTitle"><span class="IntroNum">3</span>想方便的免费群发短消息？</p>
	    </div>
			<p>叽歪网可以帮你将消息通过 <img src="<?php echo JWTemplate::GetAssetUrl('/images/jiwai-sms.gif');?>" width="16" height="16" alt="手机短信" title="手机短信"/> 手机短信、<img src="<?php echo JWTemplate::GetAssetUrl('/images/jiwai-qq.gif');?>" width="16" height="16" alt="QQ" title="QQ"/> QQ、<br />
		    <img src="<?php echo JWTemplate::GetAssetUrl('/images/jiwai-msn.gif');?>" width="16" height="16" alt="MSN" title="MSN"/> MSN、<img src="<?php echo JWTemplate::GetAssetUrl('/images/jiwai-skype.gif');?>" width="16" height="16" alt="Skype" title="Skype"/> Skype等，免费群发给你朋友们！</p>
			<p>只需让朋友们发送一条确认短信到叽歪网，你就可以通过网页免费发送消息给他们。</p>
			<p class="note2"><a href="javascript:void(0);" onclick="$('readme').style.display = 'none'==$('readme').style.display ? 'block' : 'none'" class="orange14">如何操作，点击这里查看说明</a></p>
		<div class="details" id="readme" style="display:none;">
		   <p class="detailsPo"><span class="detailsNum orange">1</span><b>注册叽歪网</b> - 假设你的用户名为“jiwai”</p>
		   <p class="detailsPo"><span class="detailsNum orange">2</span><b>设置群发关系</b> - 让朋友发送 <span class="blue12">on+空格+你的用户名</span> 到106693184</p>
		   <p class="note3">例如你注册的用户名是jiwai，则叫你的朋友发送“on jiwai”，不区分字母大小写
		   <p class="note5 gray">﹡发短信给叽歪网，与发短信给普通手机费用完全一样</p>
		   <p class="note6"><a href="http://help.jiwai.de/WizardIM" target="_blank" class="gray12">点击了解如何发给QQ/MSN</a></p>
		   <p class="detailsPo"><span class="detailsNum orange">3</span><b>群发消息</b> - 在叽歪网上发送你的消息，你的朋友们即可收到通知短信了！</p>
		   <p class="note4">只需三步，即可完成群发消息设置。一切就是这么简单！<br/>
		</div>
  	  </div>
		<div id="wizardPage">
			<ul>
				<li class="pre"><a target="_blank" href="<?php echo JW_SRVNAME . "/wo/account/create";?>"><img src="<?php echo JWTemplate::GetAssetUrl('/images/loginup.jpg');?>" width="85" height="30" border="0" alt="太棒了！现在就注册叽歪网！" title="太棒了！现在就注册叽歪网！"/></a></li>
				<li class="rightnow">或 <a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_4";?>">下一步：叽歪大屏幕</a> <em class="orange">&raquo;</em></li>
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
