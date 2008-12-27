<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();
JWLogin::MustLogined(false);

$q = $page = null;
extract($_GET, EXTR_IF_EXISTS);
$logined_user_info 	= JWUser::GetCurrentUserInfo();
$logined_user_id 	= $logined_user_info['id'];
$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page = ($page < 1 ) ? 1 : $page;

$title = '编辑群发短信';
$vipIds = array(1, 89, 863, 2802, 77297);

if (!in_array($logined_user_info['id'], $vipIds)) {
    JWApi::OutHeader(401, true);
}

if (@$_POST['jw_status'] && @$_POST['jw_bulksms']) {
    $smsMsg = preg_replace('/\r/', '', $_POST['jw_status']);
    $mobilesToSend = array_unique( preg_split('/[\n\r\s,;]+/', $_POST['jw_bulksms']) );
    $senderMobile = JWDevice::GetDeviceRowByUserId($logined_user_info['id']);
    if (@$senderMobile['sms']['address']) {
        $suffix = $senderMobile['sms']['address'];
        $smsMsgArray = ( preg_match('/^[\x00-\x7F]+$/', $smsMsg) )
            ? JWSms::SplitSms($smsMsg, 140)
            : JWSms::SplitSms($smsMsg, 70);
        foreach ($mobilesToSend as $mobileNo) {
            $serverCode = JWSPCode::GetCodeByMobileNo($mobileNo);
            $serverAddress = $serverCode['code'] . $serverCode['func'] . $suffix;
            foreach ($smsMsgArray as $smsMsgFragment) {
                JWRobot::SendMtRawQueue($mobileNo, 'sms', $smsMsgFragment, $serverAddress);
            }
        }
        JWSession::SetInfo('notice', '发送成功');
    } else {
        JWSession::SetInfo('error', '发送失败');
    }
}

?>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php 
$options = array ('ui_user_id' => $logined_user_id );
JWTemplate::html_head($options);
?>
</head>


<body class="normal">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div id="container">
<?php 
/*
$now_str = strftime("%Y/%m/%d") ;
echo <<<_HTML_
	<div id="flaginfo">$now_str</div>
_HTML_;
*/
?>
<!-- google_ad_section_start -->
	<div id="content">
		<div id="wrapper">

<?php JWTemplate::ShowBalloon($logined_user_id) ?>
<?php JWTemplate::ShowAlphaBetaTips() ?>
<?php JWTemplate::ShowActionResultTips() ?>


<!--?php
     $options = array('sendtips' => 'true');
     $options["mode"] = 2;
     JWTemplate::updater($options); 
?-->

<div id="jwupdate">
<form action="" id="updaterForm" method="post">
<h2>
<span class="tip">还可输入：<span class="counter" id="status-field-char-counter">140</span> 个字符</span><?php echo $title;?><?php if(isset($options['friends'])) { ?>给：<select name="user[id]" class="jwselect" id="user_id"> <?php
    foreach ($options['friends'] as $id => $f) echo <<<_HTML_
        <option value="$id">$f[nameScreen]</option>
_HTML_;
    ?></select>
        <?php } ?>
        </h2>
        <p>
        <input type="hidden" id="idUserReplyTo" name="idUserReplyTo"/>
        <input type="hidden" id="idStatusReplyTo" name="idStatusReplyTo"/>
        <textarea name="jw_status" rows="3" id="jw_status" value=""></textarea>
        <span class="ctrlenter">输入手机号码，每行一个</span>
        <textarea name="jw_bulksms" rows="4" id="jw_bulksms" value=""></textarea>
        </p>
        <p class="act">
        <input style="margin-left:115px;" type="submit" class="submitbutton" value="叽歪一下" title="叽歪一下"/>
        </p>	
</form>
</div>
<?php JWTemplate::ShowActionResultTips(); ?>

		</div><!-- wrapper -->
	</div><!-- content -->

<?php
include_once ( dirname(__FILE__) . '/../sidebar.php' );
JWTemplate::container_ending();
?>
</div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
