<?php

require_once "lib/render.php";
require_once "Auth/OpenID/Server.php";

/**
 * Get the URL of the current script
 */
function getServerURL()
{
    $path = $_SERVER['REQUEST_URI'];
    $host = $_SERVER['HTTP_HOST'];
    $port = $_SERVER['SERVER_PORT'];
    $s = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 's' : '';
    if (($s && $port == "443") || (!$s && $port == "80")) {
        $p = '';
    } else {
        $p = ':' . $port;
    }

    return "http$s://$host$p$path";
}

/**
 * Build a URL to a server action
 */
function buildURL($action=null, $escaped=true)
{
    $url = getServerURL();
    switch ($action) {
		case 'login':
			$url = 'http://'.$_SERVER['HTTP_HOST'].'/wo/login';
			break;
		case 'logout':
			$url = 'http://'.$_SERVER['HTTP_HOST'].'/wo/logout';
			break;
		default:
	        if ($action) {
				if (strpos($url, '?')===false) 
					$url .= '/' . $action;
				else
					$url = str_replace('?', '/'.$action.'?', $url);
			}
    }
    return $escaped ? htmlspecialchars($url, ENT_QUOTES) : $url;
}

/**
 * Extract the current action from the request
 */
function getAction()
{
    $path_info = @$_GET['pathParam']; //$_SERVER['PATH_INFO'];
	unset($_GET['pathParam']);
    $action = ($path_info) ? substr($path_info, 1) : '';
    $function_name = 'action_' . $action;
    return $function_name;
}

/**
 * Write the response to the request
 */
function writeResponse($resp)
{
    list ($headers, $body) = $resp;
    @array_walk($headers, 'header');
    header(header_connection_close);
    print $body;
}

/**
 * Instantiate a new OpenID server object
 */
function getServer()
{
    static $server = null;
    if (!isset($server)) {
        $server =& new Auth_OpenID_Server(getOpenIDStore(),
                                          buildURL());
    }
    return $server;
}

/**
 * Get the openid_url out of the cookie
 *
 * @return mixed $openid_url The URL that was stored in the cookie or
 * false if there is none present or if the cookie is bad.
 */
function getLoggedInUser($field='nameUrl')
{
	static $user_info;
	if (!$user_info) $user_info = JWUser::GetCurrentUserInfo();
    return !empty($user_info)
        ? $user_info[$field]
        : false;
}

/**
 * Set the openid_url in the cookie
 *
 * @param mixed $identity_url The URL to set. If set to null, the
 * value will be unset.
 */
function setLoggedInUser($identity_url=null)
{
    if (!isset($identity_url)) {
        unset($_SESSION['openid_url']);
    } else {
        $_SESSION['openid_url'] = $identity_url;
    }
}

function getRequestInfo()
{
    return isset($_SESSION['request'])
        ? unserialize($_SESSION['request'])
        : false;
}

function setRequestInfo($info=null)
{
    if (!isset($info)) {
        unset($_SESSION['request']);
    } else {
        $_SESSION['request'] = serialize($info);
    }
}


function getSreg($identity)
{
    // from config.php
    global $openid_sreg;

    if (!is_array($openid_sreg)) {
        return null;
    }

    return $openid_sreg[$identity];

}

function idURL()
{
	$user_info = JWUser::GetCurrentUserInfo();
	return 'http://'.$_SERVER['HTTP_HOST'].'/'.$user_info['nameUrl'].'/';
}

?>
