<?php

require_once('../../../jiwai.inc.php');

$current_user_id = JWApi::GetAuthedUserId();
if( ! $current_user_id )
{
	JWApi::RenderAuth(JWApi::AUTH_HTTP);
}

$status = null;
extract($_POST, EXTR_IF_EXISTS);

if ( null==$status )
{
	JWApi::OutHeader(400,true);
}

ob_start();
header('Content-Type: text/plain; charset=utf-8');
echo renderLingoOut($status, $current_user_id);
ob_end_flush();

function renderLingoOut($lingo, $idUser) {
    $LingoSupported = array (
        'lingo_help',
        'lingo_tips',
        'lingo_get',
        'lingo_whoami',
        'lingo_whois',
        'lingo_dict',
    );

    $unsupportedMsg = "unsupported";

    $robotMsg = new JWRobotMsg();
    $robotMsg->SetBody($lingo);
    $lingo_func = JWRobotLingoBase::GetLingoFunctionFromMsg($robotMsg);

    if ( !empty($lingo_func) )
    {
        $lingo_func[0] .= 'Api';
        if (! in_array(strtolower($lingo_func[1]), $LingoSupported) ) {
            return $unsupportedMsg;
        }
        $reply_robot_msg 	= call_user_func($lingo_func, $robotMsg, $idUser);
        return $reply_robot_msg->GetBody();
    }

    return $unsupportedMsg;
}

