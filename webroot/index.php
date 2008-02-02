<?php
require_once('../jiwai.inc.php');

if ( JWLogin::IsLogined() )
	header('Location: /wo/');

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

$options = array(	 'title'		=> '随时随地记录与分享'
					,'keywords'		=> htmlspecialchars($keywords)
					,'description'	=> htmlspecialchars($description)
					,'author'		=> htmlspecialchars($keywords)
					,'rss_url'		=> JW_SRVNAME .'/status/public_timeline.rss'
					,'rss_title'	=> '叽歪网 - 叽歪广场 [RSS]'
					,'refresh_url'	=> ''
					,'version_css_jiwai_screen'	=> 'v2'
					,'is_load_all'	=> 'false'
			);

?>
<head>
<?php JWTemplate::html_head($options) ?>
</head>

<body>

<?php JWTemplate::accessibility() ?>

<body>
<div id="IndexContainer">
    <div class="containerL">
    	<div id="jiwaiLogo"><a href="http://JiWai.de/">叽歪网</a></div>
	    <div id="flash">&nbsp;<br />
<!--影片中使用的 URL-->
<!--影片中使用的文本-->
<script language="javascript">
	if (AC_FL_RunContent == 0) {
		alert("此页需要 AC_RunActiveContent.js");
	} else {
		AC_FL_RunContent(
			'codebase', 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0',
			'width', '520',
			'height', '285',
			'src', 'map',
			'quality', 'high',
			'pluginspage', 'http://www.macromedia.com/go/getflashplayer',
			'align', 'middle',
			'play', 'true',
			'loop', 'true',
			'scale', 'showall',
			'wmode', 'transparent',
			'devicefont', 'false',
			'id', 'map',
			'bgcolor', '#cccccc',
			'name', 'map',
			'menu', 'true',
			'allowFullScreen', 'false',
			'allowScriptAccess','sameDomain',
			'movie', 'map',
			'salign', ''
			); //end AC code
	}
</script>
<noscript>
	<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="520" height="285" id="map" align="middle">
	<param name="allowScriptAccess" value="sameDomain" />
	<param name="allowFullScreen" value="false" />
	<param name="movie" value="<?php echo JWTemplate::GetAssetUrl('/js/map.swf');?>" /><param name="wmode" value="transparent" /><param name="quality" value="high" /><param name="bgcolor" value="#cccccc" />	<embed src="<?php echo JWTemplate::GetAssetUrl('/js/map.swf');?>" quality="high" bgcolor="#cccccc" width="520" height="285" name="map" align="middle" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" wmode="transparent"/>
	</object>
</noscript>
		</div>
		<ul>
		<?php
		$style_mouseover = "this.style.backgroundColor='#F2F2F2';this.style.cursor='pointer';";
		$style_mouseout = "this.style.backgroundColor='#FFFFFF'";
		?>
		    <li class="icon1" onMouseOver="<?php echo $style_mouseover;?>" onclick="location.href='<?php echo JW_SRVNAME ."/wo/wizard/wizard_1";?>'" onMouseOut="<?php echo $style_mouseout;?>">
			    <h1>想用手机写博客，发照片？</h1>
				<p><a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_1";?>" class="blue">立即体验>></a></p>
			</li>
		    <li class="icon2" onMouseOver="<?php echo $style_mouseover;?>" onclick="location.href='<?php echo JW_SRVNAME ."/wo/wizard/wizard_2";?>'" onMouseOut="<?php echo $style_mouseout;?>">
			    <h1>想用<span>QQ</span>和<span>MSN</span>好友聊天？</h1>
				<p><a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_2";?>" class="blue">立即体验>></a></p>
			</li>
		    <li class="icon3" onMouseOver="<?php echo $style_mouseover;?>" onclick="location.href='<?php echo JW_SRVNAME ."/wo/wizard/wizard_3";?>'" onMouseOut="<?php echo $style_mouseout;?>">
			    <h1>想方便的免费群发短消息？</h1>
				<p><a href="<?php echo JW_SRVNAME ."/wo/wizard/wizard_3";?>" class="blue">立即体验>></a></p>
			</li>
		</ul>
	</div>
	<div class="containerR">
	    <div id="login">
            <img src="<?php echo JWTemplate::GetAssetUrl('/images/text.gif');?>" alt="随时随地记录与分享" title="随时随地记录与分享" width="130" height="66" />
			<h1>登录到叽歪
			</h1><ul>
			<form id="f" name="f" method="post" action="<?php echo JW_SRVNAME . "/wo/login";?>">
				<li class="wid1"><label for="username_or_email">用户名</label></li>
				<li class="wid2">
				  <input id="username_or_email" name="username_or_email" type="text" onblur="this.className=this.className.replace(/\bfocus\b/,'')" onfocus="this.className+=' focus'"/>
				</li>
				<li class="wid1"><label for="password">密<span>码</span></label></li>
				<li class="wid2">
				  <input id="password" name="password" type="password" onblur="this.className=this.className.replace(/\bfocus\b/,'')" onfocus="this.className+=' focus'"/>
				</li>
				</label>
				<li class="wid1"></li>
				<li class="wid3">
				<label for="remember_me">
				<input id="remember_me" name="remember_me" type="checkbox" value="checkbox" />在这台电脑上记住我</li>
				</label>
				<li class="wid1"></li>
				<li class="wid4">
				  <input name="Submit" type="submit" class="closebutton" value="登 录" />
			</form>
				<li class="wid1"></li>
				<li class="wid5"><a target="_blank" href="<?php echo JW_SRVNAME . "/wo/account/create";?>">注册</a><a target="_blank" href="<?php echo JW_SRVNAME . "/public_timeline/";?>">随便逛逛</a><a target="_blank" href="<?php echo JW_SRVNAME . "/wo/wizard/";?>">了解更多>></a></li>
			</ul>
		</div>
		<div id="search">
		<?php $search_tips = "名字，Email，QQ号码，MSN帐号等";?>
						<form id="f2" name="f2" method="get" action="<?php echo JW_SRVNAME . "/wo/search/users";?>" onsubmit="if($('search_user').value=='<?php echo $search_tips;?>')$('search_user').value='';$('f2').target='_blank';">
		    <p align="center"><input type="text" id="search_user" class="searchBox1" onblur="this.className=this.className.replace(/\bfocus\b/,'');if(this.value=='')this.value='<?php echo $search_tips;?>';" onfocus="this.className+=' focus';if(this.value=='<?php echo $search_tips;?>')this.value=''" value="<?php echo $search_tips;?>" name="q"/>
		    <p><input type="submit" class="submitbutton" value="寻找好友" />

		    </p>
			</form>
	  </div>
	</div>
<?php JWTemplate::footer2() ?>
</div>
</body>
</html>
