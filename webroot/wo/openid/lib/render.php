<?php

define('page_template',
'<html>
  <head>
    <meta http-equiv="cache-control" content="no-cache"/>
    <meta http-equiv="pragma" content="no-cache"/>
    <title>%s</title>
%s
  </head>
  <body>
    %s
<div id="content">
    <h1>%s</h1>
    %s
</div>
  </body>
</html>');

define('logged_in_pat', 'You are logged in as %s (URL: %s)');

/**
 * HTTP response line contstants
 */
define('http_bad_request', 'HTTP/1.1 400 Bad Request');
define('http_found', 'HTTP/1.1 302 Found');
define('http_ok', 'HTTP/1.1 200 OK');
define('http_internal_error', 'HTTP/1.1 500 Internal Error');

/**
 * HTTP header constants
 */
define('header_connection_close', 'Connection: close');
define('header_content_text', 'Content-Type: text/plain; charset=us-ascii');

define('redirect_message',
       'Please wait; you are being redirected to <%s>');


/**
 * Return a string containing an anchor tag containing the given URL
 *
 * The URL does not need to be quoted, but if text is passed in, then
 * it does.
 */
function link_render($url, $text=null) {
    $esc_url = htmlspecialchars($url, ENT_QUOTES);
    $text = ($text === null) ? $esc_url : $text;
    return sprintf('<a href="%s">%s</a>', $esc_url, $text);
}

function login_render() {
	$_SESSION['login_redirect_url'] = $_SERVER['SCRIPT_URI'];
	return redirect_render(buildURL('login'));
}
/**
 * Return an HTTP redirect response
 */
function redirect_render($redir_url)
{
    $headers = array(http_found,
                     header_content_text,
                     header_connection_close,
                     'Location: ' . $redir_url,
                     );
    $body = sprintf(redirect_message, $redir_url);
    return array($headers, $body);
}

function navigation_render($msg, $items)
{
    $what = link_render(buildURL(), 'JiWai OpenID Server');
    if ($msg) {
        $what .= ' &mdash; ' . $msg;
    }
    if ($items) {
        $s = '<p>' . $what . '</p><ul class="bottom">';
        foreach ($items as $action => $text) {
            $url = buildURL($action);
            $s .= sprintf('<li>%s</li>', link_render($url, $text));
        }
        $s .= '</ul>';
    } else {
        $s = '<p class="bottom">' . $what . '</p>';
    }
    return sprintf('<div class="navigation">%s</div>', $s);
}

/**
 * Render an HTML page
 */
function page_render($body, $user, $title, $h1=null, $login=false)
{
	ob_start();
?>
<html>

<head>
<?php 
JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
)); 
?>
</head>


<body class="account" id="settings">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<?php JWTemplate::ShowActionResultTipsMain(); ?>
<div id="container">
<p class="top">设置</p>
<div id="wtMainBlock">
<div class="leftdiv">
<ul class="leftmenu">
<li><a href="/wo/account/settings">基本资料</a></li>
<li><a href="/wo/privacy/">保护设置</a></li>
<li><a href="/wo/devices/sms">绑定设置</a></li>
<li><a href="/wo/notification/email">系统通知</a></li>
<li><a href="/wo/account/profile_settings">个性化界面</a></li>
<li><a href="/wo/openid/" class="now">Open ID</a></li>
</ul>
</div><!-- leftdiv -->
<div class="rightdiv">
<div class="lookfriend">
	<h1><?php echo $title; ?></h1>
	<?php echo $body; ?>
</div><!-- lookfriend -->
<div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
</div>
<!-- rightdiv -->
</div><!-- #wtMainBlock -->
<div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
<?php
    $headers = array();
	$text = ob_get_contents();
	ob_end_clean();
	return array($headers, $text);
}
/*
    $h1 = $h1 ? $h1 : $title;

    if ($user) {
        $msg = sprintf(logged_in_pat, link_render(idURL($user), $user),
                       link_render(idURL($user)));
        $nav = array('logout' => 'Log Out');

        $navigation = navigation_render($msg, $nav);
    } else {
        if (!$login) {
            $msg = link_render(buildURL('login'), 'Log In');
            $navigation = navigation_render($msg, array());
        } else {
            $navigation = '';
        }
    }

    $text = sprintf(page_template, $title, '', $navigation, $h1, $body);
    // No special headers here
    $headers = array();
    return array($headers, $text);
}
*/
?>
