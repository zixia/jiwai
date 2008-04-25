<?php

require_once "lib/common.php";
require_once "lib/session.php";
require_once "lib/render.php";

require_once "lib/render/idpXrds.php";
require_once "lib/render/userXrds.php";

require_once "Auth/OpenID.php";

/**
 * Handle a standard OpenID server request
 */
function action_default()
{
    header('X-XRDS-Location: '.buildURL('idpXrds'));

    $server =& getServer();
    $method = $_SERVER['REQUEST_METHOD'];
    $request = null;
    if ($method == 'GET') {
        $request = $_GET;
    } else {
        $request = $_POST;
    }

    $request = $server->decodeRequest();

    if (!$request) {
		return ;
        return about_render();
    }

    setRequestInfo($request);

	if ($request instanceof Auth_OpenID_ServerError) {
		return redirect_render('http://'.$_SERVER['HTTP_HOST'].'/wo/openid/');
	}
	$trusted = JWOpenID_TrustSite::IsTrusted(getLoggedInUser('idUser'), $request->trust_root);
    if (in_array($request->mode,
                 array('checkid_immediate', 'checkid_setup'))) {
        if ($request->idSelect()) {
            // Perform IDP-driven identifier selection
            if ($request->mode == 'checkid_immediate') {
                $response =& $request->answer(false);
            } else {
                return trust_render($request, $trusted);
            }
        } else if ((!$request->identity) &&
                   (!$request->idSelect())) {
            // No identifier used or desired; display a page saying
            // so.
            return noIdentifier_render();
        } else if ($request->immediate) {
            $response =& $request->answer(false, buildURL());
        } else {
			$current_user = getLoggedInUser();
			if ($current_user && $request->identity!=idURL($current_user)) {
				JWSession::SetInfo('notice', '请求的OpenID('.$request->identity.')与当前已登录用户不一致，请确认登录或重新认证。');
				$current_user = false;
			}
            if (!$current_user) {
                return login_render();
            }
            return trust_render($request, $trusted);
        }
    } else {
        $response =& $server->handleRequest($request);
    }

    $webresponse =& $server->encodeResponse($response);

    foreach ($webresponse->headers as $k => $v) {
        header("$k: $v");
    }

    header(header_connection_close);
    print $webresponse->body;
    exit(0);
}

/**
 * Log out the currently logged in user
 */
function action_logout()
{
    setLoggedInUser(null);
    setRequestInfo(null);
    return authCancel(null);
}

/**
 * Check the input values for a login request
 */
function login_checkInput($input)
{
    $openid_url = false;
    $errors = array();

    if (!isset($input['openid_url'])) {
        $errors[] = 'Enter an OpenID URL to continue';
    }
    if (count($errors) == 0) {
        $openid_url = $input['openid_url'];
    }
    return array($errors, $openid_url);
}

/**
 * Log in a user and potentially continue the requested identity approval
 */
function action_login()
{
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/wo/login');
	exit();
}

/**
 * Ask the user whether he wants to trust this site
 */
function action_trust()
{
    $info = getRequestInfo();
    $trusted = empty($_POST['trust']) ? 0 : (empty($_POST['always']) ? 1 : 2);
    return doAuth($info, $trusted, true, @$_POST['idSelect']);
}


function action_idpXrds()
{
    return idpXrds_render();
}

function action_userXrds()
{
    $identity = $_GET['user'];
    return userXrds_render($identity);
}

?>
