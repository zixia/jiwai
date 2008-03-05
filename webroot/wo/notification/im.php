 <?php
 require_once('../../../jiwai.inc.php');
 JWTemplate::html_doctype();
 
 JWLogin::MustLogined();
 
 
 $user_info      = JWUser::GetCurrentUserInfo();
 
 $user_setting   = JWUser::GetNotification($user_info['id']);

 if ( isset($_REQUEST['commit_x']) )
 {
	$user_new_setting = isset($_POST['user']) ? $_POST['user'] : array();
	$user_setting['auto_nudge_me'] = isset($user_new_setting['auto_nudge_me']) ? $user_new_setting['auto_nudge_me'] : 'N';
	$user_setting['is_receive_offline'] = isset($user_new_setting['is_receive_offline']) ? $user_new_setting['is_receive_offline'] : 'N';
	$user_setting['allowSystemSms'] = !empty($user_new_setting['allowSystemSms']) ? 'Y' : 'N';
	$user_setting['isNotReceiveNight'] = !empty($user_new_setting['isNotReceiveNight']) ? 'Y' : 'N';
	$user_setting['notReceiveTime1'] = $user_new_setting['notReceiveTime1'];
	$user_setting['notReceiveTime2'] = $user_new_setting['notReceiveTime2'];
	$user_setting['allowReplyType'] = $user_new_setting['allowReplyType'];
 
	if ( ! JWUser::SetNotification($user_info['id'], $user_setting) )
	{
		JWSession::SetInfo('error', '通知设置由于系统故障未能保存成功，请稍后再试。');
	}
	else
	{
		JWSession::SetInfo('notice', '通知设置保存成功！');
	}

	JWTemplate::RedirectToUrl();
}

?>
<html>

<head>
<?php 
JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
)); 
?>
 </head>
 
 
 <body class="account" id="settings">
 
 <?php JWTemplate::accessibility() ?>
 
 <?php JWTemplate::header('/wo/account/settings') ?>
 <?php JWTemplate::ShowActionResultTipsMain() ?>
 
 <div id="container">
 <p class="top">设置</p>
 <div id="wtMainBlock">
 <div class="leftdiv">
 <ul class="leftmenu">
 <li><a href="/wo/account/settings">基本资料</a></li>
 <li><a href="/wo/privacy/">保护设置</a></li>
 <li><a href="/wo/devices/sms">绑定设置</a></li>
 <li><a href="/wo/notification/email" class="now">系统通知</a></li>
 <li><a href="/wo/account/profile_settings">个性化界面</a></li>
 <li><a href="/wo/openid/">Open ID</a></li>
 </ul>
 </div><!-- leftdiv -->
 <div class="rightdiv">
 <div class="lookfriend">
 <form id="f" action="" method="post" name="f">
 <input type="hidden" name="commit_x" value="1"/>
<p class="right14"><a href="/wo/notification/email">Email</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/notification/im" class="now">手机/聊天软件</a></p>
        <div class="binding">
         <p>
             <label for="user_auto_nudge_me"><input <?php if ( 'Y'==$user_setting['auto_nudge_me'] ) echo ' checked';?> 
                     id="user_auto_nudge_me" name="user[auto_nudge_me]" type="checkbox" value="Y" />&nbsp;如果我在24小时内没有更新，请让叽歪小弟提醒我</label>
         </p>
         <p>
             <label for="is_receive_offline"><input <?php if ( 'Y'==$user_setting['is_receive_offline'] ) echo ' checked';?> 
                     id="is_receive_offline" name="user[is_receive_offline]" type="checkbox" value="Y" />&nbsp;当我的聊天软件处于离线状态时也给我发送通知</label>
         </p>
	   <p class="checkboxText">我们支持qq的隐身，msn的脱机</p>	
	   <p><label for="user_isNotReceiveNight"><input type="checkbox" id="user_isNotReceiveNight" name="user[isNotReceiveNight]" value="Y"<?php if ( 'Y'==$user_setting['isNotReceiveNight'] ) echo ' checked';?> />&nbsp;睡觉时间不要给我通知</label> 
	     <select name="user[notReceiveTime1]" size="1" class="select Width80">
	     <? for($i=0;$i<24;$i++){
		     $time_array = explode(":", $user_setting['notReceiveTime1'], 3);
		?>
	     <option value="<? echo $i>9? $i : "0$i";?>:00:00"<? echo $i != intval($time_array[0])? '' : ' selected';?>><? echo $i>9? $i : "0$i";?>:00</option>
	     <? } ?>
		 </select> 至<select name="user[notReceiveTime2]" size="1" class="select Width80">
	     <? for($i=0;$i<24;$i++){
		     $time_array = explode(":", $user_setting['notReceiveTime2'], 3);
		?>
	     <option value="<? echo $i>9? $i : "0$i";?>:00:00"<? echo $i != intval($time_array[0])? '' : ' selected';?>><? echo $i>9? $i : "0$i";?>:00</option>
	     <? } ?>
		 </select></p>	
        <p><label for="user_allowSystemSms"><input type="checkbox" id="user_allowSystemSms" name="user[allowSystemSms]" value="Y"<?php if ( 'Y'==$user_setting['allowSystemSms'] ) echo ' checked';?>/>&nbsp;允许叽歪通过短信联系我</label></p>
        <p class="sysUpdate">我想<select id="user_allowReplyType" name="user[allowReplyType]" size="1" class="select seWidth">
          <option value="everyone"<?php if ( 'everyone'==$user_setting['allowReplyType'] ) echo ' selected ';?>>接收我关注的人给任何人的回复</option> 
          <option value="each"<?php if ( 'each'==$user_setting['allowReplyType'] ) echo ' selected ';?>>接收我关注的人们相互之间的回复</option>        
          <option value="mine"<?php if ( 'mine'==$user_setting['allowReplyType'] ) echo ' selected ';?>>只接收给我的回复</option>        
          <option value="none"<?php if ( 'none'==$user_setting['allowReplyType'] ) echo ' selected ';?>>不接收任何回复</option>
         </select>
          <a href="http://help.jiwai.de/ReplyReceive" target="_blank" class="orange12">什么意思？</a></p> 
	   <p class="sysUpdate"><input type="submit" id="save" name="save" class="submitbutton" value="保存" /></p>
	   </div>
	   <div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
  </form>
</div><!-- lookfriend -->
<div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
</div>
<!-- rightdiv -->
</div><!-- #wtMainBlock -->
<div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
