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

$options = array(	 'title'		=> '随时随地记录与分享 / 叽歪网向导 2-4 '
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
			<li class="navOn">2.QQ与MSN聊天</a></li>
			<li class="navOff"><a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_3";?>">3.群发短信</a></li>
			<li class="navOff"><a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_4";?>">4.叽歪大屏幕</a></li>
		</ul>
	</div>
	<div id="wizardContent">
	<div id="wizardPic" class="wizardPic"> <img src="<?php echo JWTemplate::GetAssetUrl('/images/msn_qq.jpg');?>" width="360" height="329" alt="想用QQ和MSN好友聊天？" title="想用QQ和MSN好友聊天？"/></div>
	  <div id="wizardText" class="winzardText">
	    <div class="Intro">
	      <p class="IntroTitle"><span class="IntroNum">2</span>想用QQ和MSN好友聊天？</p>
	    </div>
			<p>你爱用QQ，而他上班时只能开MSN，那可怎么办？</p>
			<p>通过叽歪网，可以轻松<span class="blue">实现MSN和QQ好友聊天！</span></p>
			<p>不止是QQ、MSN，叽歪网支持通过短信、彩信、WAP、飞信、Skype、GTalk、Yahoo!Messenger、AIM、Facebook、水木社区、网页 等多种方式进行交流，他在哪里都跑不掉！</p>
			<p class="note2"><a href="javascript:void(0);" onclick="$('readme').style.display = 'none'==$('readme').style.display ? 'block' : 'none'" class="orange14">如何操作，点击这里查看说明</a></p>
		<div class="details" id="readme" style="display:none;">
		   <p class="detailsPo"><span class="detailsNum orange">1</span><b>注册叽歪网</b></p>
		   <p class="detailsPo"><span class="detailsNum orange">2</span><b>设置QQ/MSN帐号</b> - 在设置/聊天软件中，填写自己的</p>
		   <p class="note3">QQ/MSN帐号，并按照提示操作。</p> 
		   <p class="note5 gray">﹡让你的朋友进行同样的操作</p>
		   <p class="detailsPo"><span class="detailsNum orange">3</span><b>互相关注</b> - 在叽歪网上搜索好友的QQ/MSN帐号，进入好友</p>
		   <p class="note3">主页后，点击页面右上方的“关注此人”。</p>
		   <p class="note5 gray">﹡让你的朋友进行同样的操作</p>
		   <p class="note4">只需三步，就可以通过叽歪网进行跨QQ/MSN聊天啦！就是如此简单！<br/>
		   <span class="gray">﹡发短信给叽歪网，与发短信给普通手机费用完全一样</span></p>
		</div>
  	  </div>
		<div id="wizardPage">
			<ul>
				<li class="pre"><a target="_blank" href="<?php echo JW_SRVNAME . "/wo/account/create";?>"><img src="<?php echo JWTemplate::GetAssetUrl('/images/loginup.jpg');?>" width="85" height="30" border="0" alt="太棒了！现在就注册叽歪网！" title="太棒了！现在就注册叽歪网！"/></a></li>
				<li class="rightnow">或 <a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_3";?>">下一步：群发短信 </a> <em class="orange">&raquo;</em></li>
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
