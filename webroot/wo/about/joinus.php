<? require_once('../../../jiwai.inc.php');?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta content="all" name="robots" />
<title>叽歪 / 随时随地记录与分享 / 加入我们</title>
<link rel="stylesheet" href="<? echo JWTemplate::GetAssetUrl('/css/about.css');?>" type="text/css" media="all"  />
</head>
<body>
 <a name="top"></a>
 <div class="aboutleft">
  <a title="返回叽歪网首页" href="<? echo JW_SRVNAME;?>"><img class="aboutleft_img" alt="返回叽歪网首页" src="<? echo JWTemplate::GetAssetUrl('/images/logo.gif');?>" /></a>
  <div class="aboutmenu">
    <ul>
      <li class="two"><a title="关于叽歪" href="/wo/about/jiwai">关于叽歪</a></li>
      <li class="two"><a title="团队成员" href="/wo/about/group">团队成员</a></li>
      <li class="two"><a title="联系我们" href="/wo/about/contactus">联系我们</a></li>
      <li class="two"><a title="合作伙伴" href="/wo/about/partner">合作伙伴</a></li>
      <li class="one">加入我们</li>
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
     <h1>加入我们</h1>
     <div class="groupfont">
      <p class="joinus">
     我们是一个开放、激情、快乐、坚持的团队<br />
     我们的宗旨是：快乐工作，快乐生活<br />
       </p>
       
        <h4>我们有</h4>
        <p>
        <span>舒适的工作环境</span> - 超大办公室、全幅落地窗，让你在工作时也能享受阳光的温暖；<br />
      <span>  年轻的工作团队 </span>- 迅速、高效，时刻都能碰撞出灵感的火花；。<br />
       <span> 平等的工作氛围 </span>- 宽松的环境、热烈的讨论，每个人的意见都至关重要；<br />
      <span>  丰富的学习机会 </span>- 浓烈的技术氛围、积极的工作态度，叽歪和你共同成长；
        </p>
                
         <h4>如果你</h4>
        <p>
        对互联网行业充满激情，善于沟通，勇于创新，对学习抱有永远的兴趣，那么我们欢迎你用任何方式加入我们
我们能给你提供：一份事业、一群朋友、一场精彩的生活
       </p>
       
       <div class="mecss">
         <img alt="关于我们" title="关于我们" src="<? echo JWTemplate::GetAssetUrl('/images/aboutme1.jpg');?>" />
         <img alt="关于我们" title="关于我们" src="<? echo JWTemplate::GetAssetUrl('/images/aboutme2.jpg');?>" />
         <img alt="关于我们" title="关于我们" src="<? echo JWTemplate::GetAssetUrl('/images/aboutme3.jpg');?>" />
         <img alt="关于我们" title="关于我们" src="<? echo JWTemplate::GetAssetUrl('/images/aboutme4.jpg');?>" />
         <img alt="关于我们" title="关于我们" src="<? echo JWTemplate::GetAssetUrl('/images/aboutme5.jpg');?>" />
       </div>
       
        <p>&nbsp;</p>
		<h1>招聘职位</h1>
        <p>
       <span>我们需要以下人才加盟，如果你很喜欢叽歪，又符合条件，那就赶快联系我们吧！(注：本招聘长期有效)</span>
       </p>
       <div class="fontlist">
         <ul>
           <li>Web Geeks </li>
           <li>网页设计师（中高级）</li>
           <li>网站编辑 </li>
           <li>叽歪达人 </li>
           <li>销售工程师</li>
         </ul>
       </div>
       
       <div class="groupfonth2">
         <span>Web Geeks</span>
         <a title="↑返回" href="#top">↑返回</a>
       </div>
       <p>
       <span>职位说明：</span><br />
       1. 对现有网站功能进行优化和完善；<br />
       2. 完成开发网站新功能开发；<br />
      <span> 职位要求：</span><br />
       1. 工作认真负责，善于沟通，乐于互助；<br />
       2. 热爱创造，对性能、代码、架构设计或者其他有关美感的东西（套装和天赋点数也算吗？...-_-!）有一点偏执；<br />
       3. 熟悉PHP，熟悉javascript；<br />
       4. 有 mysql L经验，熟悉LAMP开发；<br />
       5. 熟悉代码版本管理工具的使用；<br />
       6. 了解Linux基本使用，能够在Linux环境下流畅工作；<br />
       7. 可以兼职或实习，需要能够尽快开始svn ci；<br /><br />
      <span> 特别欢迎以下标签云：</span><br />
       Smarty, zendengine, lighty, AIR, mootools, cross_browser, opensource,<br />
       memcached, replication, partition, comet, semantic_web, microformats
       </p>
       
        <div class="groupfonth2">
         <span>网页设计师（中高级）</span>
         <a title="↑返回" href="#top">↑返回</a>
       </div>
       <p>
       <span>职位说明</span><br />
       1. 根据产品需求，进行创意设计、布局、配色，完成优秀的设计图；<br />
       
2. 同团队一起创建大气的 Web 2.0 网站，并对其进行不断优化、改进；<br />
       <span>职位要求</span><br />
       1. 具有良好的创意设计能力，对色彩敏感，具有把握不同风格页面的能力；<br />
       2. 了解 Web 2.0 网站的设计模式，并对不断挑战自我有着不懈的努力；<br />
       3. 对用户体验，web可用性有一定的兴趣；<br />
       4. 精通Photoshop、Dreamweaver、Html、JS、CSS、切图；<br />
       5. 崇尚高效，具备良好的个人品质与职业素养；<br />
       6. 具有团队合作精神、良好的沟通技巧力和较强的责任心；<br />
       <span><code>*</code> 了解网站制作的执行流程及规范，有大型门户网站制作经验者优先考虑；<span>
       <p>
	   
        <div class="groupfonth2">
         <span>网站编辑</span>
         <a title="↑返回" href="#top">↑返回</a>
       </div>
       <p>
       <span>职位说明：</span><br />
       1. 维护叽歪社区在线资料（帮助文档等）；<br />
       2. 维护叽歪网社区，帮助引导用户使用叽歪网；<br />
       3. 同团队一起创建大气的 Web 2.0 网站，能提出自己的见解和解决策略；<br />
       <span>职位要求：</span><br />
       1. 熟悉中文互联网，熟悉宿舍网或校园BBS；<br />
       2. 了解 Web 2.0 网站，有敏锐的观察能力，高度的协作精神；<br />
       3. 沟通能力强，性格外向，打字速度快；<br />
       4. 具有一定的文字功底<br />
       5. 能够很快融入团队,具有团队协作精神<br />
       6. 做事认真、细心，责任感强<br />
       7. 具有较强的学习能力和较前的独立处理事务的能力<br />
       <span><code>*</code> 有相关工作经验者优<span>
       <p>
	   
        <div class="groupfonth2">
         <span>实习：叽歪达人</span>
         <a title="↑返回" href="#top">↑返回</a>
       </div>
       <p>
       <span>职位说明：</span><br />
       1. 维护叽歪网社区，帮助引导用户使用叽歪网；<br />
       2. 同团队一起创建大气的 Web 2.0 网站，能提出自己的见解和解决策略；<br />
       <span>职位要求：</span><br />
       1. 熟悉中文互联网，熟悉宿舍网或校园BBS；<br />
       2. 了解 Web 2.0 网站，有敏锐的观察能力，高度的协作精神；<br />
       3. 沟通能力强，性格外向最佳；<br />
       <span><code>*</code> 我们会为你开据正规的实习证书<span>
       <p>
	   
        <div class="groupfonth2">
         <span>销售工程师</span>
         <a title="↑返回" href="#top">↑返回</a>
       </div>
       <p>
       <span>职位说明：</span><br />
       1. 全职或兼职，工作地点在北京，以及其他大中城市；<br />
       2. 通过电话、互联网以及面对面沟通开发客户与销售渠道；<br />
       3. 商务谈判与合同签订；<br />
       4. 产品服务跟踪，增长客户满意度；<br />
       5. 寻找销售机会，建立潜在的客户资源网络。<br />
       <span>职位要求：</span><br />
       1. 良好的沟通及表达表达能力，思维敏捷、逻辑性强；<br />
       2. 热爱接受新事物、具备创意思维；<br />
       3. 善于协调公司资源，能够独立完成销售项目；<br />
       4. 熟练使用各类常用办公软件。<br />
       <span><code>*</code> 具备大型会展及活动策划、广告媒体、演艺娱乐类工作经验者，优先考虑。<br/>
       <code>*</code> 应聘销售工程师者，除发邮件到 hr [at] jiwai.com （请把[at]改成@），可电话联系 010-58731472-208<span><br/><br/>
	   加入我们吧！请将简历发往： hr [at] jiwai.com （请把[at]改成@） ，邮件标题注明"【应聘某职位】"，我们虚席以待！
       <p>       
        
     </div>
     
   </div>
   <div class="aboutrightbottom"></div>
 </div>
 
  <?
  JWTemplate::footer3();
  ?>
</body>
</html>
