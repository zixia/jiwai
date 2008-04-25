<?php

require_once "lib/session.php";
require_once "lib/render.php";

define('trust_form_pat',
       '<div class="form">
  <form method="post" action="%s" id="f">
  %s
    <input type="checkbox" name="always" value="1" %s/>总是允许<br />
    <input type="submit" class="submitbutton" name="trust" value="确认" />
    <input type="submit" class="submitbutton" value="取消" />
  </form>
</div>
');

define('normal_pat',
       '<p>是否将你的OpenID身份信息' .
       '(<code>%s</code>) 发送到 <div style="background:#ccc;margin:2px;padding:4px;">%s</div> 网站？</p>');

define('id_select_pat',
       '<p>You entered the server URL at the RP.
Please choose the name you wish to use.  If you enter nothing, the request will be cancelled.<br/>
<input type="text" name="idSelect" /></p>
');

define('no_id_pat',
'
You did not send an identifier with the request,
and it was not an identifier selection request.
Please return to the relying party and try again.
');

function trust_render($info, $trusted=false)
{
    $current_user = getLoggedInUser();
    $lnk = link_render(idURL($current_user));
    $trust_root = htmlspecialchars($info->trust_root);
    $trust_url = buildURL('trust', true);

    if ($info->idSelect()) {
        $prompt = id_select_pat;
    } else {
        $prompt = sprintf(normal_pat, $lnk, $trust_root);
    }

    $form = sprintf(trust_form_pat, $trust_url, $prompt, $trusted ? 'checked disabled' : '');
	if (getLoggedInUser('isUrlFixed')=='N') $form.= '<div><br /><input type="checkbox" checked disabled /> 点击确认开始使用OpenID后，你的个人页面URL地址将不可再次修改。<a href="/wo/account/settings/" target="_blank">现在修改</a>还来得及。</div>';

	if ($trusted) $form.= '<script type="text/javascript">document.getElementById("f").submit();</script>';

    return page_render($form, $current_user, 'Trust This Site');
}

function noIdentifier_render()
{
    return page_render(no_id_pat, null, 'No Identifier Sent');
}

?>
