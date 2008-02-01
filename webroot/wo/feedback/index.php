<?php
require_once(dirname(__FILE__).'../../../../jiwai.inc.php');
function update_feedback($user_id,$device_row)
{
	if (empty($_POST))
		return;

	if (isset($_POST['commit_u']))
	{
		$message = $year = $now_time = $time = $device = $type = null;
		$message = isset($_POST['message']) ? trim($_POST['message']) : NULL;
		$type = isset( $_POST['type']) ? $_POST['type'] : NULL;
		$device = isset( $_POST['device']) ? $_POST['device'] :NULL;
		$year = isset( $_POST['year']) ? $_POST['year'] : NULL;
		$now_time = isset( $_POST['now_time']) ? $_POST['now_time'] : NULL;
		$time = $year.' '.$now_time;
		$meta_info = array(
				'time' => strtotime($time),
				'number' => $device_row[$device]['address'],
				);

		$is_succ = JWFeedBack::Create($user_id,$device, $type,$message,$meta_info);

		if( false == $is_succ )
		{
			JWSession::SetInfo('error', '对不起，举报失败。');
		}
		else
		{
			JWSession::SetInfo('notice', '你的反馈提交成功，我们会尽快处理。');
		}
	}

	if (isset($_POST['commit_p']))
	{
		$user = $reason = null;
		$user = isset( $_POST['feed_user']) ? trim( $_POST['feed_user']) : NULL;
		$reason = isset( $_POST['reason']) ? trim( $_POST['reason']) : NULL;
		$type = 'COMPLAIN';
		$meta_info = array(
				'user' => $user,		   
				);
		$device=null;
		$is_succ = JWFeedBack::Create($user_id,$device, $type, $reason, $meta_info);

		if( false == $is_succ )
		{
			JWSession::SetInfo('error', '对不起，举报失败。');
		}
		else
		{
			JWSession::SetInfo('notice', '你的举报提交成功，我们会尽快处理。');
		}
	}

	JWTemplate::RedirectToUrl( '/wo/feedback/' );
}

JWTemplate::html_doctype();
JWLogin::MustLogined();
?>
<html>
<head>
<?php
$current_user_info  = JWUser::GetCurrentUserInfo();
$current_user_id	= $current_user_info['id'];

$options = array ('ui_user_id' => $current_user_id );
JWTemplate::html_head($options);

$device_row = JWDevice::GetDeviceRowByUserId($current_user_id);
update_feedback($current_user_id,$device_row);
$supported_device_types = JWDevice::GetSupportedDeviceTypes();
$activeOptions['web'] = true;
?>
</head>



<BODY class=normal>
<?php JWTemplate::header(); ?>

<DIV id=container><!-- google_ad_section_start -->
<DIV id=content>

<?php JWTemplate::ShowActionResultTips(); ?>

<div id="wtTimeline" style="margin-top:0; ">
<div id="feedbackTitle">反馈与投诉</div>
<DIV id=wrapper>
<DIV class=tab>
<DIV>

<?php if (false==empty($device_row) ) { ?>

<form action="/wo/feedback/" id="updaterForm1" method="post" >
<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="feedbackSelectTable">
<tr>
<td colspan="2" class="tableTitie cont">信息不通</td>

</tr>
<tr>
<td class="feedbackCont">发生了什么：</td>
<td class="feedbackInput">
<input name="type" type="radio" value="MO" checked>发送信息未到叽歪？&nbsp;&nbsp;
<input type="radio" name="type" value="MT">打开了通知却未收到消息？
</td>
</tr>
<tr>

<td class="feedbackCont">在什么上面：</td>
<td class="feedbackInput">
<select name="device">
<?php
foreach ($device_row as $type => $address_row)
{
	echo '<option value="'.$type.'">'.JWDevice::GetNameFromType($type).'</option>';
}
?>
</select>
</td>
</tr>
<tr>
<td class="feedbackCont">大概的时间：</td>

<td class="feedbackInput">
<input name="year" type="text" class="input" value="<?php echo date("Y-m-d");  ?>">
<input name="now_time" type="text" class="input" value="<?php echo date("H:i"); ?>"></td>
</tr>
<tr>
<td class="feedbackCont">大概的内容：</td>
<td class="feedbackInput">
<textarea name="message" id="jw_status" >
</textarea>
</td>
<input type="hidden" name="commit_u" value="1" />
</tr>
<tr>

<td colspan="2" ><table width="100%"  border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="110">&nbsp;</td>
<td height="50" align="center">
<input type="button" class="submitbutton" onclick="if(JWValidator.validate('updaterForm1'))$('updaterForm1').submit();return false;" value="告诉叽歪"></td>
<td width="100" align="right" valign="bottom">&nbsp;</td>
</tr>
</table>
</td>
</tr>
</table>
</form>

<?php } ?>

<form action="/wo/feedback/" id="updaterForm2" method="post" >
<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="feedbackSelectTable">
<tr>
<td colspan="2" class="tableTitie cont">举报用户</td>
</tr>
<tr>
<td class="feedbackCont">要举报的用户：</td>
<td class="feedbackInput"> http://jiwai.de/	  
<input name="feed_user" type="text" class="input" ></td>

</tr>
<tr>
<td class="feedbackCont">原因：</td>
<td class="feedbackInput"><textarea name="reason"></textarea></td>
<input type="hidden" name="commit_p" value="1"/>
</tr>
<tr>
<td colspan="2" ><table width="100%"  border="0" cellspacing="0" cellpadding="0">
<tr>

<td width="110">&nbsp;</td>
<td height="50" align="center"><input type="button" class="submitbutton" onclick="if(JWValidator.validate('updaterForm2'))$('updaterForm2').submit();return false;" value="告诉叽歪"></td>
<td width="100" align="right" valign="bottom">&nbsp;</td>
</tr>
</table>
</td>
</tr>
</table>
</DIV>
</DIV><!-- tab --></div>
</DIV><!-- wrapper --></DIV><!-- content -->
<DIV class=static id=sidebar>

<DIV class=sidediv>
<div class="linkHelpMessager">
	<a href="/t/叽歪留言板/"><img src="<?php echo JWTemplate::GetAssetUrl('/images/feedback/feedback_12.jpg'); ?>" border="0"/></a>
</div>
<div class="linkHelpMessager">
	<a href="http://Help.JiWai.de/"><img src="<?php echo JWTemplate::GetAssetUrl('/images/feedback/feedback_13.jpg'); ?>" border="0"/></a>
</div>

<DIV class=sidehelpdiv>是否要问以下的问题呢？</DIV>
<UL class=helpinfo>
	<LI><A href="http://help.jiwai.de/Faq" target=_blank>常见问题集合(FAQ)</A><BR></LI>
	<LI><A href="http://help.jiwai.de/MobileFAQ" target=_blank>手机常见问题</A></LI>
	<LI><A href="http://help.jiwai.de/IMFAQ" target=_blank>QQ、MSN、Gtalk常见问题</A></LI>
	<LI><A href="http://help.jiwai.de/VerifyYourIM" target=_blank>如何绑定QQ、MSN、Gtalk？</A></LI>
	<LI><A href="http://help.jiwai.de/VerifyYourPhone" target=_blank>如何绑定手机？</A></LI>
	<LI><A href="http://help.jiwai.de/MakeFriend" target=_blank>如何关注别人？</A></LI> 
	<LI><A href="http://help.jiwai.de/WhatistheRepliestab" target=_blank>如何回复别人？</A></LI>
	<LI><A href="http://help.jiwai.de/WhatisaFavorite" target=_blank>如何收藏感兴趣的叽歪？</A></LI>
	<LI><A href="http://help.jiwai.de/NotificationsSelection" target=_blank>如何用手机和QQ等收到别人的叽歪？</A></LI>
	<LI><A href="http://help.jiwai.de/HowToAddWidgetIntoYourBlogs" target=_blank>如何在博客上显示我的叽歪？</A></LI>
</UL>
<br/>
</DIV><!-- sidediv -->
</DIV><!-- sidebar -->

<DIV style="CLEAR: both; FONT-SIZE: 1px; OVERFLOW: hidden; LINE-HEIGHT: 1px; HEIGHT: 7px"></DIV>
</DIV><!-- #container -->

<?php JWTemplate::footer(); ?>
</BODY>
</HTML>

