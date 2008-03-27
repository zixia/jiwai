<? require_once('../../../jiwai.inc.php');?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta content="all" name="robots" />
<title>叽歪网 / 随时随地记录与分享 / 联系我们</title>
<link rel="stylesheet" href="<? echo JWTemplate::GetAssetUrl('/css/about.css');?>" type="text/css" media="all"  />
</head>
<body>
 <div class="aboutleft">
  <a title="返回叽歪网首页" href="<? echo JW_SRVNAME;?>"><img class="aboutleft_img" alt="返回叽歪网首页" src="<? echo JWTemplate::GetAssetUrl('/images/logo.gif');?>" /></a>
  <div class="aboutmenu">
    <ul>
      <li class="two"><a title="关于叽歪" href="/wo/about/jiwai">关于叽歪</a></li>
      <li class="two"><a title="团队成员" href="/wo/about/group">团队成员</a></li>
      <li class="one">联系我们</li>
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
     <h1>联系我们</h1>
     <div class="groupfont">
       <h3>叽歪网</h3>
       <p>地址：北京海淀区知春路甲48号盈都大厦C座4单元3A室
<br />邮编：100098<br />
       总机：010-58731472 <br />
       传真：010-58731470<br /><br />
       客户服务邮箱：jiwai [at] jiwai.com （请把[at]改成@） <br />
       商务合作邮箱：bd [at] jiwai.com （请把[at]改成@）<br /><br />
       大屏幕合作邮箱：dpm [at] jiwai.com （请把[at]改成@）<br />
       大屏幕合作专线：010-58731472-208（205）<br /><br />
       </p>
        <img alt="叽歪网的位置" title="叽歪网的位置" style="float:left;" src="<? echo JWTemplate::GetAssetUrl('/images/tu.jpg');?>" />
     </div>
     
   </div>
   <div class="aboutrightbottom"></div>
 </div>
 
  <?
  JWTemplate::footer3();
  ?>
</body>
</html>
