<?php
require_once( dirname(__FILE__).'/config.inc.php' );

JWTemplate::html_doctype();
JWLogin::MustLogined();

$user_info = JWUser::GetCurrentUserInfo();
$idUser = $user_info['idUser'];
$nameScreen = $user_info['nameScreen'];
$idConference = $user_info['idConference'];

if( $_POST ){
	$enableConference = 'N';
	$conf = null;
	extract($_POST, EXTR_IF_EXISTS);
	if( 'Y' == $enableConference ) {
		if( !isset( $conf['deviceAllow'] ) ) {
			JWSession::SetInfo('error', '至少选择一个可发送设备');
		}else{

			$friendOnly = isset( $conf['friendOnly'] ) ? 'Y' : 'N';
			$deviceAllow = implode(',', $conf['deviceAllow'] );
			$filter = isset( $conf['filter'] ) ? 'N' : 'Y';
			$notify = isset( $conf['notify'] ) ? 'Y' : 'N';

			$conference = JWConference::GetDbRowFromUser( $idUser );
			if( empty( $conference ) ){
				$idConference = JWConference::Create($idUser, $friendOnly, $deviceAllow );
				JWUser::SetConference($idUser, $idConference);
			}else{
				$idConferenceNow = $conference['id'];
				JWConference::UpdateRow($idConferenceNow, array(
					'friendOnly' => $friendOnly,
					'deviceAllow' => $deviceAllow,
					'notify' => $notify,
					'filter' => $filter,
				));
				if( null == $idConference ) {
					JWUser::SetConference($idUser, $idConferenceNow );
				}
			}
		}
	}else{
		if( $user_info['idConference'] ) {
			JWUser::SetConference($idUser);
		}
	}

	Header("Location: /meeting.php");
}

/* Confrence Information */
$conferenceSetting = array(
		'sms' => false,
		'im' => false,
		'web' => false,
		'friendOnly' => 'N',
		'notify' => 'Y',
		'filter' => 'N',
		);
$conference = JWConference::GetDbRowFromUser( $user_info['idUser'] );
if( !empty( $conference ) ){
	$conferenceSetting['friendOnly'] = $conference['friendOnly'];
	$conferenceSetting['notify'] = $conference['notify'];
	$conferenceSetting['filter'] = $conference['filter'];
	$deviceAllow = explode(',', $conference['deviceAllow']);
	$conferenceSetting['sms'] = in_array('sms', $deviceAllow) ? true : false;
	$conferenceSetting['im'] = in_array('im', $deviceAllow) ? true : false;
	$conferenceSetting['web'] = in_array('web', $deviceAllow) ? true : false;
}

if( preg_match( '/^gp(\d+)$/', $user_info['nameScreen'] , $m) ) {
	if( strlen($m[1]) == 3 ) {
		$numberMobile = '50136986'.$m[1];
		$numberUnicom = '9501456786'.$m[1];
	}else{
		$numberMobile = '50136988'.$m[1];
		$numberUnicom = '9501456788'.$m[1];
	}
}else if( $conference && null != $conference['number'] ){
	$numberMobile = '50136910'.$conference['number'];
	$numberUnicom = '9501456710'.$conference['number'];
}else{
	$numberMobile = '50136911'.$user_info['idUser'];
	$numberUnicom = '9501456711'.$user_info['idUser'];
}

?>
<html>

<head>
<style>
	input.cb{ width:24px; display:inline; }
</style>
<base target="_self"/>
<?php JWTemplate::html_head() ?>
</head>

<body class="account" id="create">

<div id="container" class="subpage">

	<h2> <?php echo JWNotify::GetPrettySender($user_info); ?> - 会议设置 </h2>
	<fieldset>
	<form method="post" id='f' action='/meeting.php' onSubmit="return JWValidator.validate('f');">
		<table width="100%" cellspacing="3">
			<tr>
				<th valign="top" width="200">
					<b>会议模式</b>
				</th>
				<td style="padding-bottom:20px;">
					<br/>
					<input <?php if ( null!==$idConference ) echo ' checked="checked" ';?> id="enable_conference" name="enableConference" type="checkbox" value="Y" style="width:24px; display:inline;" /><label for="enable_conference">启动会议模式</label>
					<p> 使用方法：<br/>
						1、手机编辑短信，移动发送到 <?php echo $numberMobile ?> , 
						联通发送到 <?php echo $numberUnicom; ?><br/>
						2、发消息时增加头 "@<?php echo $user_info['nameScreen'] ?>"
					</p>
				</td>
			</tr>
			<tr>
				<th valign="top" width="200">
					<b>高级设置</b>
				</th>
				<td style="padding-bottom:20px;">
					<br/>
					<input <?php if ( true==$conferenceSetting['sms']) echo ' checked="checked" ';?> id="conf_device_sms" name="conf[deviceAllow][]" type="checkbox" value="sms" style="width:24px; display:inline;" />
					<label for="conf_device_sms">允许手机短信发送</label>
					<br/>

					<input <?php if ( true==$conferenceSetting['im'] ) echo ' checked="checked" ';?> id="conf_device_im" name="conf[deviceAllow][]" type="checkbox" value="im" style="width:24px; display:inline;" />
					<label for="conf_device_im">允许聊天软件(IM)发送</label>
					<br/>

					<input <?php if ( true==$conferenceSetting['web'] ) echo ' checked="checked" ';?> id="conf_device_web" name="conf[deviceAllow][]" type="checkbox" value="web" style="width:24px; display:inline;" />
					<label for="conf_device_web">允许Web发送</label>
					<br/>
				</td>
			</tr>
			<tr>
				<th valign="top" width="200">
					<b>过滤设置</b>
				</th>
				<td style="padding-bottom:20px;">
					<br/>
					<input <?php if ( 'Y'==$conferenceSetting['friendOnly'] ) echo ' checked="checked" ';?> id="conf_friend_only" name="conf[friendOnly]" type="checkbox" value="Y" style="width:24px; display:inline;" />
					<label for="conf_friend_only">只允许好友回复给我</label>
					<br/>
					<input <?php if ( 'N'==$conferenceSetting['filter'] ) echo ' checked="checked" ';?> id="conf_filter" name="conf[filter]" type="checkbox" value="N" style="width:24px; display:inline;" />
					<label for="conf_filter">用户信息直接进入会议系统</label>
					<br/>
					<input <?php if ( 'Y'==$conferenceSetting['notify'] ) echo ' checked="checked" ';?> id="conf_notify" name="conf[notify]" type="checkbox" value="Y" style="width:24px; display:inline;" />
					<label for="conf_notify">更新自动通知订阅者</label>
					<br/>
				</td>
			</tr>
		</table>
		</fieldset>
	    <div style=" padding:24px 0 0 160px; height:50px;">
	    	<input type="image" src="<?php echo JWTemplate::GetAssetUrl('/images/org-but-save.gif'); ?>" alt="保存"/>
	    </div>            

	</form>
</div>
<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>  
</div><!-- #container -->
<script>
	JWValidator.init('f');
</script>
</body>
</html>
