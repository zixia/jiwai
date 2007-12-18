<?php
require_once('./function.php');

$un = null;
extract($_GET, EXTR_IF_EXISTS);

$unResult = array();
if( $un ) {
	$user_info = JWUser::GetUserInfo( $un );
	$idUser = $user_info['id'];
}
if (empty($user_info))
{
	$user_info = JWUser::GetCurrentUserInfo();
	$idUser = $user_info['id'];
	$un = $user_info['nameScreen'];
	header("Location: confsetting.php?un=$un");
}

if( $_POST ){
	$enableConference = 'N';
	$conf = null;
	extract($_POST, EXTR_IF_EXISTS);
	if( 'Y' == $enableConference ) {
		if( isset( $conf['deviceAllow'] ) ) {
			$friendOnly = isset( $conf['friendOnly'] ) ? 'Y' : 'N';
			$deviceAllow = implode(',', $conf['deviceAllow'] );

			$conference = JWConference::GetDbRowFromUser( $idUser );
			if( empty( $conference ) ){
				$idConference = JWConference::Create($idUser, array(
					'friendOnly' => $friendOnly, 
					'deviceAllow' => $deviceAllow,
				));
				JWUser::SetConference($idUser, $idConference);
			}else{
				$idConferenceNow = $conference['id'];
				if( null == $idConference ) {
					if( !empty($conf['number']) )
					{
						$sql = "select * from Conference where number='$conf[number]'";
						$row = JWDB::GetQueryResult($sql);
					}
					else
						$conf['number'] = null;
					if( !empty($row) )			
						header("Location: confsetting.php?un=$un");
					JWConference::Update($idConferenceNow, $friendOnly, $deviceAllow, $conf['number'] );
					JWUser::SetConference($idUser, $idConferenceNow );
				}
			}
		}
	}else{
		if( $user_info['idConference'] ) {
			JWUser::SetConference($idUser);
		}
	}

	header("Location: confsetting.php?un=$un");
}

/* Confrence Information */
$conferenceSetting = array(
		'sms' => '',
		'im' => '',
		'web' => '',
		'friendOnly' => '',
		'enable_conference' => '',
		'number' => '',
		);

$conference = JWConference::GetDbRowFromUser( $idUser );
if( !empty( $conference ) ){
	$conferenceSetting['friendOnly'] = ('Y'==$conference['friendOnly']) ? 'checked="true"' : '';
	$deviceAllow = explode(',', $conference['deviceAllow']);
	$conferenceSetting['sms'] = in_array('sms', $deviceAllow) ? 'checked="true"' : '';
	$conferenceSetting['im'] = in_array('im', $deviceAllow) ? 'checked="true"' : '';
	$conferenceSetting['web'] = in_array('web', $deviceAllow) ? 'checked="true"' : '';
	$conferenceSetting['enable_conference'] = (null!=$user_info['idConference']) ? 'checked="true"' : '';
	$conferenceSetting['number'] = $conference['number'];
}

$render = new JWHtmlRender();
$render->display("confsetting", array(
			'menu_nav' => 'confsetting',
			'un' => $un,
			'uid' => $idUser,
			'confSetting' => $conferenceSetting,
			));
?>
