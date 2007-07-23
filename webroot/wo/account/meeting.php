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

?>
<html>

<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="settings">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<div class="separator"></div>

<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">


			<h2> <?php echo $user_info['nameScreen']?> </h2>

<?php JWTemplate::UserSettingNav('meeting'); ?>

<?php

if ( empty($error_html) )
	$error_html	= JWSession::GetInfo('error');

if ( empty($notice_html) )
{
	$notice_html	= JWSession::GetInfo('notice');
}

if ( !empty($error_html) )
{
		echo <<<_HTML_
			<div class="notice">信息无法修改：<ul> $error_html </ul></div>
_HTML_;
}


if ( !empty($notice_html) )
{
	echo <<<_HTML_
			<div class="notice"><ul>$notice_html</ul></div>
_HTML_;
}
?>

			<form method="post">
				<fieldset>
					<table>
						<tr>
							<th>
								<input <?php if ( null!==$idConference ) echo ' checked="checked" ';?> 
										id="enable_conference" name="enableConference" type="checkbox" value="Y" />
							</th>
							<td>
								<label for="enable_conference">启用会议模式</label>[<a href="http://help.JiWai.de/MeetingModle">?</a>]
								<p>
									使用方法：<br/>
									1、手机发送短信给 9911881699<?php echo $user_info['idUser'] ?><br/>
									2、发消息时增加头 "@<?php echo $user_info['nameScreen'] ?>"
								</p>
							</td>
						</tr>
						<tr>
							<th>
							</th>
							<td>
								高级设置[<a href="http://help.jiwai.de/MeetingSetting">?</a>]
								<br/>
								<input <?php if ( true==$conferenceSetting['sms']) echo ' checked="checked" ';?> id="conf_device_sms" name="conf[deviceAllow][]" type="checkbox" value="sms" />
								<label for="conf_device_sms">允许手机短信发送</label>
								<br/>

								<input <?php if ( true==$conferenceSetting['im'] ) echo ' checked="checked" ';?> id="conf_device_im" name="conf[deviceAllow][]" type="checkbox" value="im" />
								<label for="conf_device_im">允许聊天软件(IM)发送</label>
								<br/>

								<input <?php if ( true==$conferenceSetting['web'] ) echo ' checked="checked" ';?> id="conf_device_web" name="conf[deviceAllow][]" type="checkbox" value="web" />
								<label for="conf_device_web">允许Web发送</label>
								<br/>

								<br/>
								<input <?php if ( 'Y'==$conferenceSetting['friendOnly'] ) echo ' checked="checked" ';?> id="conf_friend_only" name="conf[friendOnly]" type="checkbox" value="Y" />
								<label for="conf_friend_only">只允许好友回复给我</label>
								<br/>
							</td>
						</tr>
						<tr>
							<th></th>
							<td>
								<input name="commit" type="submit" value="保存" />
							</td>
						</tr>
					</table>
				</fieldset>
			</form>


		</div><!-- wrapper -->
	</div><!-- content -->

</div><!-- #container -->
<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
