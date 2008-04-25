<?php

require_once "lib/session.php";
require_once "lib/render.php";

define('about_error_template',
       '<div class="error">
An error occurred when processing your request:
<br />
%s
</div>');

/**
 * Render the about page, potentially with an error message
 */
function about_render($error=false, $internal=true)
{
    $headers = array();
    if ($error) {
        $headers[] = $internal ? http_internal_error : http_bad_request;
        $body = sprintf(about_error_template, htmlspecialchars($error));
    } else {
		$headers[] = 'Location: http://'.$_SERVER['HTTP_HOST'].'/wo/openid/';
		$body = 'redirecting...';
	}
    $current_user = getLoggedInUser();
    return page_render($body, $current_user, 'JiWai OpenID Server Endpoint');
}

?>
