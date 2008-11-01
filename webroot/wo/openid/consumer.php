<?php

require_once "../../../jiwai.inc.php";

function run() {
	$consumer = JWOpenID::GetConsumer();

	// Complete the authentication process using the server's
	// response.
	$query = Auth_OpenID::getQuery();
	unset($query['pathParam']);
	$response = $consumer->complete('http://'.$_SERVER['HTTP_HOST'].'/wo/openid/consumer/finish_auth', $query);

	// Check the response status.
	if ($response->status == Auth_OpenID_CANCEL) {
		// This means the authentication was cancelled.
		$msg = 'OpenID 认证已经被取消。';
		JWSession::SetInfo('notice',$msg);
		header('Location: /wo/login');
	} else if ($response->status == Auth_OpenID_FAILURE) {
		$msg = "OpenID 认证失败：" . $response->message;
		JWSession::SetInfo('error',$msg);
		header('Location: /wo/login');
	} else if ($response->status == Auth_OpenID_SUCCESS) {
		// This means the authentication succeeded; extract the
		// identity URL and Simple Registration data (if it was
		// returned).
		$openid = $response->getDisplayIdentifier();
		$esc_identity = htmlspecialchars($openid, ENT_QUOTES);

		$success = sprintf('You have successfully verified ' .
						   '<a href="%s">%s</a> as your identity.',
						   $esc_identity, $esc_identity);

		if ($response->endpoint->canonicalID) {
			$success .= '  (XRI CanonicalID: '.$response->endpoint->canonicalID.') ';
		}

		$sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);

		$sreg = $sreg_resp->contents();

		$openid_id = JWOpenID::GetIdByUrl($openid);
		$openid_db_row	= JWOpenID::GetDbRowById($openid_id);

		$user_id = empty($openid_db_row) ? null : $openid_db_row['idUser'];


		if ( JWLogin::IsLogined() )
		{
			if ($user_id) {
				$msg = '这个OpenID（'.$openid.'）已经被使用。';
				JWSession::SetInfo('notice', $msg);
				header('Location: /wo/login');
				return;
			}
			/*
		 	 *	如果已经登录，那么过去验证openid是为了绑定。
		 	 */
			$logined_user_id = JWLogin::GetCurrentUserId();
			JWOpenID::Create($openid, $logined_user_id);
			header("Location: /wo/openid/");
			exit(0);
		} 
		else if ( $user_id )
		{
			/*
			 *  已经绑定？登录！
			 */
			JWLogin::Login($user_id);
			header("Location: /");
		}
		else
		{
			/*
			 *  注册新帐户
			 */
			$user_name = JWUser::GetPossibleName(isset($sreg['nickname']) ? $sreg['nickname'] : $openid);
			$new_user_row = array   (
							 'nameScreen'   => $user_name
							,'nameFull'	 => isset($sreg['fullname']) ? $sreg['fullname'] : $user_name
							,'pass'		 => JWDevice::GenSecret(16)
							,'email'		=> isset($sreg['email']) ? $sreg['email'] : '' //FIXME: what if empty?
						);

			$user_id =  JWSns::CreateUser($new_user_row);

			JWOpenID::Create($openid,$user_id);

			JWLogin::Login($user_id);
			header("Location: /wo/account/settings");
		}
/*

	$pape_resp = Auth_OpenID_PAPE_Response::fromSuccessResponse($response);

	if ($pape_resp) {
	  if ($pape_resp->auth_policies) {
		$success .= "<p>The following PAPE policies affected the authentication:</p><ul>";

		foreach ($pape_resp->auth_policies as $uri) {
		  $success .= "<li><tt>$uri</tt></li>";
		}

		$success .= "</ul>";
	  } else {
		$success .= "<p>No PAPE policies affected the authentication.</p>";
	  }

	  if ($pape_resp->auth_age) {
		$success .= "<p>The authentication age returned by the " .
		  "server is: <tt>".$pape_resp->auth_age."</tt></p>";
	  }

	  if ($pape_resp->nist_auth_level) {
		$success .= "<p>The NIST auth level returned by the " .
		  "server is: <tt>".$pape_resp->nist_auth_level."</tt></p>";
	  }

	} else {
	  $success .= "<p>No PAPE response was sent by the provider.</p>";
	}
*/
	}
}

run();

?>
