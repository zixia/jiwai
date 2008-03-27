<? require_once('../../../jiwai.inc.php');?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta content="all" name="robots" />
<title>叽歪网 / 随时随地记录与分享 / 团队成员</title>
<link rel="stylesheet" href="<? echo JWTemplate::GetAssetUrl('/css/about.css');?>" type="text/css" media="all"  />
</head>
<body>
 <div class="aboutleft">
  <a title="返回叽歪网首页" href="<? echo JW_SRVNAME;?>"><img class="aboutleft_img" alt="返回叽歪网首页" src="<? echo JWTemplate::GetAssetUrl('/images/logo.gif');?>" /></a>
  <div class="aboutmenu">
    <ul>
      <li class="two"><a title="关于叽歪" href="/wo/about/jiwai">关于叽歪</a></li>
      <li class="one">团队成员</li>
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
     <h2>团队成员</h2>
	 </div>
   <div class="abdle">
     <div class="groupmen">
	 <? $user_ids = array(1, 89, 863, 2802, 32834, 37222, 37340, 43000, 42998, 38047);
	 $user_namescreens = array('zixia', 'seek', 'lecause', 'wanghw', 'wqsemc', 'paopaoyu', '沈浅浅', 'Uranus-shi', 'rannie', 'leoman');
	 foreach($user_ids as $user_id)
	 {
		$user_info = JWDB_Cache_User::GetDbRowById( $user_id );
		$sql = "select * from Status where idUser=$user_id order by timeCreate desc limit 1";
		$status_row = JWDB_Cache::GetQueryResult($sql);
		$pic = JWPicture::GetUrlById( $user_info['idPicture'] );
		$status_format = JWStatus::FormatStatus($status_row);
		$status = $status_format['status'];
	?>
       <ul>
         <li class="one"><a href="/<? echo $user_info['nameUrl'];?>/"><img alt="<? echo $user_info['nameFull'];?>" title="<? echo $user_info['nameFull'];?>" src="<? echo $pic;?>" /></a></li>
         <li class="two"><h2><? echo $user_info['nameScreen'];?></h2><div title="<? echo $status_row['status'];?>"><? echo mb_substr($status_row['status'], 0, 16);?></div><p><a class="grey" href="/<? echo $user_info['nameUrl'];?>/statuses/<? echo $status_row['id'];?>" title="<? echo $status_row['timeCreate'];?>"><? echo JWStatus::GetTimeDesc($status_row['timeCreate']);?></a> 通过 <? echo JWDevice::GetNameFromType($status_row['device']);?></p></li>
       </ul>
	 <? } ?>
     </div>
     
     <div class="groupfont">
       <h2>开放</h2>
       <p>把叽歪网打造成一个开放的交流平台
<br />欢迎一切愿意和我们合作的朋友</p>
        <h2>激情</h2>
       <p>对工作以及叽歪网充满热情，全力投入
<br />速度定成败，你我定成败</p>
        <h2>快乐</h2>
       <p>快乐团队，每一个成员都不孤单
<br />使叽歪网变成一个记录与分享快乐的地方</p>
        <h2>坚持</h2>
       <p>坚持把一切服务做到最好
<br />坚信叽歪网能成为我们和用户的一部分</p>
     </div>
     
   </div>
   <div class="aboutrightbottom"></div>
 </div>
 
  <?
  JWTemplate::footer3();
  ?>
</body>
</html>
