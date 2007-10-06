<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();
/*
@header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
@header("Cache-Control: no-store, no-cache, must-revalidate");
@header("Pragma: no-cache");
*/


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

			$conference = JWConference::GetDbRowFromUser( $idUser );
			if( empty( $conference ) ){
				$idConference = JWConference::Create($idUser, $friendOnly, $deviceAllow );
				JWUser::SetConference($idUser, $idConference);
			}else{
				$idConferenceNow = $conference['id'];
				JWConference::Update($idConferenceNow, $friendOnly, $deviceAllow );
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

	Header("Location: /wo/account/meeting");
}

/* Confrence Information */
$conferenceSetting = array(
		'sms' => false,
		'im' => false,
		'web' => false,
		'friendOnly' => 'N',
		);
$conference = JWConference::GetDbRowFromUser( $user_info['idUser'] );
if( !empty( $conference ) ){
	$conferenceSetting['friendOnly'] = $conference['friendOnly'];
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
}else{
	$numberMobile = '50136911'.$user_info['idUser'];
	$numberUnicom = '9501456711'.$user_info['idUser'];
}

var_dump( $numberMobile );
var_dump( $numberUnicom );


?>
<html>

<head>
<style>
input.cb{ width:24px; display:inline; }
</style>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="create">

<?php JWTemplate::header() ?>
<?php JWTemplate::ShowActionResultTips() ?>

<div id="container" class="subpage">

	<h2> <?php echo JWNotify::GetPrettySender($user_info); ?> - 会议设置 </h2>

	<fieldset>
	<form method="post">
		<table width="100%" cellspacing="3">
			<tr>
				<th valign="top" width="200">
					<b>会议模式</b>
				</th>
				<td style="padding-bottom:20px;">
					<br/>
					<input <?php if ( null!==$idConference ) echo ' checked="checked" ';?> id="enable_conference" name="enableConference" type="checkbox" value="Y" style="width:24px; display:inline;" /><label for="enable_conference">启动会议模式</label>
					<p>
						使用方法：<br/>
						1、手机发送短信给 9911881699<?php echo $user_info['idUser'] ?><br/>
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
				</td>
			</tr>
		</table>
		</fieldset>
	    <div style=" padding:24px 0 0 160px; height:50px;">
		<a onclick="if(JWValidator.validate('f'))$('f').submit();return false;" class="button" href="#"><img src="<?php echo JWTemplate::GetAssetUrl('/images/org-text-save.gif'); ?>" alt="保存" /></a>
	    </div>            

	</form>
</div>
<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>  
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
