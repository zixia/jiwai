<?php
require_once('../../../jiwai.inc.php');
$idUser = JWLogin::GetCurrentUserId();
if( false == $idUser ) {
	$_SESSION['login_redirect_url'] = $_SERVER['SCRIPT_URI'];
	Header('Location: /wo/share/login');
	exit;
}

$t = $u = $d = null;
extract( $_GET );
$t = mb_convert_encoding($t, 'UTF-8', 'UTF-8,GB2312');
$d = mb_convert_encoding($d, 'UTF-8', 'UTF-8,GB2312');
$u = mb_convert_encoding($u, 'UTF-8', 'UTF-8,GB2312');

JWTemplate::html_doctype();
?>

<html>
<head>
<?php JWTemplate::html_head(); ?>
<script>
function collectToJiWai(){
	new Ajax( '/wo/share/u', {
		method: 'post',
		data: $('f').toQueryString(),
		headers: {'AJAX':true},
		onSuccess: function() {
			alert('收藏到叽歪成功');
			window.close();
		}
	}).request();
}
</script>
</head>

<body style="margin:0px;">
<?php JWTemplate::ShowActionResultTipsMain() ?>

<div id="wtCollectionMain">
<h1>收藏到叽歪</h1>
<form method="post" id="f">
	<p>标<span class="mar">题</span>：<input name="title" type="text" id="shareTitle" check="null" class="inputStyle2" value="<?php echo htmlSpecialChars($t);?>" alt="标题"/></p>
	<p>网<span class="mar">址</span>：<input name="url" type="text" id="shareUrl" check="null" class="inputStyle2" value="<?php echo htmlSpecialChars($u);?>" readOnly="true" alt="网址"/></p>
	<p>
		<span class="left">描<span class="mar">述</span>：</span>
		<span class="left"><textarea name="description" rows="3" id="shareDesc" check="null" class="textarea" alt="描述"><?php echo htmlSpecialChars($d);?></textarea></span>
		<div style="clear:both;"></div>
	</p>
	<p class="po2">
		<input name="Submit" type="button" class="submitbutton" value="收 藏" onClick="if(JWValidator.validate('f'))collectToJiWai();return false;" />
		<input name="close" type="button" class="submitbutton" value="取 消" onClick="window.close();"/>
	</p>
</form>
</div>

<script>
	JWValidator.init( 'f' );
</script>
</body>
</html>
