<?php
require_once "../../../jiwai.inc.php";

//define('Auth_OpenID_NO_MATH_SUPPORT', true);

// Complete the authentication process using the server's response.
$response = JWOpenidConsumer::GetCompleteResponse($_GET);

switch ($response->status)
{
	default:
	case Auth_OpenID_FAILURE:
    	$msg = "OpenID 认证失败：" . $response->message;
		JWSession::SetInfo('notice',$msg);
		header('Location: /wo/login');
		break;

	case Auth_OpenID_CANCEL:
    	// This means the authentication was cancelled.
    	$msg = 'Openid认证已经被取消。';
		JWSession::SetInfo('notice',$msg);
		header('Location: /wo/login');
		break;

	case Auth_OpenID_SUCCESS:
    	// This means the authentication succeeded.
    	$openid = $response->identity_url;
/*
    	$esc_identity = htmlspecialchars($openid, ENT_QUOTES);
    	$success = sprintf('You have successfully verified ' .
                       '<a href="%s">%s</a> as your identity.',
                       $esc_identity, $esc_identity);

    	if ($response->endpoint->canonicalID) {
        	$success .= '  (XRI CanonicalID: '.$response->endpoint->canonicalID.') ';
    	}
*/

    	$sreg = $response->extensionResponse('sreg');

    	if (@$sreg['email']) {
        	$email 		= $sreg['email'];
    	}
    	if (@$sreg['postcode']) {
        	$postcode 	= $sreg['postcode'];
    	}


		$openid_id = JWOpenid::GetIdByUrl($openid);
		$openid_db_row	= JWOpenid::GetDbRowById($openid_id);

		$user_id = $openid_db_row['idUser'];


		if ( JWLogin::IsLogined() )
		{
			/*
		 	 *	如果已经登录，那么过去验证openid是为了绑定。
		 	 */
			$logined_user_id = JWLogin::GetCurrentUserId();
			JWOpenid::Create($openid, $logined_user_id);
			header("Location: /wo/openid/");
			exit(0);
		} 
		else if ( $user_id )
		{
			// old user
			JWLogin::Login($user_id);
			header("Location: /");
		}
		else
		{
			// new user
			$user_name = JWUser::GetPossibleName($openid);
        	$new_user_row = array   (
                             'nameScreen'   => $user_name
                            ,'nameFull'     => $user_name
                            ,'pass'         => JWDevice::GenSecret(16)
							,'email'		=> $email
                        );

        	$user_id =  JWUser::Create($new_user_row);

			JWOpenid::Create($openid,$user_id);

			JWLogin::Login($user_id);
			header("Location: /wo/account/settings");
		}
		break;
}
exit(0);
?>
