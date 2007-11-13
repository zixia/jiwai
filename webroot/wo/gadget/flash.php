<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();
$user_id	= JWLogin::GetCurrentUserId();
$user_info	= JWUser::GetUserInfo($user_id);


$un = $_POST['un'];
$currentUser = JWUser::GetCurrentUserInfo();
$array_info = array();
if( isset($un) && $un )
{
    $array_info['protected'] = 'N';
    JWUser::Modify($currentUser['id'],$array_info);
    Header('Location:/wo/gadget/flash');
    //exit(0);
}   
if( $currentUser['protected'] == 'N'){

?>
<html>
<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="settings">
<?php JWTemplate::accessibility() ?>
<?php JWTemplate::header() ?>

<div id="container">
    <?php JWTemplate::UserGadgetNav('flash'); ?>
    <div class="tabbody">

<h2>叽歪de你和你的朋友们</h2>
<fieldset>
<div style="width:530px;text-align:center">
	<embed pluginspage=" http://www.macromedia.com/go/getflashplayer" 
			quality="high" allowscriptaccess="always" align="middle" flashvars="userid=<?php echo $user_id?>" 
			src=" http://asset.jiwai.de/gadget/flash/friends_gadget_maker.swf"
			type="application/x-shockwave-flash" 
			height="600" width="530"
			name="jiwai_badge"/>
	</embed>
</div>
</fieldset>

<h2>叽歪de你自己</h2>
<fieldset>
<div style="width:600px;text-align:center">
	<embed pluginspage="http://www.macromedia.com/go/getflashplayer" 
			quality="high" allowscriptaccess="always" align="middle" flashvars="userid=<?php echo $user_id?>" 
			src="http://asset.jiwai.de/gadget/flash/user_gadget_maker.swf" 
			type="application/x-shockwave-flash" 
			height="250" width="600" wmode="transparent" 
			name="jiwai_badge"/>
	</embed>
</div>
</fieldset>

    </div>
<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>
</div><!-- #container -->


<?php JWTemplate::footer() ?>

</body>
</html>
<?php
}else{
?>
<html>
<head>
<?php JWTemplate::html_head() ?>
</head>
<body class="account" id="settings">
<?php JWTemplate::accessibility() ?>
<?php JWTemplate::header() ?>
<div id="container">
<?php JWTemplate::UserGadgetNav('flash'); ?>
<div class="tabbody">
<h2 align="center">非常抱歉，你不能使用窗可贴。因为你的消息是受到保护的。如果想启用，请关闭隐私设置。</h2>
<p align = "center">
<form name="protect" method="post" action="flash">
<input type='hidden' name='un' id='un' value='true' />
<div style=" padding:20px 0 0 160px; height:50px;">
<input type="submit" onclick="if (confirm('你确定关闭吗?这样你的消息会被所有人看到!')) submit(); return false; "  type="button" class="submitbutton" value="关闭隐私设置"/> &nbsp&nbsp 你的信息会被所有人看到，并且会被搜索引擎搜索到
</div>
</form>
</p>
</div>
<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>
</div><!-- #container -->
<?php JWTemplate::footer() ?>
</body>
</html>
<?php
}
?>
