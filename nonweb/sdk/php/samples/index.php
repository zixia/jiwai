<h1>JiWai API 范例</h1>
<?php
require_once '../lib/Jiwai.php';
$consumer_key    = '771020709341b1e110959e16012da225';
$consumer_secret = '137b05030a3d9013e56072a547413369';
session_start(); 
function error($s) {
?>
<div style="background:lightgrey;color:red;text-align:center;"><?php echo $s; ?></div>
<?php
	die();
}
if (!empty($_POST['auth'])) $_SESSION['auth'] = $_POST['auth'];
/*
	auth	认证方式
	'basic'	明文密码（不推荐）
	'oauth'	OAuth
*/
if (empty($_SESSION['auth'])) {
?>
选择认证方式
<form method="post">
	<input type="radio" name="auth" value="basic" /> 明文密码（不推荐） <br />
	<input type="radio" name="auth" value="oauth" /> OAuth <br />
	<input type="submit" />
</form>
<?php
	die();
}

if ($_SESSION['auth']=='basic') { //明文密码方式
	if (!empty($_POST['username'])) $_SESSION['username'] = $_POST['username']; 
	if (!empty($_POST['password'])) $_SESSION['password'] = $_POST['password']; 
	if (empty($_SESSION['username'])||empty($_SESSION['password'])) {
?>
输入叽歪帐号信息
<form method="post">
	username <input type="text" name="username" value="" /> <br />
	password <input type="password" name="password" value="" /> <br />
	<input type="submit" />
</form>
<?php
		die();
	}
	$auth = new Jiwai_Auth_Basic();
	$auth->setLogin($_SESSION['username'], $_SESSION['password']);
} else { //OAuth方式
	$auth = new Jiwai_Auth_Oauth($consumer_key, $consumer_secret);
	if (!empty($_GET['rtk']) && !empty($_SESSION['request_token']) && $_GET['rtk']==$_SESSION['request_token']->key) { //是否回调?
		$auth->setToken($_SESSION['request_token']);
		unset($_SESSION['request_token']);
		$_SESSION['access_token'] = $auth->fetchAccessToken();
		if (empty($_SESSION['access_token'])) error('访问令牌获取失败');
	} elseif (!empty($_SESSION['access_token'])) { //已获得访问令牌?
		$auth->setToken($_SESSION['access_token']);
	} else { //获得一个请求令牌，并引导用户授权
		$_SESSION['request_token'] = $auth->fetchRequestToken();
		if (empty($_SESSION['request_token'])) error('请求令牌获取失败');
		if (strpos($_SERVER['REQUEST_URI'], '?')!==false) $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));
		$url = $auth->getAuthorizeUrl('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'?rtk='.urlencode($_SESSION['request_token']->key));
?>
		<a href="<?php echo $url; ?>"> 点击开始认证</a>
<?php
		die();
	}
}

$api = new Jiwai($auth);

if (!empty($_POST['action'])) switch ($_POST['action']) {
	case 'publictimeline':
		var_dump($api->publicTimeline()); 
		break;
	case 'update': 
		var_dump($api->update($_POST['status']));
		break;
	case 'logout':
		$_SESSION=array();
		header('Location: '.'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		break;
}
?>
<hr />
你好,
<?php
$account = $api->account();
echo $account->verify->nameScreen;
?>
<form method="post">
	<input type="hidden" name="action" value="update" />
	<input type="text" name="status" value="hehe" /> 
	<input type="submit" value="叽歪一下" />
</form>
<form method="post">
	<input type="hidden" name="action" value="publictimeline" />
	<input type="submit" value="叽歪广场" />
</form>
<form method="post">
	<input type="hidden" name="action" value="logout" />
	<input type="submit" value="注销" />
</form>

