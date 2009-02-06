<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined(false);
$t = $u = $d = null;
extract( $_GET );
$t = mb_convert_encoding($t, 'UTF-8', 'UTF-8,GB2312');
$d = mb_convert_encoding($d, 'UTF-8', 'UTF-8,GB2312');
$u = mb_convert_encoding($u, 'UTF-8', 'UTF-8,GB2312');
?>

<html>

<head>
<style>
input.cb{ width:24px; display:inline; }
</style>
<?php JWTemplate::html_head(); ?>
<script>
function collectToJiWai(){
	new Ajax( '/wo/share/u', {
		method: 'post',
		data: $('f').toQueryString(),
		headers: {'AJAX':true},
		onSuccess: function(m) {
			alert(m);
			window.close();
		}
	}).request();
}
</script>
</head>

<?php echo JWTemplate::Header(); ?>

<body class="account" id="create">

<?php JWTemplate::ShowActionResultTips() ?>

<div id="container" class="subpage">

<div id="wtCollectionMain" style="border:0; width:100%;">

<h1>收藏到叽歪</h1>
<form method="post" id="f">
	<input type="hidden" name="crumb" value="<?php echo JWUtility::GenCrumb();?>" />
	<p>标<span class="mar">题</span>：<input name="title" type="text" id="shareTitle" check="null" class="inputStyle2" value="<?php echo htmlSpecialChars($t);?>" alt="标题"/></p>
	<p>网<span class="mar">址</span>：<input name="url" type="text" id="shareUrl" check="null" class="inputStyle2" value="<?php echo htmlSpecialChars($u);?>" readOnly="true" alt="网址"/></p>
	<p>
		<span class="left">描<span class="mar">述</span>：</span>
		<span class="left"><textarea name="description" rows="3" id="shareDesc" class="textarea" alt="描述"><?php echo htmlSpecialChars($d);?></textarea></span>
		<div style="clear:both;"></div>
	</p>
	<p class="po2" style="display:block;">
		<input name="submit" type="button" class="submitbutton" value="收 藏" onClick="if(JWValidator.validate('f'))collectToJiWai();return false;" />
	</p>
</form>
</div>
</div>

<?php echo JWTemplate::Footer(); ?>
<script>
	JWValidator.init( 'f' );
</script>
</body>
</html>
