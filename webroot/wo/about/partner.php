<? require_once('../../../jiwai.inc.php');?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta content="all" name="robots" />
<title>叽歪网 / 随时随地记录与分享 / 合作伙伴</title>
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
      <li class="one">合作伙伴</li>
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
   <div class="partnercss">
     <h1>合作伙伴</h1>
     <div class="friendlink">
       <ul>
          <li><a href="http://www.xiaoi.com" title="小i机器人" target="_blank"><img align="midddle" alt="小i机器人" src="<? echo JWTemplate::GetAssetUrl('/images/fei3.jpg');?>" /></a></li>
         <li><a href="http://www.csdn.net" title="csdn" target="_blank"><img align="midddle" alt="csdn" src="<? echo JWTemplate::GetAssetUrl('/images/fei5.jpg');?>" /></a></li>
         <li><a href="http://cn.widsets.com/me.html" title="维基" target="_blank"><img align="midddle" alt="维基" src="<? echo JWTemplate::GetAssetUrl('/images/fei6.jpg');?>" /></a></li>
         <li><a href="http://www.youku.com" title="优酷" target="_blank"><img align="midddle" alt="优酷" src="<? echo JWTemplate::GetAssetUrl('/images/fei7.jpg');?>" /></a></li>
         </ul>
       <ul>
         <li><a href="http://www.ccw.com.cn" title="计算机世界" target="_blank"><img align="midddle" alt="计算机世界" src="<? echo JWTemplate::GetAssetUrl('/images/fei8.jpg');?>" /></a></li>
         <li><a href="http://www.wealink.com" title="we@link" target="_blank"><img align="midddle" alt="we@link" src="<? echo JWTemplate::GetAssetUrl('/images/fei9.jpg');?>" /></a></li>
         <li><a href="http://www.qian8ao.com" title="钱包网" target="_blank"><img align="midddle" alt="钱包网" src="<? echo JWTemplate::GetAssetUrl('/images/fei10.jpg');?>" /></a></li>
         <li><a href="http://www.blogbus.com" title="博客大巴" target="_blank"><img align="midddle" alt="博客大巴" src="<? echo JWTemplate::GetAssetUrl('/images/fei12.jpg');?>" /></a></li>
         </ul>
        <ul>
         <li><a href="http://www.feedsky.com" title="feedsky" target="_blank"><img align="midddle" alt="feedsky" src="<? echo JWTemplate::GetAssetUrl('/images/fei14.jpg');?>" /></a></li>
         <li><a href="http://www.mycaifu.com" title="my财富" target="_blank"><img align="midddle" alt="my财富" src="<? echo JWTemplate::GetAssetUrl('/images/mycaifu.gif');?>" /></a></li>
         <li><a href="http://maxtv.cn" title="迈视传媒" target="_blank"><img align="midddle" alt="迈视传媒" src="<? echo JWTemplate::GetAssetUrl('/images/flink/maxtv153x79.jpg');?>" /></a></li>
         <li ><a href="http://www.xcmlm.com" title="新媒体产业联盟" target="_blank"><img style="width:79px;" align="midddle" alt="新媒体产业联盟" src="<? echo JWTemplate::GetAssetUrl('/images/flink/xcmlm79x79.jpg');?>" /></a></li>
		 </ul>
		 <ul>
         <li><a href="http://www.51fayan.com/" title="中国发言人网" target="_blank"><img align="midddle" alt="中国发言人网" src="<? echo JWTemplate::GetAssetUrl('/images/flink/51fayan.jpg');?>" /></a></li>
         <li><a href="http://www.cctn.info/" title="中国商务新闻网" target="_blank"><img align="midddle" alt="中国商务新闻网" src="<? echo JWTemplate::GetAssetUrl('/images/flink/shangwu.jpg');?>" /></a></li>
         <li></li>
         <li></li>
       </ul>
       
        <p style="float:left;width:100%;text-align:right;margin-top:13px;font-size:1.2em;color:#545454">合作伙伴排名不分先后</p>
     </div>

 <div class="friendlink">
	<div class="groupfont"><br/>
	 <h4>友情链接</h4>
	 </div>
     <div class="friendlink">
     <a href="http://www.8box.cn" title="音乐八宝盒" target="_blank"><img width="88" height="31" src="<? echo JWTemplate::GetAssetUrl('/images/8box8831.gif');?>" alt="音乐八宝盒"></a>
	<a href="http://www.yupoo.com" title="又拍网" target="_blank"><img width="88" height="31" src="<? echo JWTemplate::GetAssetUrl('/images/yupoo8831.gif');?>" alt="又拍网"></a>
	<a href="http://www.xianguo.com" title="鲜果" target="_blank"><img width="88" height="31" src="<? echo JWTemplate::GetAssetUrl('/images/xianguo8831.gif');?>" alt="鲜果"></a>
	<a href="http://www.fundodo.com" title="粉嘟嘟" target="_blank"><img width="88" height="31" src="<? echo JWTemplate::GetAssetUrl('/images/fundodo8831.gif');?>" alt="粉嘟嘟"></a>
	<a href="http://www.inezha.com" title="哪吒网" target="_blank"><img width="88" height="31" src="<? echo JWTemplate::GetAssetUrl('/images/inezha8831.gif');?>" alt="哪吒网"></a>
	<a href="http://ow.blog.sohu.com/" title="搜狐博客开放平台" target="_blank"><img width="88" height="31" src="<? echo JWTemplate::GetAssetUrl('/images/sohublog8831.jpg');?>" alt="搜狐博客开放平台"></a>
	<a href="http://www.kaluu.com/" title="开路网" target="_blank"><img width="88" height="31" src="<? echo JWTemplate::GetAssetUrl('/images/flink/kaluu8831.gif');?>" alt="开路网"></a>
	<a href="http://www.dvod.com.cn/ " title="新世界网" target="_blank"><img width="88" height="31" src="<? echo JWTemplate::GetAssetUrl('/images/flink/dvod8831.gif');?>" alt="新视界网"></a>
	<a href="http://gocom.primeton.com/" title="构客网" target="_blank"><img width="88" height="31" src="<? echo JWTemplate::GetAssetUrl('/images/flink/gocom8831.gif');?>" alt="构客网"></a>
	<a href="http://h863.com/" title="健康863" target="_blank"><img width="88" height="31" src="<? echo JWTemplate::GetAssetUrl('/images/flink/h8638831.gif');?>" alt="健康863"></a>
	<a href="http://wed.la/" title="结婚啦" target="_blank"><img width="88" height="31" src="<? echo JWTemplate::GetAssetUrl('/images/flink/wed_la.jpg');?>" alt="结婚啦"></a>
	<a href="http://www.babytree.com/" title="育儿网站宝宝树" target="_blank"><img width="88" height="31" src="<? echo JWTemplate::GetAssetUrl('/images/flink/babytree8831.gif');?>" alt="育儿网站宝宝树"></a>
	<a href="http://www.cayie.com/" title="凯羿娱浪潮" target="_blank"><img width="88" height="31" src="<? echo JWTemplate::GetAssetUrl('/images/flink/cayie8831.gif');?>" alt="凯羿娱浪潮"></a>
	<a href="http://www.pindao.com/" title="品道" target="_blank"><img width="88" height="31" src="<? echo JWTemplate::GetAssetUrl('/images/flink/pindao8831.gif');?>" alt="品道"></a>
	<a href="http://www.cn939.com/" title="中国中医网" target="_blank"><img width="88" height="31" src="<? echo JWTemplate::GetAssetUrl('/images/flink/yiyao8831.gif');?>" alt="中国中医网"></a>
	<a href="http://www.ipart.cn/" title="爱情公寓" target="_blank"><img width="88" height="31" src="<? echo JWTemplate::GetAssetUrl('/images/flink/ipart8831.gif');?>" alt="爱情公寓"></a>
	<a href="http://www.artspy.cn/" title="艺术眼" target="_blank"><img width="88" height="31" src="<? echo JWTemplate::GetAssetUrl('/images/flink/artspy8831.jpg');?>" alt="艺术眼"></a>
	<p style="float:left;width:100%;text-align:right;margin-top:13px;font-size:1.2em;color:#545454">友情链接排名不分先后</p>
	 </div>
 </div>
     <div class="groupfont">
      
       
        <h4>链接要求</h4>
       <div class="fontlist">
         <ul>
           <li>违反我国现行法律的或含有令人不愉快内容的网站勿扰；</li>
           <li>网站alexa排名不低于10000名；</li>
           <li>站点 google pagerank 不少于4 ；</li>
           <li>友情链接网站之间有义务向对方报告链接失效，图片更新等问题，在解除友情链接之前亦应该通知对方；</li>
         </ul>
       </div>
       
        <p style="margin-top:10px;">以上各项，叽歪网保留全部解释权。</span></p>
      
        <h4>本站Logo</h4>
       <div class="logoimg">
         <ul>
           <li class="one"><img alt="Logo大小：88×31"  src="<? echo JWTemplate::GetAssetUrl('/images/logo1.gif');?>" /></li>
           <li class="two">Logo大小：88×31</li>
         </ul>
         <ul>
           <li class="one" style="padding:1px 0px;"><img alt="Logo大小：120×60"  src="<? echo JWTemplate::GetAssetUrl('/images/logo2.gif');?>" /></li>
           <li class="two">Logo大小：120×60</li>
         </ul>
       </div>
                
               
         <h4>合作联系方式</h4>
        <p>
        业务合作请发送至：bd [at] jiwai.com （请把[at]改成@）<br />
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
