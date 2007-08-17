<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$user_info		= JWUser::GetCurrentUserInfo();
$device_row 	= JWDevice::GetDeviceRowByUserId( JWLogin::GetCurrentUserId() );

$supported_devices = JWDevice::GetSupportedDeviceTypes();
$supported_devices = array_diff( $supported_devices, array( 'sms' ) );

//echo "<pre>";(var_dump($user_setting));
if ( isset($_REQUEST['_shortcut']) )
{
    list($type, $address) = explode($_REQUEST['_shortcut'], ':');
}

?>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="settings">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header('/wo/account/settings') ?>

<div id="container" class="subpage">

<?php JWTemplate::SettingTab(); ?>

<?php

if ( empty($error_html) )
	$error_html	= JWSession::GetInfo('error');

if ( empty($notice_html) )
{
	$notice_html	= JWSession::GetInfo('notice');
}

if ( !empty($error_html) )
{
		echo <<<_HTML_
			<div class="notice">系统通知修改：<ul> $error_html </ul></div>
_HTML_;
}

if ( !empty($notice_html) )
{
	echo <<<_HTML_
			<div class="notice"><ul>$notice_html</ul></div>
_HTML_;
}
?>

<div class="tabbody">

<h2>绑定聊天工具</h2> 
<p>通过MSN、QQ、GTalk、Skype等聊天软件就可以接收和更新你的叽歪，现在就来绑定吧。</p>

<fieldset>
<table width="100%" cellspacing="8" class="chat">
<form>
<?php
   foreach ( $supported_devices as $type ) {
       $bind = isset( $device_row[ $type ] );
       $readonly = $bind ? 'readonly' : '';
       $address = $bind ? $device_row[ $type ]['address'] : '';
       $secret = $bind ? $device_row[ $type ][ 'secret'] : null;
       $checked = $bind ? ( $device_row[ $type ][ 'isSignatureRecord'] == 'Y'? 'checked' : null ) : null;
?>
<tr>
    <td width="30" valign="top">&nbsp;</td>
    <th valign="top"><?php echo JWDevice::GetNameFromType($type); ?> ：</th>
    <td width="230"><input name="device[<?php echo $type;?>][address]" type="text" id="device_<?php echo $type;?>" value="<?php echo $address; ?>" <?php echo $readonly ?>/>
    <?php if( $bind ) {
        if( empty($secret) ) {
        ?>
            <span class="note">
            您好，您的<?php echo JWDevice::GetNameFromType($type);?>账户已经通过我们的验证，给叽歪的机器人<?php echo JWDevice::GetNameFromType($type);?>：<strong><?php echo JWDevice::GetRobotFromType($type, $address);?></strong>发送消息直接更新你的叽歪吧！<br/>
            <?php if( in_array($type, array('qq','msn','gtalk','skype') ) ) { ?>
                <input type="radio" style="display:inline; width:23px;" name="notify_<?php echo $type;?>" id="notify_<?php echo $type;?>_on" <?php if($device_row[$type]['enabledFor']=='everything') echo "checked";?> onclick="JiWai.EnableDevice(<?php echo $device_row[$type]['id'];?>, 'device[enabled_for]=everything');"/><label for="notify_<?php echo $type;?>_on"> 开启通知</label>
                <input type="radio" style="display:inline; width:23px;" name="notify_<?php echo $type;?>" id="notify_<?php echo $type;?>_off" <?php if($device_row[$type]['enabledFor']=='nothing') echo "checked";?> onClick="JiWai.EnableDevice(<?php echo $device_row[$type]['id'];?>, 'device[enabled_for]=nothing');"/><label for="notify_<?php echo $type;?>_off"> 关闭通知</label><br/>
                <?php if( in_array($type, array('gtalk','qq','msn') )) { ?>
                <label for="notify_<?php echo $type;?>_sig">同步聊天工具签名档更新到叽歪</label> <input id="notify_<?php echo $type;?>_sig" type="checkbox" style="display:inline; width:23px;" name="isSignatureRecord" value="<?php echo $device_row[$type]['isSignatureRecord'];?>" <?php if($device_row[$type]['isSignatureRecord']=='Y') echo "checked"; ?> onClick="this.value=(this.value=='Y' ? 'N' : 'Y'); JiWai.EnableDevice(<?php echo $device_row[$type]['id'];?>, 'isSignatureRecord='+this.value);"/> 
                <?php } ?>
            <?php } ?>
            </span>
        <?php
        }else{
        ?>
            <?php if( $type != 'facebook' ) { ?>
                <span class="note">
                你好，请验证您的<?php echo JWDevice::GetNameFromType($type); ?>账户：<br/>
                1、加 <?php echo JWDevice::GetNameFromType($type); ?>：<strong><?php echo JWDevice::GetRobotFromType($type, $address);?></strong> 为好友；<br/>
                2、发送以下验证码<?php echo JWDevice::GetNameFromType($type); ?>进行验证：<br/>
                <strong><?php echo $secret;?></strong>
                </span>
                <?php }else { ?>
            <span class="note">
                您好，facebook帐号：<br/>
                1、访问 <a href="http://apps.facebook.com/jiwaide/?verify">JiWai.de @ Facebook</a> 并安装；<br/>
                2、输入如下验证码进行验证：<br/>
                <strong><?php echo $secret;?><strong>
                </span>
            <?php } ?>
        <?php 
        }
    }
    ?>
    </td>
    <td valign="top">
        <?php if($bind) {
        ?>
            <a href="/wo/devices/destroy/<?php echo $device_row[$type]['id']; ?>" onClick="if (confirm('请确认操作：删除后将永远无法恢复！')) { var f = document.createElement('form'); f.style.display = 'none'; this.parentNode.appendChild(f); f.method = 'POST'; f.action = this.href; var m = document.createElement('input'); m.setAttribute('type', 'hidden'); m.setAttribute('name', '_method'); m.setAttribute('value', 'delete'); f.appendChild(m); f.submit(); }; return false;" ><strong>删除</strong></a>
        <?php 
        } else{
        ?>
            <a href="/wo/devices/create" onClick="{ var f = document.createElement('form'); f.style.display = 'none'; this.parentNode.appendChild(f); f.method = 'POST'; f.action = this.href; var m = document.createElement('input'); m.setAttribute('type', 'hidden'); m.setAttribute('name', 'device[type]'); m.setAttribute('value', '<?php echo $type; ?>'); f.appendChild(m); var m1 = document.createElement('input'); m1.setAttribute('type', 'hidden'); m1.setAttribute('name', 'device[address]'); m1.setAttribute('value', $('device_<?php echo $type;?>').value); f.appendChild(m1); f.submit(); }; return false;"><strong>绑定</strong></a>
        <?php
        }
        ?>
    </td>
</tr>
<? } ?>

</table>
</fieldset>
</form>

</div>
<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>         
</div>
<!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
