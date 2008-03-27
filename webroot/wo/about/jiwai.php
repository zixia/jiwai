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
      <li class="one">关于叽歪</li>
      <li class="two"><a title="团队成员" href="/wo/about/group">团队成员</a></li>
      <li class="two"><a title="联系我们" href="/wo/about/contactus">联系我们</a></li>
      <li class="two"><a title="合作伙伴" href="/wo/about/partner">合作伙伴</a></li>
      <li class="two"><a title="加入我们" href="/wo/about/joinus">加入我们</a></li>
      <li class="two"><a title="服务条款" href="/wo/about/jiwaitos">服务条款</a></li>
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
     <h1>关于叽歪</h1>
     <div class="groupfont">
       <h3>叽歪网</h3>
       <div class="aboutme">
你是否问过或被别人问过"你在做什么"这样的问题？你是否想用两句话记录生活，懒得去写那长篇大论的博客？你是否在街上看到有趣的东西希望马上分享给朋友？<br />
于是我们设想了这么一个<strong>地方</strong>，你可以在它上面看到朋友们的状态，了解他们此时正在做的事情，感受他们的喜怒哀乐，分享他们的所见所闻。甚至，这些信息能够传送给你手机或QQ上面。直到你也会通过网页，短信，MSN把消息发送到这个<strong>地方</strong>，发送你的生活轨迹。于是这些只言片语便是朋友间联系的纽带。<br />
这就是叽歪网，我们为你创立的这样一个地方。
       
       </div>
        <img alt="关于叽歪网" src="<? echo JWTemplate::GetAssetUrl('/images/aboutme.jpg');?>" title="关于叽歪网"/>
        <div class="clear"></div>
        <h4>什么是叽歪网</h4>
        <p>
        叽歪网，是一个通过发送生活中的点滴消息，和朋友、亲人、同事以及陌生人相互交流以及保持联系的网站；<br />
        叽歪网致力于为用户打造通过手机、QQ、MSN等不同终端设备实现随时随地记录与分享的网络平台。<br />
        你可以从这里<a title="了解叽歪网" href="/wo/wizard/" target="_blank" >了解叽歪网</a>，或者读读<a title="我们的博客" target="_blank" href="http://blog.jiwai.de/">我们的博客</a>
        </p>
        
        <h4>我们的目标</h4>
        <p>
        叽歪网要成为最好的微型博客（mircoblogging）平台，使更多的人了解微信息的意义，使用它记录生命之流
（Life-streaming）。<br />
        叽歪网推进了IM2.0的发展，即多种聊天工具（IM）的多方式的互通与互联，使不同平台，不同工具下的人们交流更加便利。<br/>
推出以用户创造价值为中心的SMS2.0服务，使其成为以文字见长的博客、声音视频见长的播客之后另一种全新的
自我展现平台，实现多方式订阅与交流。<br />
        应用叽歪大屏幕专利技术为用户提供功能强大，性价比高的网络直播、现场互动平台。
        </p>
                
                
         <h4>更多信息</h4>
        <p>
        叽歪网是一个开放的网站，拥有丰富的<a title="API" href="http://help.jiwai.de/API" target="_blank">API</a>接口，愿意和各种网站合作。<br/>
叽歪大屏幕是一个基于短信与各种IM工具交互的互动营销平台，是能够提升用户观看演出、出席活动、参与会议等的最佳互动平台，是最具创新的传播平台。

       </p>
       
        <h5>欢迎下载</h5>
        <p>
          <img style="float:none;margin:0px 6px 0px 0px;" align="absmiddle" src="<? echo JWTemplate::GetAssetUrl('/images/pdf.jpg');?>" /> <a title="叽歪网IM2.0&SMS2.0接入方案" href="<? echo JWTemplate::GetAssetUrl('/js/jiwai_im2_sm2_20080303.pdf');?>"> 叽歪网IM2.0&SMS2.0接入方案</a>
         </p><p style="margin-top:10px;"> <img style="float:none;margin:0px 6px 0px 0px;" align="absmiddle" src="<? echo JWTemplate::GetAssetUrl('/images/pdf.jpg');?>" /> <a title="叽歪大屏幕介绍" href="<? echo JWTemplate::GetAssetUrl('/js/jiwai_bigscreen_20080314.pdf');?>">叽歪大屏幕介绍</a>
       </p>
         <div class="forn"><a class="blno" href="/wo/about/contactus" title="欢迎联系我们">欢迎联系我们</a></div>	   
        
     </div>
     
     
     
   </div>
   <div class="aboutrightbottom"></div>
 </div>
 
  <?
  JWTemplate::footer3();
  ?>
</body>
</html>
