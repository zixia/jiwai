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

$options = array(	 'title'		=> '随时随地记录与分享 / 叽歪网向导 0-4 '
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
			<li class="navOn"><span class="num">&</span>叽歪网简介</li>
			<li class="navOff"><a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_1";?>"><span class="num">1</span>手机博客</a></li>
			<li class="navOff"><a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_2";?>"><span class="num">2</span>QQ与MSN聊天</a></li>
			<li class="navOff"><a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_3";?>"><span class="num">3</span>群发短信</a></li>
			<li class="navOff"><a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_4";?>"><span class="num">4</span>叽歪大屏幕</a></li>
		</ul>
	</div>
	<div id="wizardContent">
	<div id="wizardPic" class="wizardPic"><img src="<?php echo JWTemplate::GetAssetUrl('/images/JiwaiIntro.jpg');?>" width="360" height="176" alt="叽歪网简介" title="叽歪网简介"/></div>
	  <div id="wizardText" class="winzardText">
	    <div class="Intro">
	      <p class="IntroTitle"><span class="IntroNum">&</span>叽歪网简介</p>
	    </div>
		    <p>叽歪网，是一个通过发送生活中的点滴消息，和朋友、亲人、同事以及陌生人相互交流以及保持联系的社区。</p>
			<p>你可以用手机短信、QQ、MSN、Skype等十余种即时通讯工具发送和接收。</p>
			<p>个性化的服务、简洁易用的操作、强大实用的功能，只为一个宗旨，那就是“<span class="blue">随时随地记录与分享！</span>”</p>
			<p class="note"><strong>点击了解四大叽歪网特色功能：</strong></p>
			<p class="note1"><span class="num">1</span> <a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_1";?>" class="gray">手机博客</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="num">2</span> <a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_2";?>" class="gray">QQ与MSN聊天</a><br />
		<span class="num">3</span> <a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_3";?>" class="gray">群发短信</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="num">4</span> <a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_4";?>" class="gray">叽歪大屏幕</a></p>
  	  </div>
		<div id="wizardPage">
			<ul>
				<li class="pre"><a target="_blank" href="<?php echo JW_SRVNAME . "/wo/account/create";?>"><img src="<?php echo JWTemplate::GetAssetUrl('/images/loginup.jpg');?>" width="85" height="30" border="0" alt="太棒了！现在就注册叽歪网！" title="太棒了！现在就注册叽歪网！"/></a></li>
				<li class="rightnow">或 <a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_1";?>">下一步：手机博客</a> <em class="orange">&raquo;</em></li>
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
