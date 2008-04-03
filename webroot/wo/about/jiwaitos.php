<? require_once('../../../jiwai.inc.php');?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta content="all" name="robots" />
<title>叽歪网 / 随时随地记录与分享 / 关于叽歪</title>
<link rel="stylesheet" href="<? echo JWTemplate::GetAssetUrl('/css/about.css');?>" type="text/css" media="all"  />
</head>
<body>
 <div class="aboutleft">
  <a title="返回叽歪网首页" href="<? echo JW_SRVNAME;?>"><img class="aboutleft_img" alt="返回叽歪网首页" src="<? echo JWTemplate::GetAssetUrl('/images/logo.gif');?>" /></a>
  <div class="aboutmenu">
    <ul>
      <li class="two"><a title="关于叽歪" href="/wo/about/jiwai">关于叽歪</a></li>
      <li class="two"><a title="团队成员" href="/wo/about/group">团队成员</a></li>
      <li class="two"><a title="联系我们" href="/wo/about/contactus">联系我们</a></li>
      <li class="two"><a title="合作伙伴" href="/wo/about/partner">合作伙伴</a></li>
      <li class="two"><a title="加入我们" href="/wo/about/joinus">加入我们</a></li>
      <li class="one">服务条款</li>
    </ul>
  </div>
  <div class="baodao"><a title="媒体报道" class="blno" href="http://help.jiwai.de/MediaComments" target="_blank">媒体报道</a></div>
 </div>
 
 <div class="aboutright">
   <div class="aboutright_top">
   <?
   if (JWLogin::IsLogined())
   {
	   $current_user_info = JWUser::GetCurrentUserInfo();
	   echo '<strong>你好，</strong><a title="'.$current_user_info['nameFull'].'" class="blno" href="/'.$current_user_info['nameUrl'].'/">'.$current_user_info['nameScreen'].'</a>';
	}
   else
	   echo '<strong>欢迎来到叽歪网，</strong><a title="登录" class="blno" href="/wo/login">登录</a>或<a title="注册" class="blno" href="/wo/account/create">注册</a>';
	?>&nbsp;&nbsp;<img align="middle" src="<? echo JWTemplate::GetAssetUrl('/images/jian.jpg');?>" /><a title="返回首页" class="blno" href="<? echo JW_SRVNAME;?>">返回首页</a></div>
   <div class="aboutrighttop"></div>
   <div class="aboutrightmiddle">
     <h1>服务条款</h1>
     <div class="groupfont">
		<p>
		使用jiwai.de网站服务，您必须先同意遵守以下条款和条件（“使用条款”）。
		</p>
	   <h3>基本条款</h3>
        <p>
    	1. 你必须年满13周岁才能使用本站点。<br>
		2. 对以你的用户名进行的任何活动负责。<br>
		3. 对保护你的密码安全负责。<br>
		4. 不得谩骂，骚扰，威胁，假冒或恐吓其他jiwai.de用户。<br>
		5. 不得使用jiwai.de进行任何非法或未经授权的用途。<br>
		6. 你对你的行为和你在jiwai.de发布、粘贴和展示的任何数据、文本、信息、昵称、图表、照片、文件、音频或视频、链接（“内容”）负完全责任。<br>
		7. 不得修改、攻击jiwai.de，不得修改其他站点以造成它与jiwai.de有关系的假象。<br>
		8. 不得制造或发送不受欢迎的信息(垃圾信息)给任何jiwai.de成员。<br>
		9. 不得传播任何病毒或具有破坏性的代码。<br>
		10. 不得在使用jiwai.de过程中触犯法律（包括但不局限于著作权法）。<br><br>
		违反这些协议中的任何一项将会导致你的jiwai.de帐号被终止。鉴于jiwai.de禁止此类作为和内容在本网站发生，您须理解并认同：你在使用jiwai.de服务时也许会浏览到此类内容，但jiwai.de对发布在本网站的内容不负有责任。
	    </p>
        <h4>综合条款</h4>
        <p>
			1. 我们保留在任何时候、在不通知的情况下、以任何理由修改或终止jiwai.de服务的权利。<br>
			2. 我们保留在任何时候修改使用条款的权利。如果修改的内容造成了使用条款的实质性变化，我们会通过您账户中所提供的电子邮件通知您。造成“实质性变化”的内容一定是经过我们慎重斟酌，经过常识常情的考量和理性判断而做出的决定。<br>
			3. 我们保留在任何时候拒绝为任何人提供服务的权利。<br>
			4. 我们会，但没有义务，删除我们判断为非法、攻击性、恐吓性、诽谤、诬蔑、色情或其他不受欢迎的、侵犯知识产权的、或违背我们的使用条款的内容及包含此类内容的帐号。<br>
			5. jiwai.de为您提供把在本网站图片和文本发布到其他网站的服务。我们接受并鼓励这一用途。但其他网站显示jiwai.de提供的信息的页面必须提供一个返回jiwai的链接。<br>
	    </p>
        
        <h4>版权（你的就是你的）</h4>
        <p>
      		1. 我们不对你在jiwai.de发布的任何资料索要知识产权。你的资料和上传的内容仍属于你。任何时候你都可以删除你的帐户来移除你的资料。这也将删除你在系统内存储的所有文字和图片。<br>
			2. 我们鼓励用户对公共社区贡献他们的创造或考量进一步的许可条款。<br>
			3. Jiwai.de承诺遵守所有的相关版权法律。我们会审查收到的所有侵犯版权申诉并删除发布在jiwai.de的违反这类法律的内容。<span>如要向我们进行版权申诉，请向我们提供以下内容：<br></span>
			
				&nbsp;&nbsp;&nbsp;&nbsp;1). 一份由著作权所有人或其授权的代理人的亲笔签名或电子签名；<br>
				&nbsp;&nbsp;&nbsp;&nbsp;2). 对被侵犯了版权的作品的描述；<br>
				&nbsp;&nbsp;&nbsp;&nbsp;3). 对侵权资料和信息的详细描述，以便jiwai找到该资料；<br>
				&nbsp;&nbsp;&nbsp;&nbsp;4). 你的联系方式，包括住址、电话号码及email；<br>
				&nbsp;&nbsp;&nbsp;&nbsp;5). 一份陈述，说明你认为被你所控诉的资料未经版权所有人及其代理人许可或未经法律允许而被使用；<br>
				&nbsp;&nbsp;&nbsp;&nbsp;6). 一份声明，确认你报告中的信息准确，以及你被授权代表版权所有人。<br>
        </p>
     </div>
     
     
     
   </div>
   <div class="aboutrightbottom"></div>
 </div>
 
  <?
  JWTemplate::footer3();
  ?>
</body>
</html>
