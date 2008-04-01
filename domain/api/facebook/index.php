<?php
include_once '../../../jiwai.inc.php';
$action = preg_match('/^[a-z]+/', $_SERVER['QUERY_STRING'], $m) ? $m[0] : '';

if ($action == 'profile') $idUser = (int)$_GET['profile'];
else {
	$facebook = new JWFacebook();
	$idUser = JWDevice::GetDeviceInfoByAddress($facebook->user, 'facebook', 'idUser');
	$idUser = (count($idUser)) ? (int)$idUser[0] : 0;
}

function title() {
	global $idUser;
?>
<fb:title>JiWai.de</fb:title>
<fb:dashboard>
  <fb:action href="http://apps.facebook.com/jiwaide/">首页</fb:action>
  <fb:action href="?verify">帐号设置</fb:action>
  <fb:action href="http://jiwai.de/wo/account/settings">其他设置</fb:action>
  <fb:help href="http://jiwai.de/" title="Go to JiWai.de">叽歪de</fb:help>
</fb:dashboard>
<?php
}

function bind() {
	global $facebook, $idUser;
	$err = '';
	$idUser = JWUser::GetUserInfo($_POST['username'], 'idUser');
	if ($idUser) {
		if (JWDevice::Verify($facebook->user, 'facebook', $_POST['code'], $idUser)) $err = '';
		else $err = '验证码错误';
	} else $err = '无效用户名';
	if (!$err) {
?>
		<fb:success>
			<fb:message>JiWai.de</fb:message>
			成功关联帐号！<br />
			<fb:if-user-has-added-app>
				<a href="http://jiwai.de/">Go back to JiWai.de</a>
				<fb:else>
					<a href="<?php echo $facebook->get_add_url(); ?>">Add JiWai.de to profile</a>
				</fb:else>
			</fb:if-user-has-added-app>
		</fb:success>
<?php
	} else {
?>
		<fb:error>
			<fb:message>JiWai.de</fb:message>
			帐号关联失败。<?php echo $err; ?>
		</fb:error>
<?php
	}
	$facebook->SetProfile($idUser); //Update user's profile fbml to dynamic handler
	JWFacebook::RefreshRef($idUser);
}

function verify() {
	global $facebook, $idUser;
	if (!$idUser) {
?>
		<fb:editor action="?bind">
			<fb:editor-text label="<?php echo mb_convert_encoding("登录名","HTML-ENTITIES","UTF-8")?>" name="username" value=""/>
			<fb:editor-text label="<?php echo mb_convert_encoding("验证码","HTML-ENTITIES","UTF-8")?>" name="code" value=""/>
			<fb:editor-buttonset>
				<fb:editor-button value="<?php echo mb_convert_encoding("关联叽歪帐号","HTML-ENTITIES","UTF-8")?>"/>
			</fb:editor-buttonset>
		</fb:editor>
<?php
	} else {
?>
		<fb:editor action="?bind">
			<div style="text-align:center;">
				叽歪帐号已经关联，取消关联或绑定其他帐号请访问<a href="http://jiwai.de/wo/devices/im">叽歪设置页</a>。
			</div>
		</fb:editor>
<?php
	}
?>
		<fb:editor action="?bind">
			<div style="text-align:center;">
				<a href="<?php echo JWFacebook::GetPermUrl('status_update'); ?>">让叽歪更新我的Facebook状态</a>
			</div>
		</fb:editor>
<?php
}

function update() { 
	global $facebook, $idUser, $g_with_friends;
	$s = trim($_POST['status']);
	if ($s && $idUser) {
		JWSns::UpdateStatus($idUser, $s, 'facebook');
		$facebook->SetStatus($s);
	}
	$g_with_friends = 1;
	include_once 'status.php';
}

function profile() {
	global $idUser, $facebook;
?>
<fb:if-is-own-profile>
	<fb:profile-action url="http://jiwai.de/<?php echo JWUser::GetUserInfo($idUser, 'nameUrl'); ?>/">
		View your JiWai status
	</fb:profile-action>
	<fb:else>
		<fb:if-is-app-user uid="profileowner">
			<fb:profile-action url="http://jiwai.de/<?php echo JWUser::GetUserInfo($idUser, 'nameUrl'); ?>/">
				View this person's JiWai status
			</fb:profile-action>
			<fb:else>
				<fb:profile-action url="http://apps.facebook.com/jiwaide/?invite">
					Invite this person to JiWai
				</fb:profile-action>
			</fb:else>
		</fb:if-is-app-user>
	</fb:else>
</fb:if-is-own-profile>
<style>
.thumb img {width: 24px; height: 24px; margin: 2px;}
.odd {background-color:#F7F7F7;}
.even {background-color:#FFFFFF;}
.meta {color: #999;}
.meta a {color: #999;}
</style>
<?php
	include_once 'status.php';
?>
<fb:if-is-app-user>
<else>
</else>
</fb:if-is-app-user>
<?php
}

if (isset($_POST['code'])) {
	title();
	bind();
} elseif (!$idUser) {
	title();
	verify();
	exit();
} else {
	switch ($action) {
		case 'verify':
			title();
			verify();
			exit();
			break;
		case 'update':
			update();
			exit();
			break;
		case 'profile':
			profile();
			exit();
			break;
		default:
			title();
	}
}

?>

<style>
.even {background-color:#FFFFFF;}
.odd {background-color:#F7F7F7;}
.doing {padding: 1em; font-size: 1.2em; line-height: 1.1; width: 100%;}
.meta {color: #777777;}
.status_area {width: 96%; font-size: 1.5em; }
.send {
background-color:#3B5998;
border-color:#D9DFEA rgb(14, 31, 91) rgb(14, 31, 91) rgb(217, 223, 234);
border-style:solid;
border-width:1px;
color:#FFFFFF;
font-family:"lucida grande",tahoma,verdana,arial,sans-serif;
font-size:12px;
padding:2px 15px 3px;
text-align:center;
}
.asend {background:transparent url(http://asset.jiwai.de/img/form/button_big.gif) no-repeat scroll left top; border:medium none; color:#FFFFFF; cursor:pointer;  font-weight:bold; height:50px; padding:2px 5px; width:140px;}
</style>
<div>
<fb:if-is-app-user>
<form onsubmit="return false;">
<script>
function update() {
	var txt = document.getElementById('txt_status');
	var btn = document.getElementById('btn_update');
	if (!txt.getValue()) {
		var d = new Dialog();
		d.onconfirm = function() {
			document.getElementById('txt_status').focus();
		}
		d.showMessage('叽歪', '说点什么吧~~');
		return;
	}
	btn.setValue('发送中...');
	btn.setDisabled(true);
	txt.setReadOnly(true);
	txt.setDisabled(true);
	var ajax = new Ajax(); 
	ajax.responseType = Ajax.FBML;
	ajax.requireLogin = true;
	ajax.ondone = function(data) {
		var txt = document.getElementById('txt_status');
		var btn = document.getElementById('btn_update');
		btn.setValue('叽歪一下');
		btn.setDisabled(false);
		txt.setReadOnly(false);
		txt.setDisabled(false);
		document.getElementById('statuses').setInnerFBML(data);
		txt.setValue('');
	}
	ajax.onerror = function() {
		var txt = document.getElementById('txt_status');
		var btn = document.getElementById('btn_update');
		btn.setValue('叽歪一下');
		btn.setDisabled(false);
		txt.setReadOnly(false);
		txt.setDisabled(false);
		var d = new Dialog();
		d.onconfirm = function() {
			document.getElementById('btn_update').focus();
		}
		d.showMessage('叽歪', '发送失败，等会儿再试试？');
	}
	ajax.post('http://api.jiwai.de/facebook/?update', {'status': txt.getValue()});
}
</script>
<table class="doing">
	<tr><td><textarea class="status_area" name="status" id="txt_status" /></td></tr>
	<tr><td><center><input class="send" type="submit" value="<?php echo mb_convert_encoding("叽歪一下","HTML-ENTITIES","UTF-8")?>" id="btn_update" onclick="update();" /></center></td></tr>
</table>
</form>
<div id="statuses">
<?php 
	$g_with_friends = 1;
	include_once 'status.php';
?>
</div>
	<fb:if-user-has-added-app>
		<fb:else>
			<fb:error>
				<fb:message>Hey!</fb:message>
				Click <a href="<?php echo $facebook->get_add_url(); ?>">here</a> to put JiWai.de in your profile.
			</fb:error>
		</fb:else>
	</fb:if-user-has-added-app>
	<fb:else>
		<fb:error>
			<fb:message>Hey!</fb:message>
			No permission granted to JiWai.de application!
		</fb:error>
	</fb:else>
</fb:if-is-app-user>
</div>
