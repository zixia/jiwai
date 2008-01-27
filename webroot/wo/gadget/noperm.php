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
<?php JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
));?>
</head>

<body class="account" id="create">
<?php JWTemplate::accessibility() ?>
<?php JWTemplate::header() ?>

<div id="container">
    <p class="top">窗可贴</p>
        <div id="wtMainBlock">
            <div class="leftdiv">
                <ul class="leftmenu">
                    <li><a href="/wo/gadget/">窗可贴说明</a></li>
                    <li><a href="/wo/gadget/image/" <?php echo $sub_menu=='image'?'class="now"':'';?>>图片窗可贴</a></li>

                    <li><a href="/wo/gadget/flash/" <?php echo $sub_menu=='flash'?'class="now"':'';?>>Flash窗可贴</a></li>
                    <li><a href="/wo/gadget/javascript/" <?php echo $sub_menu=='javascript'?'class="now"':'';?>>代码窗可贴</a></li>
                </ul>
            </div><!-- leftdiv -->
            <div class="rightdiv">
            	<div class="lookfriend">
            		<p class="black15bold">非常抱歉，你不能使用窗可贴。</p>
			<p class="gray12">因为你的消息是受到保护的。如果想启用，请关闭隐私设置。</p>
		<p class="gray12">
		<form name="f" method="POST">
			<input type="hidden" name="protected" id="protected" value="N" />
			<div><input type="submit" onclick="if (confirm('你确定关闭吗？这样你的消息会被所有人看到！')) submit(); return false; "  type="button" class="submitbutton" value="关闭隐私设置"/> <span class="gray12">&nbsp;(&nbsp;你的信息会被所有人看到，并且会被搜索引擎搜索到&nbsp;)&nbsp;</span></div>
		</form>
		</p>
		</div>
	    </div><!-- rightdiv -->
	</div><!-- #wtMainBlock -->
	<div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->
<?php JWTemplate::footer(); ?>
</body>
</html>
