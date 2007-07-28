<?php
include_once '../../../jiwai.inc.php';
$action = preg_match('/^[a-z]+/', $_SERVER['QUERY_STRING'], $m) ? $m[0] : '';

if ($action == 'profile') $idUser = (int)$_GET['profile'];
else {
	$facebook = new JWFacebook();
	$idUser = JWDevice::GetDeviceInfoByAddress($facebook->user, 'facebook', 'idUser');
	$idUser = (count($idUser)) ? $idUser[0] : 0;
}

function title() {
	global $idUser;
?>
<fb:title>JiWai.de</fb:title>
<fb:dashboard>
  <fb:action href="http://jiwai.de/">JiWai.de</fb:action>
  <fb:action href="http://jiwai.de/<?php echo JWUser::GetUserInfo($idUser, 'nameScreen'); ?>/">Profile</fb:action>
  <fb:action href="http://jiwai.de/wo/account/settings">More settings</fb:action>
  <fb:action href="?verify">(re)Verify account</fb:action>
  <fb:help href="http://help.jiwai.de/" title="Go to JiWai.de">Go to JiWai.de</fb:help>
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
			Account verified! <br />
			<fb:if-user-has-added-app>
				<a href="http://jiwai.de/">Go back to JiWai.de</a>
				<fb:else>
					<a href="<?php echo $facebook->get_add_url(); ?>">Add JiWai.de to profile</a>
				</fb:else>
			</fb:if-user-has-added-app>
		</fb:success>
<?php
	} else {
		$facebook->SetProfile($idUser); //Update user's profile fbml to dynamic handler
?>
		<fb:error>
			<fb:message>JiWai.de</fb:message>
			Failed to verify your account at JiWai.de: <?php echo $err; ?>
		</fb:error>
<?php
	}
}

function verify() {
?>
		<fb:editor action="?bind">
			<fb:editor-text label="Username" name="username" value=""/>
			<fb:editor-text label="Verification" name="code" value=""/>
			<fb:editor-buttonset>
				<fb:editor-button value="Verify your JiWai.de account"/>
			</fb:editor-buttonset>
		</fb:editor>
<?php
}

function update() { 
	global $facebook, $idUser;
	$s = trim($_POST['status']);
	if ($s && $idUser) {
		JWSns::UpdateStatus($idUser, $s, 'facebook');
	}
	include_once 'status.php';
}

function profile() {
	global $idUser;
?>
<fb:subtitle>
	<fb:action href="http://jiwai.de/<?php echo JWUser::GetUserInfo($idUser, 'nameScreen'); ?>/">More...</fb:action>
	叽歪de我和朋友们
</fb:subtitle>
<style>
.thumb img {width: 24px; height: 24px; margin: 2px;}
.odd {background-color:#F7F7F7;}
.meta {color: #999;}
.meta a {color: #999;}
</style>
<?php
	include_once 'status.php';
?>
<fb:if-is-app-user>
<else>
Click <a href="<?php echo $facebook->get_add_url(); ?>">here</a> to put JiWai.de in your profile.
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
.send {background:transparent url(http://asset.jiwai.de/img/form/button_big.gif) no-repeat scroll left top; border:medium none; color:#FFFFFF; cursor:pointer;  font-weight:bold; height:50px; padding:2px 5px; width:140px;}
</style>
<div>
<fb:if-is-app-user>
<form>
<table class="doing">
	<tr><td><textarea class="status_area" name="status" /></td></tr>
	<tr><td><center><input class="send" type="submit" value="POST" clickrewriteid="statuses" clickrewriteurl="http://api.alpha.jiwai.de/facebook/?update" /></center></td></tr>
</table>
</form>
<div id="statuses">
<?php 
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
