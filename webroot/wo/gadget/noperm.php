<?php
if( isset($_POST) && isset($_POST['protected']) ) 
{
	$protected = $_POST['protected'];
	$array_info = array();
	$array_info['protected'] = $protected;
	JWUser::Modify($user_info['id'],$array_info);
	JWTemplate::RedirectBackToLastUrl();
}
?>
<html>
<head>
<?php JWTemplate::html_head() ?>
</head>

<body class="account" id="settings">
<?php JWTemplate::accessibility() ?>
<?php JWTemplate::header() ?>

<div id="container">

<?php JWTemplate::UserGadgetNav($subMenu); ?>

	<div class="tabbody">
		<h2 align="center">非常抱歉，你不能使用窗可贴。因为你的消息是受到保护的。如果想启用，请关闭隐私设置。</h2>
		<p align = "center">
		<form name="f" method="POST">
			<input type="hidden" name="protected" id="protected" value="N" />
			<div style=" padding:20px 0 0 160px; height:50px;">
				<input type="submit" onclick="if (confirm('你确定关闭吗？这样你的消息会被所有人看到！')) submit(); return false; "  type="button" class="submitbutton" value="关闭隐私设置"/> &nbsp&nbsp 你的信息会被所有人看到，并且会被搜索引擎搜索到
			</div>
		</form>
		</p>
	</div><!-- tabbody end -->

	<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>
</div><!-- #container end -->

<?php JWTemplate::footer() ?>
</body>
</html>
