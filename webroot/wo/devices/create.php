<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined();

$current_user_info = JWUser::GetCurrentUserInfo();
$current_user_id = $current_user_info['id'];

if ( $current_user_id
		&& array_key_exists('device',$_REQUEST) ){

	$aDeviceInfo = $_REQUEST['device'];

	if ( 'newsmth'==$aDeviceInfo['type'] &&  ! preg_match('/@/',$aDeviceInfo['address']) )
		$aDeviceInfo['address'] .= '@newsmth.net';


	$is_succ = JWDevice::Create($current_user_id
							, $aDeviceInfo['address']
							, $aDeviceInfo['type'] );

	$address	= $aDeviceInfo['address'];
	$type		= $aDeviceInfo['type'];

	$typename 	= JWDevice::GetNameFromType($type);
	$robot =  JWDevice::GetRobotFromType($type , $address);

	$error_html = '';
	$notice_html = '';
	$ContactUsUrl = JWTemplate::GetConst('UrlContactUs');

	if (null===$is_succ )
	{
		if('sms'!=$type)
		$notice_html = <<<_ERR_
		<div class="bindingIMbox"><p>$typename 帐号 $address 未能通过叽歪网系统检查，请你检查是否输入了正确的 $typename 帐号（EMail需要写全@域名）。如有疑问请
_ERR_;
		else
		$notice_html = <<<_ERR_
		<div class="bindingIMbox"><p>手机号码 $address 未能通过叽歪网系统检查，请你检查是否输入了正确的手机号码。如有疑问请
_ERR_;
		$notice_html .= "<a href=\"${ContactUsUrl}\">联系我们</a></p></div>";
	} 
	else if (false===$is_succ )
	{
		$secret = JWDevice::GetMergeSecret( $current_user_id, $type, $address );

		$device_row = JWDB::GetTableRow('Device',array(
			'address' => $address,
			'type' => $type,
		));
		$device_user_info = JWDB_Cache_User::GetDbRowById($device_row['idUser']);
		$quote1 = JWTemplate::GetAssetUrl('/images/quote1.gif');
		$quote2 = JWTemplate::GetAssetUrl('/images/quote2.gif');
		if('N'==$device_user_info['isWebUser'])
		{
			$sql = "select * from Status where idUser=${device_row['idUser']} order by id desc limit 1";
			$status_row = JWDB_Cache::GetQueryResult($sql);
			$status = "${device_user_info['nameScreen']}: ${status_row['status']}";
			if('sms'!=$type)
				$notice_html = <<<_ERR_
		<div class="bindingIMbox">   
	   <p>你想要绑定的<b class="black14">${typename}号码为&nbsp;${address}</b>，我猜，你以前肯定用这个${typename}号给叽歪网发送过消息？</p>
	   <p class="bindingblack">这是该${typename}发送的最新一条叽歪，是不是你的呢？<br /><div><img src="$quote1" title="“" alt="“" />$status<img src="$quote2" title="”" alt="”" /></div></p>
	   <p>如果你确定绑定这个号码，请按以下步骤操作：</p>
	   <p class="bindingblack">1. 请在${typename}上添加【${typename}叽歪小弟：${robot}】为好友</p>
	   <p class="bindingblack">2. 请复制以下验证信息给叽歪小弟，完成绑定</p>
       <p class="bindingblack">&nbsp;&nbsp;&nbsp;&nbsp;验证码：<input type="text" value="hebing ${current_user_info['nameScreen']} ${secret}" class="inputStyle3 Width135"/></p>
	   </div>
_ERR_;
			else
				$notice_html = <<<_ERR_
	   <p>你想要绑定的<b class="black14">手机号码为&nbsp;${address}</b>，我猜，你以前肯定用这个手机号给叽歪网发送过消息？</p>
	   <p class="bindingblack">这是该手机发送的最新一条叽歪，是不是你的呢？<br /><div><img src="$quote1" title="“" alt="“" />$status<img src="$quote2" title="”" alt="”" /></div>
	   <p>如果你确定绑定这个号码，请按以下步骤操作：</p>
	   <p class="bindingblack">1. 将【叽歪小弟：106693184】 加入你的通讯簿</p>
	   <p class="bindingblack">2. 发送以下验证信息给叽歪小弟，完成绑定</p>
       <p class="bindingblack">&nbsp;&nbsp;&nbsp;&nbsp;验证码：<input type="text" value="hebing ${current_user_info['nameScreen']} ${secret}" class="inputStyle3 Width135"/></p>
	   <p class="bindinggray12">用手机发送验证码或发短信给叽歪网，与发短信给普通手机费用完全一样</p>
_ERR_;
		}
		else
			$notice_html = <<<_ERR_
		<div class="bindingIMbox">   
		<p>该${typename}号码${address}已经被其他用户绑定，如果是你的马甲，请先取消绑定。如果不是，请选择其他号码绑定。</p><a href="${ContactUsUrl}" target="_blank">如需帮助请点这里</a></p>
	   </div>
_ERR_;
	}
	else if ( $is_succ )
	{
		$notice_html='';
	}
	else
	{
		// no condition here
	}

	if ( !empty($error_html) )
		JWSession::SetInfo('error',$error_html);

	if ( !empty($notice_html) )
		JWSession::SetInfo('notice',$notice_html);

}
JWTemplate::RedirectBackToLastUrl();
?>
